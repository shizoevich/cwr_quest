<?php

namespace App\Http\Middleware;

use Closure;

class ValidatePostSize extends \Illuminate\Foundation\Http\Middleware\ValidatePostSize  {

    protected function getPostMaxSize()
    {
        if (is_numeric($postMaxSize = ini_get('post_max_size'))) {
            if((int) $postMaxSize < 64 * 1048576) {
                return 64 * 1048576;
            }
            return (int) $postMaxSize;
        }

        $metric = strtoupper(substr($postMaxSize, -1));

        if((int) $postMaxSize < 64) {
            $postMaxSize = 64;
        }

        switch ($metric) {
            case 'K':
                return (int) $postMaxSize * 1024;
            case 'M':
                return (int) $postMaxSize * 1048576;
            case 'G':
                return (int) $postMaxSize * 1073741824;
            default:
                return (int) $postMaxSize;
        }
    }

}
