<?php

namespace App\Cleaners;

use Hhxsv5\LaravelS\Illuminate\Cleaners\LaravelAdminCleaner as LaravelAdminCleanerBase;

class LaravelAdminCleaner extends LaravelAdminCleanerBase {
    protected $properties = [
        'deferredScript' => [],
        'script'         => [],
        'style'          => [],
        'css'            => [],
        'js'             => [],
        'html'           => [],
        'headerJs'       => [],
        'manifestData'   => [],
        'minifyIgnores'  => [],
    ];
}
