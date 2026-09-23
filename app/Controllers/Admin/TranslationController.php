<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Models\Language;

class TranslationController extends AdminController
{
    /** UI string keys the theme uses. Missing rows fall back to these defaults. */
    public const DEFAULTS = [
        'nav.home' => 'Home', 'nav.products' => 'Products', 'nav.about' => 'About',
        'nav.contact' => 'Contact', 'nav.quote' => 'Request a Quote',
        'nav.corporate' => 'Corporate', 'nav.production' => 'Production',
        'products.intro' => 'Explore our home textile collections.',
        'products.all' => 'All Products', 'products.in_category' => 'products', 'products.items' => 'items',
        'product.request_quote' => 'Request a quote for this product',
        'product.specs' => 'Specifications', 'product.gallery' => 'Gallery',
        'product.buy_now' => 'Buy now', 'product.related' => 'You may also like',
        'product.related_eyebrow' => 'More from us',
        'quote.title' => 'Request a Quote', 'quote.intro' => 'Tell us what you need and we will prepare an offer.',
        'contact.title' => 'Get in touch',
        'contact.intro' => 'Contact our team for product groups, custom production and sample requests.',
        'contact.info_title' => 'Contact details', 'contact.form_title' => 'Send us a message',
        'contact.form_intro' => 'Fill in the form and we will reply as soon as possible.',
        'contact.hours' => 'Working hours',
        'form.name' => 'Full name', 'form.company' => 'Company', 'form.email' => 'Email',
        'form.phone' => 'Phone', 'form.country' => 'Country', 'form.message' => 'Message',
        'form.send' => 'Send', 'form.required' => 'This field is required.',
        'form.email_invalid' => 'Enter a valid email address.',
        'form.message_short' => 'Please write a bit more.',
        'form.fix_errors' => 'Please correct the errors below.',
        'form.thanks' => 'Thank you — we will get back to you shortly.',
        'form.thanks_title' => 'Thank you',
        'search.title' => 'Search', 'search.placeholder' => 'Search products…',
        'search.no_results' => 'No products found.',
        'footer.rights' => 'All rights reserved.',
        'footer.follow' => 'Follow', 'footer.reach' => 'Get in touch',
        'footer.address' => 'Address', 'footer.company' => 'Company',
        'footer.eyebrow' => 'Cooperative', 'footer.made_by' => 'Design',
        'footer.headline_1' => 'From nature,', 'footer.headline_2' => 'to your table.',
        'footer.tagline_caps' => 'Natural & local products',
        'home.featured' => 'Featured Products', 'home.categories' => 'Collections',
        'home.cta' => 'Request a Quote', 'common.read_more' => 'Read more',
        'common.back_to' => 'Back to', 'common.discover' => 'Discover', 'common.view' => 'View',
        'common.close' => 'Close', 'common.skip' => 'Skip to content', 'common.back_to_top' => 'Back to top',
        'home.collections_title' => 'Products for every table',
        'home.cta_title' => 'Let us prepare the best for you',
        'home.featured_title' => 'Selected from our range',
        'home.badge_n' => '100%', 'home.badge_l' => 'natural',
        'home.stat_lines' => 'product groups', 'home.stat_sites' => 'producer partnership',
        'home.stat_certs' => 'fast delivery',
        'home.why_eyebrow' => 'Why us', 'home.why_title' => 'From field to table, with trust',
        'home.value_1_t' => 'Cooperative production', 'home.value_1_d' => 'Our products come from cooperative member producers, the source is known.',
        'home.value_2_t' => 'Natural & additive-free', 'home.value_2_d' => 'We produce with traditional methods, without additives.',
        'home.value_3_t' => 'Easy purchase', 'home.value_3_d' => 'Order the product you like with one click via our delivery partner.',
        'home.value_4_t' => 'On-site sales points', 'home.value_4_d' => 'You can also get our products from our sales points.',
        'blog.eyebrow' => 'Knowledge', 'blog.title' => 'Blog', 'blog.intro' => 'News from our cooperative and articles about our products.',
        'blog.empty' => 'No articles yet.', 'blog.more' => 'More articles',
        'blog.cta' => 'Want to know more about our products?',
        'topbar.note' => 'Natural and local products',
        'error.404_title' => 'Page not found',
        'error.404_text' => 'The page you are looking for does not exist.',
        'faq.eyebrow' => 'FAQ', 'faq.title' => 'Frequently asked questions',
    ];

    public function index(): string
    {
        $langs = Language::all();
        $existing = [];
        foreach (DB::all('SELECT language_id, `key`, `value` FROM translations') as $r) {
            $existing[$r['key']][(int) $r['language_id']] = $r['value'];
        }
        $keys = array_unique(array_merge(array_keys(self::DEFAULTS), array_keys($existing)));
        sort($keys);
        return $this->render('translations/index', [
            'langs'    => $langs,
            'keys'     => $keys,
            'existing' => $existing,
            'defaults' => self::DEFAULTS,
        ]);
    }

    public function save(): string
    {
        $data = (array) ($this->request->post['t'] ?? []); // t[key][langId] = value
        foreach ($data as $key => $byLang) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }
            foreach ((array) $byLang as $lid => $val) {
                $lid = (int) $lid;
                $val = (string) $val;
                $ex = DB::value('SELECT id FROM translations WHERE `key` = ? AND language_id = ?', [$key, $lid]);
                if ($val === '') {
                    if ($ex) {
                        DB::delete('translations', 'id = :id', ['id' => $ex]);
                    }
                    continue;
                }
                if ($ex) {
                    DB::update('translations', ['value' => $val], 'id = :id', ['id' => $ex]);
                } else {
                    DB::query('INSERT INTO translations (language_id, `key`, `value`) VALUES (?, ?, ?)', [$lid, $key, $val]);
                }
            }
        }
        $this->redirectWith('/admin/translations', 'success', 'Arayüz metinleri kaydedildi.');
    }
}
