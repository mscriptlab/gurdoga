<?php
namespace App\Models;

use App\Core\Database as DB;
use App\Core\Lang;

class Slider
{
    /** Active sliders with the current-language translation, in display order. */
    public static function activeList(?int $langId = null): array
    {
        $langId ??= Lang::id();
        $defId = Lang::default()['id'];
        return DB::all(
            'SELECT s.*,
                    COALESCE(NULLIF(st.eyebrow,""), st2.eyebrow)         AS eyebrow,
                    COALESCE(NULLIF(st.title,""), st2.title)             AS title,
                    COALESCE(NULLIF(st.description,""), st2.description) AS description,
                    COALESCE(NULLIF(st.button_text,""), st2.button_text) AS button_text,
                    COALESCE(NULLIF(st.link_url,""), st2.link_url)       AS link_url
             FROM sliders s
             LEFT JOIN slider_translations st  ON st.slider_id = s.id AND st.language_id = ?
             LEFT JOIN slider_translations st2 ON st2.slider_id = s.id AND st2.language_id = ?
             WHERE s.is_active = 1
             ORDER BY s.sort, s.id',
            [$langId, $defId]
        );
    }

    public static function allForAdmin(): array
    {
        $def = Lang::default()['id'];
        return DB::all(
            'SELECT s.*, st.title
             FROM sliders s
             LEFT JOIN slider_translations st ON st.slider_id = s.id AND st.language_id = ?
             ORDER BY s.sort, s.id',
            [$def]
        );
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM sliders WHERE id = ?', [$id]);
    }

    public static function translations(int $id): array
    {
        $rows = DB::all('SELECT * FROM slider_translations WHERE slider_id = ?', [$id]);
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['language_id']] = $r;
        }
        return $out;
    }
}
