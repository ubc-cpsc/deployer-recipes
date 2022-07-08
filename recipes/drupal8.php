<?php

namespace Deployer;

require_once __DIR__ . '/drupal.php';

// Using filter because the order of the exculsions matters.
// This is the option order:
//   {{rsync_options}}{{rsync_excludes}}{{rsync_includes}}{{rsync_filter}}
add('rsync', [
  'filter' => [
    '+ /drush/',
    '+ /drush/Commands/***',
    '+ /public/',
    '+ /public/core/***',
    '+ /public/libraries/***',
    '+ /public/modules/',
    '+ /public/modules/contrib/***',
    '+ /public/profiles/',
    '+ /public/profiles/contrib/***',
    '+ /public/themes/',
    '+ /public/themes/contrib/***',
    '+ /vendor/***',
    '- *',
  ],
]);

desc('Execute database update & config import');
task('deploy:drush', function () {
  // https://www.drush.org/latest/deploycommand/
  invoke('drush:deploy');
})->once();
