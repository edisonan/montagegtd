<?php

namespace App\Services;

use InvalidArgumentException;
use SplFileInfo;

class AdminLogService
{
    const DEFAULT_LINES = 500;
    const MAX_LINES = 2000;
    const TAIL_BYTES = 1048576;
    const SEARCH_BYTES = 10485760;

    protected $logPath;

    protected $levels = array(
        'DEBUG',
        'INFO',
        'NOTICE',
        'WARNING',
        'ERROR',
        'CRITICAL',
        'ALERT',
        'EMERGENCY',
    );

    public function __construct()
    {
        $this->logPath = storage_path('logs');
    }

    public function levels()
    {
        return $this->levels;
    }

    public function listFiles()
    {
        if (!is_dir($this->logPath)) {
            return array();
        }

        $files = array();
        foreach (new \DirectoryIterator($this->logPath) as $file) {
            if (!$this->isAllowedFileInfo($file)) {
                continue;
            }

            $files[] = array(
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'size_label' => $this->formatBytes($file->getSize()),
                'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                'readable' => $file->isReadable(),
            );
        }

        usort($files, function ($a, $b) {
            return strcmp($b['modified_at'], $a['modified_at']);
        });

        return $files;
    }

    public function defaultFile()
    {
        $files = $this->listFiles();

        return count($files) > 0 ? $files[0]['name'] : null;
    }

    public function read($file, $lines = self::DEFAULT_LINES, $level = null, $keyword = null, $order = 'desc')
    {
        $path = $this->resolvePath($file);
        $lineLimit = $this->normalizeLineLimit($lines);
        $level = $this->normalizeLevel($level);
        $keyword = trim((string) $keyword);
        $order = $this->normalizeOrder($order);
        $fileSize = filesize($path);
        $scanBytes = $keyword !== '' ? min($fileSize, self::SEARCH_BYTES) : min($fileSize, self::TAIL_BYTES);

        $raw = $this->readTail($path, $keyword === '' ? $lineLimit : self::MAX_LINES, $keyword !== '');
        $entries = $this->parseEntries($raw);
        $filtered = $this->filterEntries($entries, $level, $keyword);
        $filtered = array_slice($filtered, -$lineLimit);
        if ($order === 'desc') {
            $filtered = array_reverse($filtered);
        }

        return array(
            'file' => basename($path),
            'size' => $fileSize,
            'size_label' => $this->formatBytes($fileSize),
            'modified_at' => date('Y-m-d H:i:s', filemtime($path)),
            'lines' => $lineLimit,
            'level' => $level,
            'keyword' => $keyword,
            'order' => $order,
            'content' => implode("\n", $filtered),
            'matched_count' => count($filtered),
            'truncated' => $keyword !== '' && $fileSize > self::SEARCH_BYTES,
            'scan_size_label' => $this->formatBytes($scanBytes),
        );
    }

    protected function resolvePath($file)
    {
        $file = (string) $file;
        if ($file === '' || basename($file) !== $file || !preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
            throw new InvalidArgumentException('日志文件名不合法');
        }

        $base = realpath($this->logPath);
        $candidate = $this->logPath . DIRECTORY_SEPARATOR . $file;
        if (is_link($candidate)) {
            throw new InvalidArgumentException('日志文件不可读');
        }

        $path = realpath($candidate);

        if (!$base || !$path || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0) {
            throw new InvalidArgumentException('日志文件不存在');
        }

        if (!is_file($path) || is_link($path) || !is_readable($path)) {
            throw new InvalidArgumentException('日志文件不可读');
        }

        return $path;
    }

    protected function readTail($path, $lines, $forSearch)
    {
        $maxBytes = $forSearch ? self::SEARCH_BYTES : self::TAIL_BYTES;
        $size = filesize($path);
        $offset = max(0, $size - $maxBytes);
        $handle = fopen($path, 'rb');

        if (!$handle) {
            throw new InvalidArgumentException('日志文件打开失败');
        }

        if ($offset > 0) {
            fseek($handle, $offset);
            fgets($handle);
        }

        $buffer = array();
        while (($line = fgets($handle)) !== false) {
            $buffer[] = rtrim($line, "\r\n");
            if (!$forSearch && count($buffer) > $lines * 8) {
                $buffer = array_slice($buffer, -$lines * 8);
            }
        }

        fclose($handle);

        return $buffer;
    }

    protected function parseEntries(array $lines)
    {
        $entries = array();
        $current = '';

        foreach ($lines as $line) {
            if ($this->isEntryStart($line)) {
                if ($current !== '') {
                    $entries[] = $current;
                }
                $current = $line;
                continue;
            }

            $current .= ($current === '' ? '' : "\n") . $line;
        }

        if ($current !== '') {
            $entries[] = $current;
        }

        return $entries;
    }

    protected function filterEntries(array $entries, $level, $keyword)
    {
        $result = array();
        $keyword = trim((string) $keyword);

        foreach ($entries as $entry) {
            if ($level && !$this->entryHasLevel($entry, $level)) {
                continue;
            }

            if ($keyword !== '' && stripos($entry, $keyword) === false) {
                continue;
            }

            $result[] = $entry;
        }

        return $result;
    }

    protected function isEntryStart($line)
    {
        return preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $line) === 1;
    }

    protected function entryHasLevel($entry, $level)
    {
        return preg_match('/\b' . preg_quote($level, '/') . '\b/', $entry) === 1;
    }

    protected function normalizeLineLimit($lines)
    {
        $lines = (int) $lines;
        if (!in_array($lines, array(100, 500, 1000, 2000), true)) {
            $lines = self::DEFAULT_LINES;
        }

        return min(self::MAX_LINES, max(100, $lines));
    }

    protected function normalizeLevel($level)
    {
        $level = strtoupper(trim((string) $level));

        return in_array($level, $this->levels, true) ? $level : null;
    }

    protected function normalizeOrder($order)
    {
        return strtolower((string) $order) === 'asc' ? 'asc' : 'desc';
    }

    protected function isAllowedFileInfo(SplFileInfo $file)
    {
        if ($file->isDot() || !$file->isFile() || $file->isLink()) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9._-]+$/', $file->getFilename()) === 1;
    }

    protected function formatBytes($bytes)
    {
        $bytes = (int) $bytes;
        $units = array('B', 'KB', 'MB', 'GB');
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes = $bytes / 1024;
            $index++;
        }

        return round($bytes, $index === 0 ? 0 : 2) . ' ' . $units[$index];
    }
}
