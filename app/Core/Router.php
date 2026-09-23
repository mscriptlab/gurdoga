<?php
namespace App\Core;

class Router
{
    /** @var array<int,array{method:string,regex:string,params:string[],handler:mixed}> */
    private array $routes = [];

    public function add(string $method, string $pattern, $handler): void
    {
        $params = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_]+)(:[^}]+)?\}#', function ($m) use (&$params) {
            $params[] = $m[1];
            $sub = isset($m[2]) ? substr($m[2], 1) : '[^/]+';
            return '(' . $sub . ')';
        }, $pattern);
        $this->routes[] = [
            'method'  => strtoupper($method),
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function get(string $p, $h): void    { $this->add('GET', $p, $h); }
    public function post(string $p, $h): void   { $this->add('POST', $p, $h); }

    /**
     * @return array{handler:mixed,params:array<string,string>}|null
     */
    public function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);
        foreach ($this->routes as $r) {
            if ($r['method'] !== $method && !($r['method'] === 'GET' && $method === 'HEAD')) {
                continue;
            }
            if (preg_match($r['regex'], $path, $m)) {
                array_shift($m);
                $params = [];
                foreach ($r['params'] as $i => $name) {
                    $params[$name] = $m[$i] ?? '';
                }
                return ['handler' => $r['handler'], 'params' => $params];
            }
        }
        return null;
    }
}
