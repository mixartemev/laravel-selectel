This is package for use [Selectel](https://selectel.com/) as CDN service for Laravel 5.*

Installation
------------

Install using composer:

```bash
composer require febalist/laravel-selectel
```

Add to config/filesystems.php in "disks" array:
```php
    'selectel' => [
        'driver'    => 'selectel',
        'username'  => env('SELECTEL_USERNAME'),
        'key'       => env('SELECTEL_SAPPWORD'),
        'container' => env('SELECTEL_CONTAINER'),
        'endpoint'  => 'https://auth.selcdn.ru',
        'region'   => 'aws-global',
    ],
```
Set "default", or "cloud" selectel storage.

And use: https://laravel.com/docs/filesystem
