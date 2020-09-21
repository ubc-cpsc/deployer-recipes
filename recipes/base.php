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

// Prepare vendor files to be synced.
set('rsync_src', realpath('.') . '/.build/current');
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
  'flags' => 'rclE',
  'options' => ['delete', 'delete-after', 'force'],
  'timeout' => 300,
]);

// Build the vendor directory locally.
task('build', function () {
  $stage = input()->getArgument('stage');
  if ($stage == 'production') {
    set('branch', 'master');
  }

  set('deploy_path', realpath('.') . '/.build');
  invoke('deploy:prepare');
  invoke('deploy:release');
  invoke('deploy:update_code');
  invoke('deploy:vendors');
  invoke('deploy:symlink');
})->local();

// Remove the build directory after deploy.
task('build:cleanup', function () {
  set('deploy_path', realpath('.') . '/.build');
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
  'deploy:symlink',
  'deploy:unlock',
  'cleanup',
  'build:cleanup',
]);

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');