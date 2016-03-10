# Buki\Router Documentation

## Install
composer.json file:
```json
{
    "require": {
        "izniburak/router": "dev-master"
    }
}
```
after run the install command.
```
$ composer install
```

OR run the following command directly.

```
$ composer require izniburak/router:dev-master
```


## Quick Usage
```php
require 'vendor/autoload.php';

$router = new \Buki\Router();

$router->get('/', function() {
    return 'Hello World!';
});

$router->run();
```


Congratulations! Now, you can use Buki\Router.

If you have a problem, you can [contact me][support-url].



# Detailed Usage and Methods

Coming soon...

[support-url]: https://github.com/izniburak/PDOx#support
