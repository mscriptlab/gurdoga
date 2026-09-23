<?php
namespace App\Core;

abstract class Controller
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function view(string $template, array $data = [], ?string $layout = 'site'): string
    {
        return View::render($template, $data, $layout);
    }

    protected function json($data, int $code = 200): string
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function notFound()/*: never */
    {
        throw new HttpNotFound();
    }

    protected function back()/*: never */
    {
        redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
    }
}
