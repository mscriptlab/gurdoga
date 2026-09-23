<?php
namespace App\Core;

/**
 * Handles uploaded images: validates, stores original as JPEG, and generates
 * WebP + responsive width variants. Returns a relative path like
 * "uploads/2026/08/slug-abc123.jpg" (the "base" file). Variants live next to it:
 *   slug-abc123.jpg           (max 1600w JPEG, always)
 *   slug-abc123.webp          (max 1600w WebP)
 *   slug-abc123-800.webp / -400.webp / -800.jpg / -400.jpg
 */
class Image
{
    public const WIDTHS = [1600, 800, 400];

    /** @return array{path:string,width:int,height:int}  ('path' is the .jpg base) */
    public static function ingest(array $file, string $slugHint = 'img'): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (code ' . ($file['error'] ?? '?') . ').');
        }
        $tmp = $file['tmp_name'];
        $info = @getimagesize($tmp);
        if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            throw new \RuntimeException('Unsupported image type.');
        }
        $src = self::load($tmp, $info[2]);
        if (!$src) {
            throw new \RuntimeException('Could not read image.');
        }
        return self::store($src, $slugHint);
    }

    /** Import a file already on disk (used by the media import script). Supports TIFF via Imagick. */
    public static function importFromPath(string $srcPath, string $slugHint = 'img'): array
    {
        $ext = strtolower(pathinfo($srcPath, PATHINFO_EXTENSION));
        $img = null;
        $info = @getimagesize($srcPath);
        if ($info !== false && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
            $img = self::load($srcPath, $info[2]);
        } elseif (in_array($ext, ['tif', 'tiff'], true) && class_exists('Imagick')) {
            $im = new \Imagick();
            $im->readImage($srcPath . '[0]');
            $im->setImageFormat('jpeg');
            $img = imagecreatefromstring($im->getImageBlob());
            $im->clear();
        }
        if (!$img) {
            throw new \RuntimeException('Unreadable image (' . $ext . ').');
        }
        return self::store($img, $slugHint);
    }

    private static function load(string $path, int $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG: return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:  return imagecreatefrompng($path);
            case IMAGETYPE_WEBP: return imagecreatefromwebp($path);
            case IMAGETYPE_GIF:  return imagecreatefromgif($path);
            default:             return null;
        }
    }

    private static function store($src, string $slugHint): array
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $dir = 'uploads/' . date('Y/m');
        $abs = BASE_DIR . '/' . $dir;
        if (!is_dir($abs)) {
            mkdir($abs, 0775, true);
        }
        $name = slugify($slugHint) . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
        $baseRel = $dir . '/' . $name . '.jpg';

        $maxW = self::WIDTHS[0];
        [$bw, $bh] = ($w > $maxW) ? [$maxW, (int) round($h * $maxW / $w)] : [$w, $h];
        $baseImg = self::resample($src, $bw, $bh);
        imagejpeg($baseImg, BASE_DIR . '/' . $baseRel, 82);
        if (function_exists('imagewebp')) {
            imagewebp($baseImg, BASE_DIR . '/' . $dir . '/' . $name . '.webp', 80);
        }

        foreach ([800, 400] as $vw) {
            if ($bw <= $vw) {
                continue;
            }
            $vh = (int) round($bh * $vw / $bw);
            $v = self::resample($baseImg, $vw, $vh);
            imagejpeg($v, BASE_DIR . '/' . $dir . '/' . $name . '-' . $vw . '.jpg', 80);
            if (function_exists('imagewebp')) {
                imagewebp($v, BASE_DIR . '/' . $dir . '/' . $name . '-' . $vw . '.webp', 78);
            }
            imagedestroy($v);
        }
        imagedestroy($baseImg);
        imagedestroy($src);

        return ['path' => $baseRel, 'width' => $bw, 'height' => $bh];
    }

    private static function resample($src, int $w, int $h)
    {
        $dst = imagecreatetruecolor($w, $h);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
        return $dst;
    }

    /** Build a <picture>/<img> srcset HTML block for a stored base path. */
    public static function tag(string $basePath, string $alt, array $attr = []): string
    {
        $noExt = preg_replace('/\.jpg$/', '', $basePath);
        $b = fn($s) => htmlspecialchars($s, ENT_QUOTES);
        $webp = [];
        $jpg  = [];
        foreach (['-400' => 400, '-800' => 800, '' => 1600] as $suf => $wv) {
            // Small source images never get a -400/-800 variant (store() skips
            // upscaling), and .webp is only written when the server's GD build
            // supports it — only offer a candidate that actually exists on disk,
            // otherwise the browser can pick a 404 and render a broken image.
            if (is_file(BASE_DIR . '/' . $noExt . $suf . '.webp')) {
                $webp[] = media($noExt . $suf . '.webp') . ' ' . $wv . 'w';
            }
            if (is_file(BASE_DIR . '/' . $noExt . $suf . '.jpg')) {
                $jpg[] = media($noExt . $suf . '.jpg') . ' ' . $wv . 'w';
            }
        }
        $sizes = $attr['sizes'] ?? '(max-width: 768px) 100vw, 800px';
        $cls   = isset($attr['class']) ? ' class="' . $b($attr['class']) . '"' : '';
        $dim   = '';
        if (!empty($attr['width']))  { $dim .= ' width="' . (int) $attr['width'] . '"'; }
        if (!empty($attr['height'])) { $dim .= ' height="' . (int) $attr['height'] . '"'; }
        $loading = $attr['loading'] ?? 'lazy';
        $source  = $webp ? '<source type="image/webp" srcset="' . $b(implode(', ', $webp)) . '" sizes="' . $b($sizes) . '">' : '';
        $srcset  = $jpg ? ' srcset="' . $b(implode(', ', $jpg)) . '" sizes="' . $b($sizes) . '"' : '';

        return '<picture>'
            . $source
            . '<img src="' . $b(media($basePath)) . '"' . $srcset
            . ' alt="' . $b($alt) . '" loading="' . $b($loading) . '" decoding="async"' . $dim . $cls . '>'
            . '</picture>';
    }
}
