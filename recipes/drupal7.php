<?php

namespace Deployer;

require_once __DIR__ . '/drupal.php';

add('rsync', [
  'filter' => [
    '+ /vendor/***',
  ],
]);