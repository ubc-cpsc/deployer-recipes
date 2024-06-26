<?php

namespace Deployer;

require_once __DIR__ . '/drupal.php';

add('recipes', ['drupal8']);

add('rsync_untracked_paths', [
    'drush/Commands',
    'public/core',
    'public/libraries',
    'public/modules/contrib',
    'public/profiles/contrib',
    'public/themes/contrib',
]);

desc('Execute database update & config import');
task('deploy:drush', function () {
  // https://www.drush.org/latest/deploycommand/
  invoke('drush:deploy');
})->once();

task('drush:config:export', function() {
  $releasesList = get('releases_list');
  $prev_release = max($releasesList);
  $destination = '{{deploy_path}}/releases/' . $prev_release . '/config/backup';
  run("mkdir $destination");
  drush("config:export --destination=$destination", ['runInCurrent', 'showOutput']);
})->once();
