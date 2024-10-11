<?php

namespace App\Helpers;


use App\Jobs\MakeImageThumbnail;
use Illuminate\Support\Facades\Storage;
use Sabre\DAV\Client;
use Sabre\DAV\MkCol;

class ImageHelper {
    public function __construct() {
    }

    public static function getImageThumbnailName($imageName) {
        $name = pathinfo($imageName, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        return "{$name}_thumbnail.{$ext}";
    }

    public static function getBase64ImageThumbnail($imageName, $generateIfNotExists = true, $diskName = 'patients_docs') {
        $disk = Storage::disk($diskName);
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $thumbnailName = 'thumbnails/';
        if($ext === 'pdf') {
            $thumbnailName .= pathinfo($imageName, PATHINFO_FILENAME) . '.jpg';
        } else {
            $thumbnailName .= $imageName;
        }

        if($generateIfNotExists && $disk->exists($imageName) && !$disk->exists($thumbnailName)) {
            \Bus::dispatchNow(new MakeImageThumbnail($imageName, $diskName));
        }

        if($disk->exists($thumbnailName)) {
            $mime = $disk->mimeType($thumbnailName);
            return 'data:' . $mime . ';base64,' . base64_encode($disk->get($thumbnailName));
        }
        return null;
    }
}