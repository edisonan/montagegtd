<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$outFile = $root . '/docs/openapi-v2.yaml';

function parseCapabilityFromMiddleware(string $middleware): string
{
    if (strpos($middleware, 'hybrid.token:admin') !== false) {
        return 'admin';
    }
    if (strpos($middleware, 'hybrid.token:write') !== false) {
        return 'write';
    }
    if (strpos($middleware, 'hybrid.token:read') !== false) {
        return 'read';
    }
    if (strpos($middleware, 'hybrid.token') !== false) {
        return 'token';
    }
    if (strpos($middleware, 'web,auth') !== false || strpos($middleware, "->middleware('auth')") !== false) {
        return 'web-session';
    }
    return 'public';
}

function yamlQuote(string $s): string
{
    $escaped = str_replace(["\\", "\""], ["\\\\", "\\\""], $s);
    return '"' . $escaped . '"';
}

function opTag(string $path): string
{
    $parts = explode('/', trim($path, '/'));
    $first = $parts[0] ?? 'misc';
    return $first !== '' ? $first : 'misc';
}

function parseActionMeta(array $entry): array
{
    $action = $entry['action'];
    if ($action === 'Closure') {
        return ['controller' => 'Closure', 'handler' => 'handle'];
    }
    if (preg_match('/Api\\\\V2\\\\([^@]+)@(.+)$/', $action, $m)) {
        return ['controller' => $m[1], 'handler' => $m[2]];
    }
    return ['controller' => 'Unknown', 'handler' => 'handle'];
}

function operationId(array $entry): string
{
    $meta = parseActionMeta($entry);
    $ctrl = str_replace('Controller', '', $meta['controller']);
    $ctrl = preg_replace('/[^A-Za-z0-9]+/', '_', $ctrl ?: 'unknown');
    $handler = preg_replace('/[^A-Za-z0-9]+/', '_', $meta['handler'] ?: 'handle');

    $path = trim($entry['path'], '/');
    $path = preg_replace('/\{([^}]+)\}/', 'by_$1', $path);
    $path = preg_replace('/[^A-Za-z0-9]+/', '_', $path ?: 'root');

    return strtolower($ctrl . '_' . $handler . '_' . $entry['method'] . '_' . $path);
}

function extractPathParams(string $path): array
{
    if (!preg_match_all('/\{([^}]+)\}/', $path, $m)) {
        return [];
    }
    return $m[1];
}

function methodOrder(string $method): int
{
    static $order = ['get' => 1, 'post' => 2, 'put' => 3, 'patch' => 4, 'delete' => 5, 'options' => 6, 'head' => 7];
    return $order[$method] ?? 99;
}

$entries = [];

$apiFile = $root . '/routes/api.php';
$apiLines = file($apiFile, FILE_IGNORE_NEW_LINES) ?: [];

$depth = 0;
$inV2 = false;
$v2Depth = -1;
$capStack = [];

foreach ($apiLines as $line) {
    while (!empty($capStack) && $depth < $capStack[count($capStack) - 1]['depth']) {
        array_pop($capStack);
    }

    if ($inV2 && $depth < $v2Depth) {
        $inV2 = false;
    }

    if (!$inV2 && strpos($line, "Route::prefix('v2')->group(function () {") !== false) {
        $inV2 = true;
        $v2Depth = $depth + 1;
    }

    if ($inV2) {
        if (preg_match("/Route::group\(.*hybrid\.token:(read|write|admin).*function\s*\(\)\s*\{/", $line, $m)) {
            $capStack[] = [
                'capability' => $m[1],
                'depth' => $depth + 1,
            ];
        }

        // String action route
        if (preg_match("/Route::(get|post|put|delete|any)\('([^']+)',\s*'([^']+)'\)(.*);/", $line, $m)) {
            $method = strtolower($m[1]);
            $uri = $m[2];
            $action = $m[3];
            $tail = $m[4] ?? '';

            if ($method === 'any') {
                $methods = ['get', 'post', 'put', 'delete'];
            } else {
                $methods = [$method];
            }

            $cap = !empty($capStack) ? $capStack[count($capStack) - 1]['capability'] : 'public';
            if (preg_match("/->middleware\('([^']+)'\)/", $tail, $mm)) {
                $cap = parseCapabilityFromMiddleware($mm[1]);
            }

            foreach ($methods as $meth) {
                $entries[] = [
                    'method' => $meth,
                    'path' => '/' . ltrim($uri, '/'),
                    'action' => $action,
                    'capability' => $cap,
                    'middleware' => trim($cap !== 'public' ? ('hybrid.token:' . $cap) : 'public'),
                ];
            }
        }

        // Closure route
        if (preg_match("/Route::(get|post|put|delete|any)\('([^']+)',\s*function\s*\(.*$/", $line, $m)) {
            $method = strtolower($m[1]);
            $uri = $m[2];
            $methods = $method === 'any' ? ['get', 'post', 'put', 'delete'] : [$method];
            $cap = !empty($capStack) ? $capStack[count($capStack) - 1]['capability'] : 'public';

            foreach ($methods as $meth) {
                $entries[] = [
                    'method' => $meth,
                    'path' => '/' . ltrim($uri, '/'),
                    'action' => 'Closure',
                    'capability' => $cap,
                    'middleware' => trim($cap !== 'public' ? ('hybrid.token:' . $cap) : 'public'),
                ];
            }
        }
    }

    $depth += substr_count($line, '{');
    $depth -= substr_count($line, '}');
}

