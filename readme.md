```bash
composer require febalist/laravel-selectel 
``` 
 
```php
'selectel' => [
  'driver' => 'selectel',
  'container' => env('SELECTEL_CONTAINER'),
  'username' => env('SELECTEL_USERNAME'),
  'password' => env('SELECTEL_PASSWORD'),
  'domain' => env('SELECTEL_DOMAIN'),
],
``` 

```dotenv
SELECTEL_CONTAINER=
SELECTEL_USERNAME=
SELECTEL_PASSWORD=
SELECTEL_DOMAIN=
```
