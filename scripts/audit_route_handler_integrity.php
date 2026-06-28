<?php
$files = ['routes/web.php', 'routes/api.php'];
$missing = [];
foreach ($files as $rf) {
    $txt = file_get_contents($rf);
    $lines = preg_split('/\R/', $txt);
    $clean = [];
    foreach ($lines as $line) {
        $trim = ltrim($line);
        if (strpos($trim, '//') === 0) {
            continue;
        }
        $clean[] = $line;
    }
    $txt = implode("\n", $clean);

    preg_match_all('/Route::(?:get|post|put|patch|delete|any)\([^\n;]*["\']([A-Za-z0-9_\\\\]+)@([A-Za-z0-9_]+)["\']/', $txt, $matches, PREG_SET_ORDER);
    foreach ($matches as $r) {
        $ctrl = $r[1];
        $method = $r[2];
        $path = 'app/Http/Controllers/' . str_replace('\\', '/', $ctrl) . '.php';
        if (!file_exists($path)) {
            $missing[] = "$rf :: $ctrl@$method (controller file missing: $path)";
            continue;
        }
        $code = file_get_contents($path);
        if (!preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $code)) {
            $missing[] = "$rf :: $ctrl@$method (method missing)";
        }
    }
}

if ($missing) {
    echo "[FAIL] Route handler integrity check failed:\n";
    echo implode("\n", $missing), "\n";
    exit(1);
}

echo "[PASS] Route handler integrity check passed\n";
