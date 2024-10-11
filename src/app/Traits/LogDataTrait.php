<?php

namespace App\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

trait LogDataTrait
{
    private function logData(string $filepath, $data, string $type = 'INFO'): void
    {
        $message = '[' . now()->toDateTimeString() . '] ' . $type . ': ' . $this->formatLogData($data);

        file_put_contents(storage_path($filepath), $message . PHP_EOL, FILE_APPEND);
    }

    private function formatLogData($data)
    {
        if (is_array($data)) {
            return var_export($data, true);
        }
        if ($data instanceof Jsonable) {
            return $data->toJson();
        }
        if ($data instanceof Arrayable) {
            return var_export($data->toArray(), true);
        }

        return $data;
    }
}