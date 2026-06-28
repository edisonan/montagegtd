<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from CLI.\n");
    exit(1);
}

$argvCopy = $argv;
array_shift($argvCopy);

$options = array(
    'keyword' => 'tiktok',
    'source_type' => 'manual_pixabay',
    'limit' => 20,
);
$urls = array();

foreach ($argvCopy as $arg) {
    if (strpos($arg, '--keyword=') === 0) {
        $options['keyword'] = trim(substr($arg, 10));
        continue;
    }
    if (strpos($arg, '--source-type=') === 0) {
        $options['source_type'] = trim(substr($arg, 14));
        continue;
    }
    if (strpos($arg, '--limit=') === 0) {
        $options['limit'] = max(1, (int)substr($arg, 8));
        continue;
    }
    $urls[] = trim($arg);
}

if (empty($urls)) {
    $stdin = stream_get_contents(STDIN);
    if ($stdin !== false && trim($stdin) !== '') {
        $urls = preg_split('/\R+/', trim($stdin));
        $urls = array_values(array_filter(array_map('trim', $urls)));
    }
}

if (empty($urls)) {
    fwrite(STDERR, "Usage:\n");
    fwrite(STDERR, "  php scripts/import_pixabay_bgm.php [--keyword=tiktok] <pixabay-music-url> [...]\n");
    fwrite(STDERR, "  printf '%s\n' '<url1>' '<url2>' | php scripts/import_pixabay_bgm.php --keyword=tiktok\n");
    exit(1);
}

$urls = array_slice(array_values(array_unique($urls)), 0, $options['limit']);
$rows = array();
$errors = array();
$sortOrder = 10;

foreach ($urls as $url) {
    if (!preg_match('#^https?://#i', $url)) {
        $errors[] = "Skip invalid url: {$url}";
        continue;
    }

    $html = fetchHtml($url);
    if ($html === null) {
        $errors[] = "Fetch failed: {$url}";
        continue;
    }

    $track = parsePixabayMusicPage($html, $url);
    $track['source_type'] = $options['source_type'];
    $track['search_keyword'] = $options['keyword'];
    $track['sort_order'] = $sortOrder;
    $sortOrder += 10;

    $rows[] = $track;
}

if (empty($rows)) {
    fwrite(STDERR, "No track rows generated.\n");
    foreach ($errors as $error) {
        fwrite(STDERR, $error . "\n");
    }
    exit(2);
}

echo buildInsertSql($rows), "\n";

if (!empty($errors)) {
    fwrite(STDERR, "\nWarnings:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, "- {$error}\n");
    }
}

function fetchHtml(string $url): ?string
{
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; task-gitee-pixabay-importer/1.0)',
        CURLOPT_HTTPHEADER => array(
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
        ),
    ));
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0 || $httpCode < 200 || $httpCode >= 300 || !is_string($body) || trim($body) === '') {
        return null;
    }

    return $body;
}

function parsePixabayMusicPage(string $html, string $url): array
{
    $title = firstNonEmpty(array(
        extractMetaTag($html, 'property', 'og:title'),
        extractMetaTag($html, 'name', 'twitter:title'),
        extractJsonLikeValue($html, 'name'),
        extractTitleTag($html),
    ));

    $artist = firstNonEmpty(array(
        extractJsonLikeValue($html, 'author'),
        extractMetaTag($html, 'name', 'author'),
        extractArtistFromTitle($title),
    ));

    $audioUrl = firstNonEmpty(array(
        extractJsonLikeValue($html, 'contentUrl'),
        extractJsonLikeValue($html, 'downloadUrl'),
        extractRegexValue($html, '#"contentUrl"\s*:\s*"([^"]+)"#'),
        extractRegexValue($html, '#"downloadUrl"\s*:\s*"([^"]+)"#'),
        extractRegexValue($html, '#https://cdn\.pixabay\.com/audio/[^"\']+#'),
    ));

    $cleanTitle = normalizeTitle($title);
    if ($cleanTitle === '') {
        $cleanTitle = 'Pixabay Track ' . substr(md5($url), 0, 6);
    }

    $cleanArtist = normalizeArtist($artist);
    if ($cleanArtist === '') {
        $cleanArtist = 'Pixabay Artist';
    }

    return array(
        'title' => $cleanTitle,
        'artist' => $cleanArtist,
        'audio_url' => $audioUrl !== '' ? decodeEscapedUrl($audioUrl) : 'https://your-cdn-or-audio-url/replace-me.mp3',
        'source_url' => $url,
        'cover_color' => pickColorByTitle($cleanTitle),
        'metadata_json' => array(
            'license' => 'pixabay',
            'imported_from' => 'pixabay_music_page',
            'audio_url_auto_detected' => $audioUrl !== '',
        ),
    );
}

