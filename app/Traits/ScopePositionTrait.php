<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class ScopePositionTrait
 *
 * @package App\Traits
 */
trait ScopePositionTrait {

    /**
     * 查询推荐位的数据
     * @param $query 支持多种传参方式， 如：
    //      ->position(1,2,4,6)
    //      ->position([1,2,4,6])
    //      ->position('home', 'list')
    //      ->position(['home', 'list'])
     * @param $position
     */
    public static function scopePosition(Builder $query, $position)
    {
        if (!is_array($position)) {
            $position = array_slice(func_get_args(), 1);
        }
        $query->where(function(Builder $q) use ($position) {
            foreach ((array) $position as $k => $v) {
                $q->orWhereJsonContains("position", $v);
            }
        });
    }


    /**
     * 获取推荐位数组
     * @param $value
     *
     * @return mixed
     */
    public function getPositionAttribute($value)
    {
        return json_decode($value, true);
    }


    /**
     * 设置推荐位
     * @param array $value 如：['home', 'list']、[1,3,4,5]
     */
    public function setPositionAttribute($value = [])
    {
        if (is_array($value)) {
            //            $position = [];
            //            foreach ($value as $k => $v) {
            //                $position['pos__' . $v] = true;
            //            }
            $this->attributes['position'] = json_encode($value, JSON_THROW_ON_ERROR);
        } else {
            $this->attributes['position'] = null;
        }
    }

}
