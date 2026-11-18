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

| Recipe    | Docs                      |
|-----------|---------------------------|
| base      |  |
| cachetool | [read](docs/cachetool.md) |
| drupal    | |
| drupal7   |  |
| drupal8   |  |
| laravel   |  |

### Drupal `.env` validation

The Drupal recipes default `drush_require_env` to `true`. Before running Drush,
the task requires a readable, nonempty `.env` in the directory where Drush will
run. A missing or unusable file stops the task and reports the path.

Set `drush_require_env` to `false` if your site runs Drush and supplies its
database credentials without a `.env` file, for example through Drupal's local
settings. Add this after loading the recipe in `deploy.php`.

```php
set('drush_require_env', false);
```

The setting bypasses only the `.env` check. Drush commands still run, and a
failed database probe still stops the task. Deploy workflows that omit the
Drush tasks need no override.

## Contributing

Read the [contributing](https://github.com/deployphp/ubccpsc/blob/master/CONTRIBUTING.md) guide, take a look on open [issues](https://github.com/ubccpsc/recipes/issues)

## License

Licensed under the [MIT license](https://github.com/ubccpsc/recipes/blob/master/LICENSE).
