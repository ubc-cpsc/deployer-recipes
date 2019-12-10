<?php

namespace Deployer;

require_once __DIR__ . '/drupal.php';

// Shared directories and files.
add('shared_files', [
  '.env',
]);

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

desc('Execute config import');
task('deploy:config_import', function () {
  cd('{{release_path}}/public');
  // Run updb before config import to catch up schema.
  run('drush -y updb');
  run('drush -y csim');
})->once();
