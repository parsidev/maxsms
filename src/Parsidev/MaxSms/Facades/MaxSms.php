<?php


namespace Parsidev\MaxSms\Facades;

use Illuminate\Support\Facades\Facade;

class MaxSms extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'maxsms';
    }
}