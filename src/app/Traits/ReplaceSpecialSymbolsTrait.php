<?php

namespace App\Traits;

trait ReplaceSpecialSymbolsTrait
{
    private function replaceSpecialCharacters(string $search): string
    {
        return str_replace(['%', '#', '&', '_', '+'], ['\\%', '\\#', '\\&', '\\_', '\\+'], $search);
    }
}