// include web.php routes that still expose /api/v2/*
$webFile = $root . '/routes/web.php';
$webLines = file($webFile, FILE_IGNORE_NEW_LINES) ?: [];
foreach ($webLines as $line) {
    if (strpos($line, "'/api/v2/") === false) {
        continue;
    }

    if (!preg_match("/Route::(get|post|put|delete|any)\('([^']+)',\s*'([^']+)'\)(.*);/", $line, $m)) {
        continue;
    }

    $method = strtolower($m[1]);
    $uri = $m[2];
    $action = $m[3];
    $tail = $m[4] ?? '';

    if (strpos($uri, '/api/v2/') !== 0) {
        continue;
    }

    $methods = $method === 'any' ? ['get', 'post', 'put', 'delete'] : [$method];
    $cap = parseCapabilityFromMiddleware($tail);

    foreach ($methods as $meth) {
        $entries[] = [
            'method' => $meth,
            'path' => substr($uri, strlen('/api/v2')),
            'action' => $action,
            'capability' => $cap,
            'middleware' => $cap,
        ];
    }
}

// normalize and de-duplicate (prefer api.php entries over web.php duplicates)
$dedup = [];
foreach ($entries as $e) {
    $path = '/' . ltrim($e['path'], '/');
    $key = $e['method'] . ' ' . $path;
    $e['path'] = $path;

    if (!isset($dedup[$key])) {
        $dedup[$key] = $e;
        continue;
    }

    // Keep non-web-session capability if available
    $old = $dedup[$key];
    if ($old['capability'] === 'web-session' && $e['capability'] !== 'web-session') {
        $dedup[$key] = $e;
    }
}

$entries = array_values($dedup);

