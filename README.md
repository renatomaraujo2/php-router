## Router
simple router class for php

[![Total Downloads](https://poser.pugx.org/izniburak/pdox-orm/d/total.svg)](https://packagist.org/packages/izniburak/router)
[![Latest Stable Version](https://poser.pugx.org/izniburak/pdox-orm/v/stable.svg)](https://packagist.org/packages/izniburak/router)
[![Latest Unstable Version](https://poser.pugx.org/izniburak/pdox-orm/v/unstable.svg)](https://packagist.org/packages/izniburak/router)
[![License](https://poser.pugx.org/izniburak/pdox-orm/license.svg)](https://packagist.org/packages/izniburak/router)

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

## Example Usage
```php
require 'vendor/autoload.php';

$router = new \Buki\Router();

$router->get('/', function() {
    return 'Hello World!';
});
```

## Docs
Documentation page: [Buki\Router Docs][doc-url]

## Support
[izniburak's homepage][author-url]

[izniburak's twitter][twitter-url]

## Licence
[MIT Licence][mit-url]

## Contributing

1. Fork it ( https://github.com/izniburak/router/fork )
2. Create your feature branch (git checkout -b my-new-feature)
3. Commit your changes (git commit -am 'Add some feature')
4. Push to the branch (git push origin my-new-feature)
5. Create a new Pull Request

## Contributors

- [izniburak](https://github.com/izniburak) İzni Burak Demirtaş - creator, maintainer

[mit-url]: http://opensource.org/licenses/MIT
[doc-url]: https://github.com/izniburak/router/blob/master/DOCS.md
[author-url]: http://burakdemirtas.org
[twitter-url]: https://twitter.com/izniburak
