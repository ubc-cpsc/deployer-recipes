<?php

namespace Deployer;

require_once __DIR__ . '/base.php';

add('recipes', ['drupal']);

// Require a .env file only for sites that depend on it.
set('drush_require_env', true);

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
task('drush:updatedb', drush('updb -y', ['showOutput']))->once();

desc('Import latest config');
task('drush:config:import', drush('config:import', ['showOutput']))->once();

desc('Deploy latest db updates and config');
task('drush:deploy', drush('deploy', ['showOutput', 'askInstallIfEmptyDb']))->once();

desc('Install Drupal using existing config');
task('drush:site:install', drush('site:install --existing-config', ['showOutput']))->once();

/**
 * Run drush commands.
 *
 * Supported options:
 *  - 'runInCurrent': Run the drush command in the current directory.
 *  - 'askInstallIfEmptyDb': Run drush:site:install if the database is empty.
 *  - 'showOutput': Show the output of the command if given.
 *
 * @param string $command The drush command (with cli options if any).
 * @param array $options The options that define the behaviour of the command.
 *
 * @return callable A function that can be used as a task.
 */
function drush($command, $options = [])
{
    return function() use ($command, $options) {
        // Set the working path for where the vendor directory we want to run drush from.
        $path = in_array('runInCurrent', $options)
            ? '{{current_path}}'
            : '{{release_path}}';
        cd($path);
        validateDrushEnvironmentFile();

        if (! test("[ -s ./vendor/bin/drush ]")) {
            throw new \Exception('Your drush is missing from vendor/bin! Cannot proceed.');
        }

        // Check if the database is empty and if so, ask to install from existing config.
        if (in_array('askInstallIfEmptyDb', $options) && run("./vendor/bin/drush sql:query 'SHOW TABLES'") === '') {
            if (askConfirmation('You have an empty database, would you like to install drupal with existing config?')) {
                invoke('drush:site:install');
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

/**
 * Check for a usable .env file in the directory where Drush will run.
 */
function validateDrushEnvironmentFile(): void
{
    if (get('drush_require_env') && !test('[ -f ./.env ] && [ -s ./.env ] && [ -r ./.env ]')) {
        $path = get('working_path');
        throw new \RuntimeException("Required .env file at $path/.env is missing, empty, or unreadable.");
    }
}
