<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Lookfeel\AppendAutomate\AppendAutomateTrait;

// use App\Traits\ScopeFieldsTrait;

/**
 * App\Models\Base
 *
 * @mixin \Eloquent
 * @method static Builder|\App\Models\Base newModelQuery()
 * @method static Builder|\App\Models\Base newQuery()
 * @method static Builder|\App\Models\Base query()
 */
class Base extends Model {
    use AppendAutomateTrait;
    use Compoships;

    protected $guarded = ['password_confirmation', 'from_url'];
    protected $scopes = [];


    protected function asJson($value) {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_NUMERIC_CHECK);
    }
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
