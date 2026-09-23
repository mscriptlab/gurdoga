<?php
namespace App\Core;

class View
{
    private static array $shared = [];

    public static function share(string $key, $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        $data = array_merge(self::$shared, $data);
        $content = self::renderPartial($template, $data);
        if ($layout !== null) {
            $data['content'] = $content;
            return self::renderPartial('layouts/' . $layout, $data);
        }
        return $content;
    }

    public static function renderPartial(string $template, array $data = []): string
    {
        $data = array_merge(self::$shared, $data);
        $file = BASE_DIR . '/app/Views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $template");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
