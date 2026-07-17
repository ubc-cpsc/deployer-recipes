# Deployer Recipes

This repository contains UBC CS department recipes to integrate with deployer.

## Installing

~~~sh
composer require ubc-cpsc/deployer-recipes:^3.0 --dev
~~~

## Compatibility

| Recipe version | Deployer version | PHP version |
|----------------|------------------|-------------|
| 3.x            | 8.x              | 8.3 or later |
| 2.x            | 7.x              | Supported by the selected Deployer 7 release |

The 2.x branch receives critical compatibility fixes for Deployer 7. New
development targets Deployer 8 on the 3.x branch.

Include recipes in `deploy.php` file.

```php
require 'recipes/drupal8.php';
```

## Recipes

| Recipe    | Docs                      |
|-----------|---------------------------|
| base      |  |
| cachetool | [read](docs/cachetool.md) |
| drupal    | |
| drupal7   |  |
| drupal8   |  |
| laravel   |  |

## Contributing

Read the [contributing](https://github.com/deployphp/ubccpsc/blob/master/CONTRIBUTING.md) guide, take a look on open [issues](https://github.com/ubccpsc/recipes/issues)

## License

Licensed under the [MIT license](https://github.com/ubccpsc/recipes/blob/master/LICENSE).
