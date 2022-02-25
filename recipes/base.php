<?php

namespace Deployer;

require_once 'recipe/common.php';
require_once __DIR__ . '/cachetool.php';
require_once __DIR__ . '/rsync.php';

set('default_stage', 'staging');
set('keep_releases', 5);
set('deploy_path', '/var/www/{{application}}');

// Allocate tty for git clone. Default value is false.
set('git_tty', TRUE);
set('allow_anonymous_stats', FALSE);

// Set writable for w-html and w-run as part of www-content group.
set('writable_mode', 'chmod');
set('writable_chmod_mode', '2770');
set('writable_chmod_recursive', FALSE);

// Prepare vendor files to be synced.
set('rsync_src', realpath('.') . '/.build');
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

function whichLocally(string $name): string {
  $nameEscaped = escapeshellarg($name);

  // Try `command`, should cover all Bourne-like shells
  // Try `which`, should cover most other cases
  // Fallback to `type` command, if the rest fails
  $path = runLocally("command -v $nameEscaped || which $nameEscaped || type -p $nameEscaped");
  if (empty($path)) {
    throw new \RuntimeException("Can't locate [$nameEscaped] - neither of [command|which|type] commands are available");
  }

  // Deal with issue when `type -p` outputs something like `type -ap` in some implementations
  return trim(str_replace("$name is", "", $path));
}

// Build the vendor directory locally.
task('build', function () {
  $build_path = realpath('.') . '/.build';

  // Invoke deploy:update_code.
  $git = whichLocally('git');
  $target = get('target');

  runLocally("[ -d $build_path ] || mkdir -p $build_path");

  // Update all tracking branches.
  runLocally("$git remote update 2>&1");

  // Archive target branch/tag/revision to deploy_path.
  runLocally("$git archive $target | tar -x -f - -C $build_path 2>&1");

  // Invoke deploy:vendors.
  $composer = whichLocally('composer');
  if (!whichLocally('unzip')) {
    warning('To speed up composer installation setup "unzip" command with PHP zip extension.');
  }
  runLocally("cd $build_path && $composer {{composer_action}} {{composer_options}} 2>&1");
})->once();

// Remove the build directory after deploy.
task('build:cleanup', function () {
  $build_path = realpath('.') . '/.build';
  runLocally('rm -rf ' . $build_path);
})->once();

set('bin/ss', function () {
  return which('ss');
});

set('bin/grep', function () {
  return which('grep');
});

// Clear OPcache and realpath caches.
task('deploy:cachetool', function () {
  $fcgi = get('cachetool');
  $SERVER = '';
  $PORT = '';
  if (strpos($fcgi, ':') !== FALSE) {
    list($SERVER, $PORT) = explode(':', $fcgi);
  }

  // Check to see that we can connect to the PHP-FPM port we're trying to clear.
  if ($PORT && run("</dev/tcp/$SERVER/$PORT; if [ $? -eq 0 ]; then echo 'true'; fi") !== 'true') {
    writeln("<fg=yellow;options=bold;>Warning: </><fg=yellow;>Your server doesn't have PHP-FPM running on port $PORT. Skipping...</>");
    return;
  }
  // Check to see if we have a socket open that has 'php' in the name.
  elseif (!$PORT && run("{{bin/ss}} -xa | {{bin/grep}} php -q; if [ $? -eq 0 ]; then echo 'true'; fi") !== 'true') {
    writeln("<fg=yellow;options=bold;>Warning: </><fg=yellow;>Your server doesn't have PHP-FPM running on a socket. Skipping...</>");
    return;
  }

  invoke('cachetool:clear:stat');
  invoke('cachetool:clear:opcache');
});

set('branch', function () {
  $stage = get('labels')['stage'] ?? NULL;
  if ($stage == 'production') {
    $production_branches = [
      'latest',
      'master',
      'main',
    ];
    foreach ($production_branches as $target_branch) {
      $target_branch_exists = testLocally('[ -n "$(git rev-parse --verify --quiet ' . $target_branch . ')" ]');
      if ($target_branch_exists) {
        info("Setting Production branch: <fg=magenta;options=bold>$target_branch</>");
        return $target_branch;
      }
    }
    throw error('No production branches available: ' . implode(', ', $production_branches));
  }

  // Default.
  return 'HEAD';
});

task('deploy', [
  'build',
  'deploy:info',
  'deploy:setup',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  // Removed due to permission carry over.
  //'rsync:warmup',
  'rsync',
  'deploy:shared',
  // Removed, web server w-run user using chmod with ownership of w-html
  // yields permission issues, and visa versa.
  //'deploy:writable',
  'deploy:symlink',
  'deploy:cachetool',
  'deploy:unlock',
  'deploy:cleanup',
  'deploy:success',
]);

// Clean up the local build.
after('deploy:cleanup', 'build:cleanup');

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
