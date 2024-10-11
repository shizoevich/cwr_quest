<?php

namespace App\Jobs;

use App\Helpers\ImageHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MakeImageThumbnail implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $normalImageWidth;
    private $normalImageHeight;

    private $fileName;
    private $disk;

    private $allowedExt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileName, $disk = 'patients_docs', $normalImageWidth = 150, $normalImageHeight = 150) {
        $this->fileName = $fileName;
        $this->disk = $disk;
        $this->normalImageWidth = $normalImageWidth;
        $this->normalImageHeight = $normalImageHeight;
        $this->allowedExt = [
            'jpg',
            'jpeg',
            'png',
            'gif',
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $disk = Storage::disk($this->disk);
        if ($disk->exists($this->fileName)) {
//            $mime = $disk->mimeType($this->fileName);
//            if (preg_match('/^image\\//', $mime) && !preg_match('/bmp/', $mime)) {
            $ext = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
            if (in_array($ext, $this->allowedExt)) {
                $preview = $disk->get($this->fileName);
                $img = Image::make($preview);
                $width = $img->width();
                $height = $img->height();
                $temp = $this->getNewImageSize($width, $height);


                $img = $img->resize($temp['w'], $temp['h']);

                $preview = (string)$img->encode($ext);
                $disk->put('thumbnails/' . $this->fileName, $preview);
            } else if($ext === 'pdf1') {    //change "pdf1" to "pdf" after fix bug with generating pdf thumbnails
                $tempPdfDisk = Storage::disk('temp_pdf');
                $tempPdfUrl = config('filesystems.disks.temp_pdf.root');
                $tempPdfDisk->put($this->fileName, $disk->get($this->fileName));
                $baseFileName = pathinfo($this->fileName, PATHINFO_FILENAME);
                $previewFileName = $baseFileName . '.jpg';
                exec("convert $tempPdfUrl/{$this->fileName}[0] $tempPdfUrl/$previewFileName");
                if($tempPdfDisk->exists($previewFileName)) {
                    $preview = $tempPdfDisk->get($previewFileName);
                    $img = Image::make($preview);
                    $width = $img->width();
                    $height = $img->height();
                    $temp = $this->getNewImageSize($width, $height, true);


                    $img = $img->resize($temp['w'], $temp['h']);

                    $preview = (string)$img->encode('jpg');
                    $disk->put('thumbnails/' . $previewFileName, $preview);
                }
                $tempPdfDisk->delete($previewFileName);
                $tempPdfDisk->delete($this->fileName);
            }
        }

    }

    private function getNewImageSize($width, $height, $isPdf = false) {
        if($isPdf) {
            $this->normalImageWidth = 300;
            $this->normalImageHeight = 300;
        }
        if ($width !== $height) {
            if ($width > $height) {
                $newWidth = $this->normalImageWidth;
                $newHeight = $height * $newWidth / $width;
            } else {
                $newHeight = $this->normalImageHeight;
                $newWidth = $width * $newHeight / $height;
            }
        } else {
            $newHeight = $this->normalImageHeight;
            $newWidth = $this->normalImageWidth;
        }
        return [
            'w' => $newWidth,
            'h' => $newHeight,
        ];
    }
}
