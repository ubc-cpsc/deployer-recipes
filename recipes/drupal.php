<?php

namespace Deployer;

require_once __DIR__ . '/base.php';

// Shared directories and files.
set('shared_dirs', [
  'private',
  'public/sites/default/files',
]);
set('shared_files', [
  'public/sites/default/settings.local.php',
]);

// Duplicate our example.settings.local.php.
task('drupal:settings', function () {
  $sharedPath = "{{deploy_path}}/shared";
  $file = 'public/sites/default/settings.local.php';
  $default_file = 'public/sites/default/example.settings.local.php';

  $directory_name = dirname(parse($file));
  // Create dir of shared file.
  run("mkdir -p $sharedPath/" . $directory_name);
  if (!test("[ -f $sharedPath/$file ]") && test("[ -f {{release_path}}/$default_file ]")) {
    // Copy default local settings file in shared dir if not present.
    run("cp -rv {{release_path}}/$default_file $sharedPath/$file");
  }
});
