<?php
namespace App\Controllers\Admin;

use App\Core\Database as DB;
use App\Core\Settings;
use App\Models\Language;

/**
 * The public "Contact" / "Request a quote" pages are rendered by a controller,
 * not stored as editable `pages` rows — their text comes from interface strings
 * and settings. This screen gathers all of it in one place.
 */
class ContactPageController extends AdminController
{
    /** interface-string keys shown here (translations table) */
    private const TKEYS = [
        'contact.title'      => 'Sayfa başlığı (H1)',
        'contact.intro'      => 'Giriş cümlesi',
        'contact.info_title' => 'Bilgi kartı başlığı',
        'contact.form_title' => 'Form başlığı',
        'contact.form_intro' => 'Form açıklaması',
        'contact.hours'      => '“Çalışma saatleri” etiketi',
        'quote.title'        => 'Teklif sayfası başlığı',
        'quote.intro'        => 'Teklif sayfası girişi',
    ];
    /** plain settings */
    private const KEYS = [
        'contact_email', 'contact_phone', 'contact_phone2', 'working_hours',
        'office1_label', 'office1_address', 'office2_label', 'office2_address',
        'map_embed', 'quote_recipients',
    ];

    public function index(): string
    {
        $langs = Language::all();
        $tr = [];
        foreach (DB::all('SELECT language_id, `key`, `value` FROM translations WHERE `key` IN ("' . implode('","', array_keys(self::TKEYS)) . '")') as $r) {
            $tr[$r['key']][(int) $r['language_id']] = $r['value'];
        }
        $meta = [];
        foreach (DB::all('SELECT language_id, `value` FROM setting_translations WHERE `key` = "contact_meta_description"') as $r) {
            $meta[(int) $r['language_id']] = $r['value'];
        }

        return $this->render('contact-page/index', [
            'langs'    => $langs,
            'tkeys'    => self::TKEYS,
            'tr'       => $tr,
            'meta'     => $meta,
            'settings' => Settings::all(),
        ]);
    }

    public function save(): string
    {
        $r = $this->request;

        foreach (self::KEYS as $k) {
            if (array_key_exists($k, (array) $r->post)) {
                Settings::set($k, trim((string) $r->post[$k]));
            }
        }

        // interface strings
        foreach ((array) ($r->post['t'] ?? []) as $key => $byLang) {
            if (!isset(self::TKEYS[$key])) {
                continue;
            }
            foreach ((array) $byLang as $lid => $val) {
                $lid = (int) $lid;
                $val = trim((string) $val);
                $id = DB::value('SELECT id FROM translations WHERE `key` = ? AND language_id = ?', [$key, $lid]);
                if ($val === '') {
                    if ($id) { DB::delete('translations', 'id = :id', ['id' => $id]); }
                } elseif ($id) {
                    DB::update('translations', ['value' => $val], 'id = :id', ['id' => $id]);
                } else {
                    DB::query('INSERT INTO translations (language_id, `key`, `value`) VALUES (?,?,?)', [$lid, $key, $val]);
                }
            }
        }

        // meta description (per language)
        foreach ((array) ($r->post['meta'] ?? []) as $lid => $val) {
            Settings::setTranslation('contact_meta_description', (int) $lid, trim((string) $val));
        }

        $this->redirectWith('/admin/contact-page', 'success', 'İletişim sayfası kaydedildi.');
    }
}
