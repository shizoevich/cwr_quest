<?php

namespace App\Components\Square;

use App\Repositories\Square\ApiRepositoryInterface;

abstract class AbstractSquare
{
    protected $squareApi;
    
    public function __construct()
    {
        $this->squareApi = app()->make(ApiRepositoryInterface::class);
    }
}