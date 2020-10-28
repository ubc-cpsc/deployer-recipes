<?php

namespace Deployer;

require_once 'recipe/common.php';
require_once __DIR__ . '/cachetool.php';
require_once 'recipe/rsync.php';

set('default_stage', 'staging');
set('keep_releases', 10);
set('deploy_path', '/var/www/{{application}}');

// Allocate tty for git clone. Default value is false.
set('git_tty', TRUE);
set('allow_anonymous_stats', FALSE);

// Set writable for w-html and w-run as part of www-content group.
set('writable_mode', 'chmod');
set('writable_chmod_mode', '2770');
set('writable_chmod_recursive', FALSE);

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

  // As long as the branch isn't explicitly passed in the command line,
  // use master for production stage.
  $branch = input()->getOption('branch');
  if ($stage == 'production' && !$branch) {
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

// Clear OPcache and realpath caches.
task('deploy:cachetool', function () {
  set('cachetool', '127.0.0.1:9074');
  invoke('cachetool:clear:stat');
  invoke('cachetool:clear:opcache');
});


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
  'deploy:writable',
  'deploy:symlink',
  'deploy:cachetool',
  'deploy:unlock',
  'cleanup',
  'build:cleanup',
]);

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