usort($entries, static function ($a, $b) {
    $cmp = strcmp($a['path'], $b['path']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return methodOrder($a['method']) <=> methodOrder($b['method']);
});

$paths = [];
foreach ($entries as $entry) {
    $paths[$entry['path']][] = $entry;
}
ksort($paths);

$allTags = [];
foreach (array_keys($paths) as $path) {
    $allTags[opTag($path)] = true;
}
ksort($allTags);

$yaml = [];
$yaml[] = 'openapi: 3.0.3';
$yaml[] = 'info:';
$yaml[] = '  title: Task-Gitee API V2';
$yaml[] = '  version: 1.0.0';
$yaml[] = '  description: |';
$yaml[] = '    Auto-generated OpenAPI document for `/api/v2` routes.';
$yaml[] = '    Optimized for AI agents and API clients with stable operationId and capability hints.';
$yaml[] = 'servers:';
$yaml[] = '  - url: http://testtask.congcong.us/api/v2';
$yaml[] = '    description: test environment';
$yaml[] = '  - url: http://localhost/api/v2';
$yaml[] = '    description: local environment';
$yaml[] = 'security:';
$yaml[] = '  - BearerAuth: []';
$yaml[] = 'tags:';
foreach (array_keys($allTags) as $tag) {
    $yaml[] = '  - name: ' . $tag;
}
$yaml[] = 'paths:';

foreach ($paths as $path => $ops) {
    $yaml[] = '  ' . yamlQuote($path) . ':';

    foreach ($ops as $op) {
        $method = $op['method'];
        $tag = opTag($path);
        $meta = parseActionMeta($op);
        $summary = strtoupper($method) . ' ' . $path;

        $yaml[] = '    ' . $method . ':';
        $yaml[] = '      tags:';
        $yaml[] = '        - ' . $tag;
        $yaml[] = '      summary: ' . yamlQuote($summary);
        $yaml[] = '      operationId: ' . operationId($op);
        $yaml[] = '      description: |';
        $yaml[] = '        Action: ' . $op['action'];
        $yaml[] = '        Capability: ' . $op['capability'];
        $yaml[] = '      x-token-capability: ' . yamlQuote($op['capability']);
        $yaml[] = '      x-ai-hints:';
        $yaml[] = '        controller: ' . yamlQuote($meta['controller']);
        $yaml[] = '        handler: ' . yamlQuote($meta['handler']);

        $params = extractPathParams($path);
        if (!empty($params)) {
            $yaml[] = '      parameters:';
            foreach ($params as $param) {
                $yaml[] = '        - name: ' . $param;
                $yaml[] = '          in: path';
                $yaml[] = '          required: true';
                $yaml[] = '          schema:';
                $yaml[] = '            type: string';
            }
        }

        if (in_array($method, ['post', 'put', 'patch', 'delete'], true)) {
            $yaml[] = '      requestBody:';
            $yaml[] = '        required: false';
            $yaml[] = '        content:';
            $yaml[] = '          application/json:';
            $yaml[] = '            schema:';
            $yaml[] = '              $ref: "#/components/schemas/FlexibleObject"';
            $yaml[] = '            examples:';
            $yaml[] = '              sample:';
            $yaml[] = '                value:';
            $yaml[] = '                  key: value';
        }

        if ($op['capability'] === 'public') {
            $yaml[] = '      security: []';
        } elseif ($op['capability'] === 'web-session') {
            $yaml[] = '      security:';
            $yaml[] = '        - SessionCookie: []';
            $yaml[] = '        - BearerAuth: []';
        } else {
            $yaml[] = '      security:';
            $yaml[] = '        - BearerAuth: []';
        }

        $yaml[] = '      responses:';
        $yaml[] = '        "200":';
        $yaml[] = '          description: success';
        $yaml[] = '          content:';
        $yaml[] = '            application/json:';
        $yaml[] = '              schema:';
        $yaml[] = '                $ref: "#/components/schemas/ApiResponse"';
        $yaml[] = '              examples:';
        $yaml[] = '                ok:';
        $yaml[] = '                  value:';
        $yaml[] = '                    code: 9999';
        $yaml[] = '                    msg: ok';
        $yaml[] = '                    result: {}';
        $yaml[] = '        "401":';
        $yaml[] = '          $ref: "#/components/responses/Unauthorized"';
        $yaml[] = '        "403":';
        $yaml[] = '          $ref: "#/components/responses/Forbidden"';
        $yaml[] = '        "422":';
        $yaml[] = '          $ref: "#/components/responses/ValidationError"';
        $yaml[] = '        default:';
        $yaml[] = '          $ref: "#/components/responses/GenericError"';
    }
}

$yaml[] = 'components:';
$yaml[] = '  securitySchemes:';
$yaml[] = '    BearerAuth:';
$yaml[] = '      type: http';
$yaml[] = '      scheme: bearer';
$yaml[] = '      bearerFormat: PAT_OR_UAT';
$yaml[] = '      description: PAT/UAT token in `Authorization: Bearer <token>`';
$yaml[] = '    SessionCookie:';
$yaml[] = '      type: apiKey';
$yaml[] = '      in: cookie';
$yaml[] = '      name: laravel_session';
$yaml[] = '  schemas:';
$yaml[] = '    ApiResponse:';
$yaml[] = '      type: object';
$yaml[] = '      additionalProperties: true';
$yaml[] = '      properties:';
$yaml[] = '        code:';
$yaml[] = '          type: integer';
$yaml[] = '          example: 9999';
$yaml[] = '        msg:';
$yaml[] = '          type: string';
$yaml[] = '          example: ok';
$yaml[] = '        result:';
$yaml[] = '          nullable: true';
$yaml[] = '          description: business payload (varies by endpoint)';
$yaml[] = '    ErrorResponse:';
$yaml[] = '      type: object';
$yaml[] = '      additionalProperties: true';
$yaml[] = '      properties:';
$yaml[] = '        code:';
$yaml[] = '          type: integer';
$yaml[] = '          example: 1000';
$yaml[] = '        msg:';
$yaml[] = '          type: string';
$yaml[] = '          example: unauthorized';
$yaml[] = '        result:';
$yaml[] = '          nullable: true';
$yaml[] = '    FlexibleObject:';
$yaml[] = '      type: object';
$yaml[] = '      additionalProperties: true';
$yaml[] = '  responses:';
$yaml[] = '    Unauthorized:';
$yaml[] = '      description: authentication required or token invalid';
$yaml[] = '      content:';
$yaml[] = '        application/json:';
$yaml[] = '          schema:';
$yaml[] = '            $ref: "#/components/schemas/ErrorResponse"';
$yaml[] = '    Forbidden:';
$yaml[] = '      description: token lacks required capability';
$yaml[] = '      content:';
$yaml[] = '        application/json:';
$yaml[] = '          schema:';
$yaml[] = '            $ref: "#/components/schemas/ErrorResponse"';
$yaml[] = '    ValidationError:';
$yaml[] = '      description: request validation failed';
$yaml[] = '      content:';
$yaml[] = '        application/json:';
$yaml[] = '          schema:';
$yaml[] = '            $ref: "#/components/schemas/ErrorResponse"';
$yaml[] = '    GenericError:';
$yaml[] = '      description: unexpected error';
$yaml[] = '      content:';
$yaml[] = '        application/json:';
$yaml[] = '          schema:';
$yaml[] = '            $ref: "#/components/schemas/ErrorResponse"';

file_put_contents($outFile, implode("\n", $yaml) . "\n");

echo "Generated: {$outFile}\n";
echo "Endpoints: " . count($entries) . "\n";
