<?php
namespace App\Core;

class Request
{
    public string $method;
    public string $path;        // path relative to base_path, leading slash, no query
    public array $query;
    public array $post;
    public array $files;

    public function __construct(string $basePath)
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri  = rawurldecode($uri);
        if ($basePath !== '' && strncmp($uri, $basePath, strlen($basePath)) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');
        if ($uri !== '/' ) {
            $uri = rtrim($uri, '/');
        }
        $this->path  = $uri === '' ? '/' : $uri;
        $this->query = $_GET;
        $this->post  = $_POST;
        $this->files = $_FILES;
    }

    public function input(string $key, $default = null)
    {
        return $this->post[$key] ?? $this->query[$key] ?? $default;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || (string) $this->input('ajax', '') === '1';
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