function extractMetaTag(string $html, string $attrName, string $attrValue): string
{
    $pattern = '#<meta[^>]*' . preg_quote($attrName, '#') . '\s*=\s*["\']' . preg_quote($attrValue, '#') . '["\'][^>]*content\s*=\s*["\']([^"\']+)["\']#i';
    $value = extractRegexValue($html, $pattern);
    if ($value !== '') {
        return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    }

    $patternReverse = '#<meta[^>]*content\s*=\s*["\']([^"\']+)["\'][^>]*' . preg_quote($attrName, '#') . '\s*=\s*["\']' . preg_quote($attrValue, '#') . '["\']#i';
    $value = extractRegexValue($html, $patternReverse);
    return $value !== '' ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : '';
}

function extractTitleTag(string $html): string
{
    $value = extractRegexValue($html, '#<title[^>]*>(.*?)</title>#is');
    return $value !== '' ? html_entity_decode(trim(strip_tags($value)), ENT_QUOTES, 'UTF-8') : '';
}

function extractJsonLikeValue(string $html, string $field): string
{
    $patterns = array(
        '#"' . preg_quote($field, '#') . '"\s*:\s*"([^"]+)"#',
        "#'" . preg_quote($field, '#') . "'\s*:\s*'([^']+)'#",
    );

    foreach ($patterns as $pattern) {
        $value = extractRegexValue($html, $pattern);
        if ($value !== '') {
            return html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        }
    }

    return '';
}

function extractRegexValue(string $html, string $pattern): string
{
    if (preg_match($pattern, $html, $matches)) {
        return isset($matches[1]) ? trim($matches[1]) : trim($matches[0]);
    }
    return '';
}

function extractArtistFromTitle(string $title): string
{
    $title = trim($title);
    if ($title === '') {
        return '';
    }

    if (preg_match('/\s+by\s+(.+)$/i', $title, $matches)) {
        return trim($matches[1]);
    }

    return '';
}

function normalizeTitle(string $title): string
{
    $title = trim(html_entity_decode($title, ENT_QUOTES, 'UTF-8'));
    $title = preg_replace('/\s*-\s*Pixabay.*$/iu', '', $title);
    $title = preg_replace('/\s*\|\s*Pixabay.*$/iu', '', $title);
    $title = preg_replace('/\s+/u', ' ', $title);
    return trim($title);
}

function normalizeArtist(string $artist): string
{
    $artist = trim(html_entity_decode($artist, ENT_QUOTES, 'UTF-8'));
    $artist = preg_replace('/\s+/u', ' ', $artist);
    return trim($artist);
}

function decodeEscapedUrl(string $url): string
{
    $url = str_replace('\\/', '/', $url);
    return html_entity_decode($url, ENT_QUOTES, 'UTF-8');
}

function pickColorByTitle(string $title): string
{
    $colors = array('#ff7a59', '#00b894', '#6c5ce7', '#0984e3', '#e17055', '#2d3436');
    $index = abs(crc32($title)) % count($colors);
    return $colors[$index];
}

function firstNonEmpty(array $values): string
{
    foreach ($values as $value) {
        $value = trim((string)$value);
        if ($value !== '') {
            return $value;
        }
    }
    return '';
}

function sqlQuote(?string $value): string
{
    if ($value === null) {
        return 'NULL';
    }
    return "'" . str_replace(array("\\", "'"), array("\\\\", "\\'"), $value) . "'";
}

function buildInsertSql(array $rows): string
{
    $sql = "INSERT INTO `bgm_tracks` (\n"
        . "  `title`,\n"
        . "  `artist`,\n"
        . "  `audio_url`,\n"
        . "  `source_url`,\n"
        . "  `cover_color`,\n"
        . "  `source_type`,\n"
        . "  `search_keyword`,\n"
        . "  `sort_order`,\n"
        . "  `is_active`,\n"
        . "  `metadata_json`,\n"
        . "  `created_at`,\n"
        . "  `updated_at`\n"
        . ") VALUES\n";

    $valueLines = array();
    foreach ($rows as $row) {
        $valueLines[] = "("
            . sqlQuote($row['title']) . ", "
            . sqlQuote($row['artist']) . ", "
            . sqlQuote($row['audio_url']) . ", "
            . sqlQuote($row['source_url']) . ", "
            . sqlQuote($row['cover_color']) . ", "
            . sqlQuote($row['source_type']) . ", "
            . sqlQuote($row['search_keyword']) . ", "
            . (int)$row['sort_order'] . ", "
            . "1, "
            . sqlQuote(json_encode($row['metadata_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . ", "
            . "NOW(), NOW()"
            . ")";
    }

    return $sql . implode(",\n", $valueLines) . ";";
}
