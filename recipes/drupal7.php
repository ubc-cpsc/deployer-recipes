<?php

namespace Deployer;

require_once __DIR__ . '/drupal.php';

add('recipes', ['drupal7']);

add('rsync', [
  'filter' => [
    '+ /vendor/***',
  ],
]);
