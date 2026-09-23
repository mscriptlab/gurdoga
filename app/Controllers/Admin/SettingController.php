<?php
namespace App\Controllers\Admin;

use App\Core\Image;
use App\Core\Settings;
use App\Models\Language;

class SettingController extends AdminController
{
    /** Plain (non-translatable) keys. */
    private const KEYS = [
        'site_name', 'contact_email', 'contact_phone', 'contact_phone2', 'contact_address',
        'office1_label', 'office1_address', 'office2_label', 'office2_address', 'working_hours',
        'social_linkedin', 'social_instagram', 'social_facebook', 'social_youtube',
        'ga4_id', 'gsc_verification', 'bing_verification', 'quote_recipients',
        'default_og_image', 'robots_txt', 'map_embed', 'meta_keywords', 'marquee_items', 'blog_meta_description',
        'years_established', 'stat_capacity', 'stat_countries',
    ];
    /** Translatable keys (per language). */
    private const TKEYS = [
        'site_tagline', 'footer_eyebrow', 'topbar_note', 'footer_about',
        'home_meta_title', 'home_meta_description', 'products_meta_description', 'contact_meta_description',
    ];

    public function index(): string
    {
        $strans = [];
        foreach (\App\Core\Database::all('SELECT `key`, language_id, `value` FROM setting_translations') as $r) {
            $strans[$r['key']][(int) $r['language_id']] = $r['value'];
        }
        return $this->render('settings/index', [
            'settings' => Settings::all(),
            'langs'    => Language::all(),
            'tkeys'    => self::TKEYS,
            'strans'   => $strans,
        ]);
    }

    public function save(): string
    {
        $r = $this->request;
        foreach (self::KEYS as $k) {
            if ($r->post !== null && array_key_exists($k, $r->post)) {
                Settings::set($k, trim((string) $r->post[$k]));
            }
        }
        // "Under construction" toggle (checkbox: absent = off)
        if ($r->post !== null && array_key_exists('_settings_form', $r->post)) {
            Settings::set('maintenance_mode', !empty($r->post['maintenance_mode']) ? '1' : '0');
        }
        foreach (self::TKEYS as $k) {
            foreach ((array) ($r->post['t'][$k] ?? []) as $lid => $val) {
                Settings::setTranslation($k, (int) $lid, trim((string) $val));
            }
        }
        foreach (['logo', 'favicon'] as $imgKey) {
            $f = $r->files[$imgKey] ?? null;
            if ($f && ($f['error'] ?? 4) === UPLOAD_ERR_OK) {
                try {
                    Settings::set($imgKey, Image::ingest($f, $imgKey)['path']);
                } catch (\Throwable $e) {
                    flash('error', ucfirst($imgKey) . ': ' . $e->getMessage());
                }
            }
        }
        $this->redirectWith('/admin/settings', 'success', 'Ayarlar kaydedildi.');
    }
}
