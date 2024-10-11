<?php
/**
 * Created by PhpStorm.
 * User: zotov_000
 * Date: 07.07.2015
 * Time: 12:00
 */

namespace App\Contracts\Http\RequestLogging;


interface Factory
{

    /**
     * Get a RequestLogging store instance by name.
     *
     * @param  string|null $name
     * @return mixed
     */
    public function store($name = null);
}