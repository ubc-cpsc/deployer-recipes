<?php

namespace Deployer;

require_once 'recipe/common.php';
require_once 'recipe/rsync.php';

set('default_stage', 'staging');
set('keep_releases', 10);
set('deploy_path', '/var/www/{{application}}');

// Allocate tty for git clone. Default value is false.
set('git_tty', TRUE);
set('allow_anonymous_stats', FALSE);

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

// Prepare vendor files to be synced.
set('rsync_src', __DIR__ . '/.build/current');
set('rsync', [
  'exclude' => [
    '.git',
  ],
  'include' => [],
  'exclude-file' => FALSE,
  'include-file' => FALSE,
  'filter' => [],
  'filter-file' => FALSE,
  'filter-perdir' => FALSE,
  'flags' => 'rzclE',
  'options' => ['delete', 'delete-after', 'force'],
  'timeout' => 300,
]);

// Build the vendor directory locally.
task('build', function () {
  $stage = input()->getArgument('stage');
  if ($stage == 'production') {
    set('branch', 'master');
  }

  set('deploy_path', __DIR__ . '/.build');
  invoke('deploy:prepare');
  invoke('deploy:release');
  invoke('deploy:update_code');
  invoke('deploy:vendors');
  invoke('deploy:symlink');
})->local();

// Remove the build directory after deploy.
task('build:cleanup', function () {
  set('deploy_path', __DIR__ . '/.build');
  $sudo = get('cleanup_use_sudo') ? 'sudo' : '';
  $runOpts = [];
  if ($sudo) {
    $runOpts['tty'] = get('cleanup_tty', FALSE);
  }
  run("$sudo rm -rf {{deploy_path}}", $runOpts);
})->local();

task('deploy', [
  'build',
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  'rsync:warmup',
  'rsync',
  'deploy:shared',
  'drupal:settings',
  'deploy:symlink',
  'deploy:unlock',
  'cleanup',
  'build:cleanup',
]);

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
