<?php
namespace App\Core;

class App
{
    private array $config;
    private Router $router;
    private Request $request;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function run(): void
    {
        $sessName = $this->config['security']['session_name'] ?? 'gurdoga_sess';
        session_name($sessName);
        session_start();

        $this->request = new Request(rtrim($this->config['app']['base_path'] ?? '', '/'));

        try {
            Database::init($this->config['db']);
        } catch (\Throwable $e) {
            $this->bail500('Database connection failed. Copy <code>config/config.sample.php</code> to '
                . '<code>config/config.php</code>, create the database, and import <code>database/schema.sql</code> + '
                . '<code>database/seed.sql</code>.<br><br>' . ($this->config['app']['debug'] ? e($e->getMessage()) : ''));
            return;
        }

        try {
            $this->checkRedirects();
            Lang::boot();
            Settings::boot();
            License::boot();

            View::share('csrf', Csrf::token());

            $this->router = new Router();
            (require BASE_DIR . '/app/routes.php')($this->router);

            $this->dispatch();
        } catch (HttpNotFound $e) {
            $this->render404();
        } catch (\Throwable $e) {
            if ($this->config['app']['debug']) {
                $this->bail500('<pre>' . e($e) . '</pre>');
            } else {
                $this->bail500('An unexpected error occurred.');
            }
        }
    }

    private function checkRedirects(): void
    {
        $from = $this->request->path;
        $row = Database::one('SELECT * FROM redirects WHERE from_path = ? LIMIT 1', [$from]);
        if ($row) {
            Database::query('UPDATE redirects SET hits = hits + 1 WHERE id = ?', [$row['id']]);
            $to = $row['to_path'];
            if (!preg_match('#^https?://#', $to)) {
                $to = url($to);
            }
            redirect($to, (int) $row['code'] ?: 301);
        }
    }

    private function dispatch(): void
    {
        $path = $this->request->path;

        // Non-localized system routes (admin, sitemap, robots, assets fall through server)
        $system = $this->router->match($this->request->method, $path);
        if ($system && ($system['handler']['localized'] ?? true) === false) {
            if ($this->request->isPost()) {
                Csrf::verifyOrFail($this->request);
            }
            $this->invoke($system['handler'], $system['params']);
            return;
        }

        // "Under construction" splash — public site only; /admin, sitemap, robots already
        // handled above. A logged-in admin bypasses it to preview the real site.
        if ($this->maintenanceOn() && !Auth::check()) {
            http_response_code(503);
            header('Retry-After: 86400');
            header('X-Robots-Tag: noindex, nofollow');
            Lang::setCurrentByCode(Lang::default()['code']);
            echo View::render('site/coming-soon', [], null);
            return;
        }

        // Root -> default language home
        if ($path === '/' || $path === '') {
            redirect(lang_url('', Lang::default()['code']), 302);
        }

        // Expect /{lang}/...
        $seg = explode('/', ltrim($path, '/'), 2);
        $code = $seg[0];
        if (!Lang::setCurrentByCode($code)) {
            // maybe it's a system route without lang (admin/login etc already matched above if localized=false)
            if ($system) {
                $this->invoke($system['handler'], $system['params']);
                return;
            }
            throw new HttpNotFound();
        }

        View::share('lang', Lang::current());
        Seo::reset();

        $rest = '/' . ($seg[1] ?? '');
        $rest = $rest === '/' ? '/' : rtrim($rest, '/');

        // Localized URL segments: map the public segment back to the canonical one
        // the router knows, and 301 to the language's own segment if it doesn't match.
        $restTrim = ltrim($rest, '/');
        if ($restTrim !== '') {
            $canon = canon_path($restTrim);
            $desired = loc_path($canon, $code);
            if ($this->request->method === 'GET' && $desired !== $restTrim) {
                $qs = (string) ($_SERVER['QUERY_STRING'] ?? '');
                redirect(url($code . '/' . $desired) . ($qs !== '' ? '?' . $qs : ''), 301);
            }
            $rest = '/' . $canon;
        }

        $match = $this->router->match($this->request->method, $rest);
        if (!$match || ($match['handler']['localized'] ?? true) === false) {
            throw new HttpNotFound();
        }

        if ($this->request->isPost()) {
            Csrf::verifyOrFail($this->request);
        }
        $this->invoke($match['handler'], $match['params']);
    }

    /** "Under construction" mode: config flag OR the maintenance_mode admin setting. */
    private function maintenanceOn(): bool
    {
        if (!empty($this->config['app']['maintenance'])) {
            return true;
        }
        return (string) Settings::raw('maintenance_mode', '0') === '1';
    }

    private function invoke(array $handler, array $params): void
    {
        [$class, $method] = $handler['action'];
        $controller = new $class($this->request);
        echo $controller->$method(...array_values($params));
    }

    private function render404(): void
    {
        http_response_code(404);
        Seo::$robots = 'noindex,follow';
        if (!Lang::current()) {
            Lang::setCurrentByCode(Lang::default()['code']);
        }
        try {
            echo View::render('site/errors/404', [], 'site');
        } catch (\Throwable $e) {
            echo '<h1>404 Not Found</h1>';
        }
    }

    private function bail500(string $msg): void
    {
        http_response_code(500);
        echo '<!doctype html><meta charset="utf-8"><title>Gürdoğa Kooperatifi</title>'
            . '<div style="font:16px/1.6 system-ui;max-width:640px;margin:10vh auto;padding:0 20px">'
            . '<h1>Site is being set up</h1><p>' . $msg . '</p></div>';
    }
}
