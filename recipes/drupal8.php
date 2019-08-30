<?php

namespace Deployer;

require_once __DIR__ . '/drupal.php';

// Shared directories and files.
add('shared_files', [
  '.env',
]);

add('rsync', [
  'exclude' => [
    '/drush/*',
    '/web/*',
  ],
  'include' => [
    'drush',
    'drush/Commands',
    'web',
    'web/core',
    'web/libraries',
    'web/modules/contrib',
    'web/profiles/contrib',
    'web/themes/contrib',
  ],
]);

