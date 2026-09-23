<?php
namespace App\Controllers\Site;

use App\Core\Lang;
use App\Core\Mailer;
use App\Core\Seo;
use App\Core\Settings;
use App\Models\Product;
use App\Models\QuoteRequest;

class ContactController extends SiteController
{
    public function show(): string
    {
        Seo::title(t('nav.contact', 'Contact'));
        Seo::description(Settings::get('contact_meta_description', t('contact.intro', 'Get in touch with Gürdoğa Kooperatifi.')));
        $this->localize('contact');
        $crumbs = $this->breadcrumb([
            ['name' => t('nav.home', 'Home'), 'url' => abs_url(Lang::code())],
            ['name' => t('nav.contact', 'Contact'), 'url' => null],
        ]);
        return $this->view('site/contact', ['mode' => 'contact', 'crumbs' => $crumbs]);
    }

    public function submit(): string
    {
        return $this->handle('contact', 'contact');
    }

    public function quote(): string
    {
        $productId = (int) $this->request->input('product', 0);
        $product = $productId ? Product::find($productId) : null;
        Seo::title(t('quote.title', 'Request a Quote'));
        Seo::$robots = 'noindex,follow';
        $this->localize('quote');
        return $this->view('site/contact', ['mode' => 'quote', 'product' => $product]);
    }

    public function submitQuote(): string
    {
        return $this->handle('quote', 'quote');
    }

    private function handle(string $type, string $view): string
    {
        $req = $this->request;
        $data = [
            'name'    => trim((string) $req->input('name', '')),
            'company' => trim((string) $req->input('company', '')),
            'email'   => trim((string) $req->input('email', '')),
            'phone'   => trim((string) $req->input('phone', '')),
            'country' => trim((string) $req->input('country', '')),
            'message' => trim((string) $req->input('message', '')),
        ];
        $productId = (int) $req->input('product_id', 0) ?: null;

        $errors = [];
        if ($data['name'] === '')  { $errors['name'] = t('form.required', 'This field is required.'); }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = t('form.email_invalid', 'Enter a valid email address.'); }
        if (mb_strlen($data['message']) < 10) { $errors['message'] = t('form.message_short', 'Please write a bit more.'); }

        // Honeypot + timing + rate-limit
        if (trim((string) $req->input('website', '')) !== '') {
            $errors['_spam'] = 'spam';
        }
        if (QuoteRequest::recentlyFrom($req->ip(), 30) > 2) {
            $errors['_spam'] = 'rate';
        }

        if ($errors) {
            if ($req->isAjax()) {
                unset($errors['_spam']);
                return $this->json([
                    'ok'      => false,
                    'errors'  => $errors,
                    'message' => t('form.fix_errors', 'Please correct the errors below.'),
                ], 422);
            }
            remember_old($data);
            flash('errors', $errors);
            flash('form_error', t('form.fix_errors', 'Please correct the errors below.'));
            redirect(lang_url($type) . ($productId ? '?product=' . $productId : ''));
        }

        $id = QuoteRequest::create($data + [
            'type'       => $type,
            'product_id' => $productId,
            'locale'     => Lang::code(),
            'ip'         => $req->ip(),
        ]);

        $to = Settings::raw('quote_recipients');
        $to = $to ? array_map('trim', explode(',', $to)) : (config('mail.to') ?: []);
        $productName = $productId ? (Product::find($productId)['sku'] ?? ('#' . $productId)) : '-';
        $body = '<h2>' . e(ucfirst($type)) . ' request #' . $id . '</h2><table cellpadding="6">'
            . '<tr><td><b>Name</b></td><td>' . e($data['name']) . '</td></tr>'
            . '<tr><td><b>Company</b></td><td>' . e($data['company']) . '</td></tr>'
            . '<tr><td><b>Email</b></td><td>' . e($data['email']) . '</td></tr>'
            . '<tr><td><b>Phone</b></td><td>' . e($data['phone']) . '</td></tr>'
            . '<tr><td><b>Country</b></td><td>' . e($data['country']) . '</td></tr>'
            . '<tr><td><b>Product</b></td><td>' . e((string) $productName) . '</td></tr>'
            . '<tr><td><b>Locale</b></td><td>' . e(Lang::code()) . '</td></tr>'
            . '</table><p>' . nl2br(e($data['message'])) . '</p>';
        if ($to) {
            Mailer::send($to, '[Gürdoğa] ' . ucfirst($type) . ' request from ' . $data['name'], $body, $data['email']);
        }

        clear_old();
        if ($req->isAjax()) {
            return $this->json(['ok' => true, 'message' => t('form.thanks', 'Thank you — we will get back to you shortly.')]);
        }
        flash('success', t('form.thanks', 'Thank you — we will get back to you shortly.'));
        redirect(lang_url($type === 'quote' ? 'quote' : 'contact'));
    }
}
