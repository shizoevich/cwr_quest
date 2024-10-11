<?php

namespace App\Console\Commands\Cache;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ClearCache extends Command
{
    protected $signature = 'clear:cache-one-day {--clear-dir=false}';

    public function handle(): int
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        $isDeleteDirsAndFiles = $this->option('clear-dir');

        if ($isDeleteDirsAndFiles !== 'false') {
            $cacheDisk = [
                'driver' => 'local',
                'root' => config('cache.stores.file.path')
            ];

            config(['filesystems.disks.fcache' => $cacheDisk]);

            $directories = Storage::disk('fcache')->allDirectories();

            foreach ($directories as $directory) {
                if (Storage::disk('fcache')->exists($directory)) {
                    Storage::disk('fcache')->deleteDirectory($directory);
                }
            }
        }

        return 1;
    }
}
