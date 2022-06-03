<?php

namespace App\Facades;

use App\Services\Localization;
use Illuminate\Support\Facades\Facade;

class LocalizationService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Localization::class;
    }
}
