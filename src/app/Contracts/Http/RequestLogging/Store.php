<?php
/**
 * Created by PhpStorm.
 * User: zotov_000
 * Date: 07.07.2015
 * Time: 11:58
 */

namespace App\Contracts\Http\RequestLogging;


interface Store
{

    /**
     * @param array $data
     * @return mixed
     */
    public function save(array $data);
}