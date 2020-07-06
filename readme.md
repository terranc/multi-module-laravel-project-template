## Installation

```shell
cp .env.example .env
# 配置.env
php artisan key:generate
php artisan migrate --seed
# 打开 http://xxxxx.test/admin
# admin/admin
```

## Usage

```php
// Cache
\CacheClient::put(fmt(CacheService::KEY_USER_INFO, 123), $user);    // 所有缓存Key必须统一在 CacheService 中定义

// Exception
throw new ApiNotFoundException('无可用记录');    // 404
throw new ApiRequestException();    // 400
throw new ApiSystemException();     // 500
throw new ApiUnAuthException();     // 401
```
