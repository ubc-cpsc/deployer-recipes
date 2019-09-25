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
    '+ /web/',
    '+ /web/core/***',
    '+ /web/libraries/***',
    '+ /web/modules/',
    '+ /web/modules/contrib/***',
    '+ /web/profiles/',
    '+ /web/profiles/contrib/***',
    '+ /web/themes/',
    '+ /web/themes/contrib/***',
    '+ /vendor/***',
    '- *',
  ],
]);
