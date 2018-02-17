```bash
composer require febalist/laravel-selectel 
``` 
 
```php
'selectel' => [
  'driver'    => 'selectel',
  'username'  => env('SELECTEL_USERNAME'),
  'password'  => env('SELECTEL_PASSWORD'),
  'container' => env('SELECTEL_CONTAINER'),
  'domain'    => env('SELECTEL_DOMAIN'),
  'ssl'       => env('SELECTEL_SSL', true),
],
``` 

```dotenv
SELECTEL_USERNAME=
SELECTEL_PASSWORD=
SELECTEL_CONTAINER=
```
