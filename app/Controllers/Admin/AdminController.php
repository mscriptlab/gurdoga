<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Lang;
use App\Core\License;
use App\Core\View;
use App\Models\Language;

abstract class AdminController extends Controller
{
    protected array $languages = [];

    public function __construct(\App\Core\Request $request)
    {
        parent::__construct($request);
        Auth::requireLogin();

        if (License::blocksAdmin()) {
            http_response_code(403);
            header('X-Robots-Tag: noindex');
            echo View::render('admin/license-block', ['state' => License::state()], null);
            exit;
        }
        View::share('licenseNotice', License::ok() ? null : License::message());

        $this->languages = Language::all();
        View::share('adminUser', Auth::user());
        View::share('newQuotes', \App\Models\QuoteRequest::countNew());
    }

    protected function render(string $template, array $data = []): string
    {
        $data['languages'] = $data['languages'] ?? $this->languages;
        return View::render('admin/' . $template, $data, 'admin');
    }

    /** Collect a per-language array of fields from POST: name="tr[$langId][slug]" */
    protected function langInput(string $key): array
    {
        return (array) ($this->request->post[$key] ?? []);
    }

    protected function redirectWith(string $to, string $type, string $msg)/*: never */
    {
        flash($type, $msg);
        redirect(url($to));
    }
}
