<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
  $map = ['Core\\'=>__DIR__.'/../core/','App\\'=>__DIR__.'/../app/'];
  foreach($map as $prefix=>$base){
    if(str_starts_with($class,$prefix)){
      $path=$base.str_replace('\\','/',substr($class,strlen($prefix))).'.php';
      if(is_file($path)) require $path;
    }
  }
});

// Load .env
$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
  foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $_ENV[$k] = $v;
  }
}

$routes = [];
require __DIR__ . '/../routes/web.php';

$uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path = ($base && str_starts_with($uri, $base)) ? substr($uri, strlen($base)) : $uri;
if ($path === false) $path = '/';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$handler = $routes[$method][$path] ?? null;
if (!$handler) { http_response_code(404); echo "404 Not Found"; exit; }

echo $handler();
