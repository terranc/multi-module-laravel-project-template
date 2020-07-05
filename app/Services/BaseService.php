<?php
namespace App\Services;
class BaseService
{
    public static function getInstance()
    {
        return new static;
    }
}
