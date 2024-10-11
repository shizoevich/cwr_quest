<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class GenerateUserSignature implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId) {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $user = User::withTrashed()->findOrFail($this->userId);
        if(!$user->isProviderAttached() || $user->isAdmin()) {
            return false;
        }

        $fileName = md5(uniqid(time())) . '.png';

        $providerName = $user->provider()->withTrashed()->first()->provider_name;
        $width = strlen($providerName) * 53;
        $height = 400;
        $img = Image::canvas($width, $height);
        $imgString = (string)$img->text($providerName, $width/2, $height, function($font) {
            $font->file(public_path('fonts/DancingScript-Regular.otf'));
            $font->size(110);
            $font->color('#000000');
            $font->align('center');
            $font->valign('bottom');
        })->encode('png');
        if(Storage::disk('signatures')->put($fileName, $imgString)) {
            $user->meta()->withTrashed()->update(['signature' => $fileName]);
        }
    }
}
