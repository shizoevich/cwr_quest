<?php

namespace App\Http\RequestLogging;

use App\Contracts\Http\RequestLogging\Store;

class NullStore implements Store
{

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
    
    }

}