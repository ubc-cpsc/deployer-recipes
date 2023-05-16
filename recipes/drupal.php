<?php

namespace Deployer;

require_once __DIR__ . '/base.php';

add('recipes', ['drupal']);

// Shared directories and files.
set('shared_dirs', [
  'private',
  'public/sites/default/files',
]);
set('shared_files', [
  '.env',
  'public/sites/default/settings.local.php',
]);

desc('Execute database updates');
task('deploy:drush', function () {
  // Run updb before config import to catch up schema.
  invoke('drush:updatedb');
})->once();

// Additional database update and config import steps for Drupal.
before('deploy:symlink', 'deploy:drush');

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
task('drush:config:import', drush('config:import', ['skipIfNoEnv', 'showOutput']))->once();

desc('Deploy latest db updates and config');
task('drush:deploy', drush('deploy', ['skipIfNoEnv', 'showOutput', 'askInstallIfEmptyDb']))->once();

desc('Install Drupal using existing config');
task('drush2:site:install', drush('site:install --existing-config', ['skipIfNoEnv', 'showOutput']))->once();

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
        if (in_array('failIfNoEnv', $options) && ! test('[ -s {{release_or_current_path}}/.env ]')) {
            throw new \Exception('Your .env file is empty! Cannot proceed.');
        }

        if (in_array('skipIfNoEnv', $options) && ! test('[ -s {{release_or_current_path}}/.env ]')) {
            writeln("<fg=yellow;options=bold;>Warning: </><fg=yellow;>Your .env file is empty! Skipping...</>");
            return;
        }

        // Set the working path for where the vendor directory we want to run drush from.
        $path = in_array('runInCurrent', $options)
            ? '{{current_path}}'
            : '{{release_path}}';
        cd($path);

        if (! test("[ -s ./vendor/bin/drush ]")) {
            throw new \Exception('Your drush is missing from vendor/bin! Cannot proceed.');
        }

        // Check if the database is empty and if so, ask to install from existing config.
        if (in_array('askInstallIfEmptyDb', $options) && test('[[ -z "$(./vendor/bin/drush sql:query \'SHOW TABLES\')" ]]')) {
            if (askConfirmation('You have an empty database, would you like to install drupal with existing config?')) {
                invoke('drush2:site:install');
                return;
            }
        }

        // Run command.
        $output = run("./vendor/bin/drush -y $command");

        if (in_array('showOutput', $options)) {
            writeln("<info>$output</info>");
        }
    };
}
