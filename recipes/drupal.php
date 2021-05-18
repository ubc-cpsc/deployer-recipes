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

/**
 * Helper tasks for drush.
 */
desc('Run database updates');
task('drush:updatedb', drush('updb -y', ['skipIfNoEnv', 'showOutput']))->once();

desc('Import latest config');
task('drush:config:import', drush('config:import -y', ['skipIfNoEnv', 'showOutput']))->once();

/**
 * Run drush commands.
 *
 * Supported options:
 * - 'skipIfNoEnv': Skip and warn the user if `.env` file is non existing or empty.
 * - 'failIfNoEnv': Fail the command if `.env` file is non existing or empty.
 * - 'runInCurrent': Run the drush command in the current directory.
 * - 'showOutput': Show the output of the command if given.
 *
 * @param string $command The drush command (with cli options if any).
 * @param array $options The options that define the behaviour of the command.
 * @return callable A function that can be used as a task.
 */
function drush($command, $options = [])
{
    return function() use ($command, $options) {
        if (in_array('failIfNoEnv', $options) && ! test('[ -s {{release_path}}/.env ]')) {
            throw new \Exception('Your .env file is empty! Cannot proceed.');
        }

        if (in_array('skipIfNoEnv', $options) && ! test('[ -s {{release_path}}/.env ]')) {
            writeln("<fg=yellow;options=bold;>Warning: </><fg=yellow;>Your .env file is empty! Skipping...</>");
            return;
        }

        $path = in_array('runInCurrent', $options)
            ? '{{deploy_path}}/current'
            : '{{release_path}}';

        cd($path);
        $output = run("drush $command");

        if (in_array('showOutput', $options)) {
            writeln("<info>$output</info>");
        }
    };
}
