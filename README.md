# Deployer Recipes

This repository contains UBC CS department recipes to integrate with deployer.

## Installing

~~~sh
composer require ubc-cpsc/deployer-recipes --dev
~~~

Include recipes in `deploy.php` file.

```php
require 'recipes/drupal8.php';
```

## Recipes

| Recipe     | Docs
| ------     | ----
| cachetool  | [read](docs/cachetool.md)
| rsync      | [read](docs/rsync.md)


## Contributing

Read the [contributing](https://github.com/deployphp/ubccpsc/blob/master/CONTRIBUTING.md) guide, take a look on open [issues](https://github.com/ubccpsc/recipes/issues)

## License

Licensed under the [MIT license](https://github.com/ubccpsc/recipes/blob/master/LICENSE).
