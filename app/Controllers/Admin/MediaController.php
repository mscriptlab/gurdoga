<?php
namespace App\Controllers\Admin;

use App\Core\Image;

class MediaController extends AdminController
{
    /** Generic image upload used by the rich-text editor's "insert image" button. */
    public function upload(): string
    {
        $file = $this->request->files['file'] ?? null;
        if (!$file) {
            return $this->json(['ok' => false, 'message' => 'Dosya bulunamadı.'], 422);
        }
        try {
            $res = Image::ingest($file, pathinfo($file['name'] ?? 'img', PATHINFO_FILENAME));
        } catch (\Throwable $e) {
            return $this->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }
        return $this->json(['ok' => true, 'path' => $res['path'], 'url' => media($res['path'])]);
    }
}
