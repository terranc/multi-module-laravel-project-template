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

    // 定义全局的 scope，有别于 globalScopes，方便扩展类重载

    public static function removeGlobalScope($scope) {
        if (isset(static::$globalScopes[static::class][$scope])) {
            unset(static::$globalScopes[static::class][$scope]);
        }
    }

    protected static function boot() {
        parent::boot();
        $scopes = (new static)->scopes;
        foreach ($scopes as $key => $scope) {
            static::addGlobalScope(new $scope);
        }
    }

    public function getMorphClassAlias($class) {
        $morphMap = Relation::morphMap();

        if (!empty($morphMap) && in_array($class, $morphMap)) {
            return array_search($class, $morphMap, true);
        }

        return $class;
    }

    protected function asJson($value) {
        return json_encode($value, JSON_NUMERIC_CHECK);
    }

    /**
     * 判断是否需要加载全局查询作用域（默认对于非 Http\Controller\Admin 的查询自动添加全局作用域）
     *
     * @return bool
     */
    protected function isAddGlobalScopes() {
        $route = request()->route();
        return !($route && Str::is("App\Http\Controllers\Admin*", $route->getAction()['namespace']) === false);
    }
}
