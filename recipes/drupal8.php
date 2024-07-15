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

desc('Backup production config');
task('drush:config:backup', function() {
  if (has('previous_release')) {
    $destination = '{{previous_release}}/config/backup';
  }
  else {
    $destination = '{{release_path}}/config/backup';
  }
  run("mkdir -p $destination");
  cd('{{release_or_current_path}}');
  run("./vendor/bin/drush -y config:export --destination=$destination");
  writeln('Backup saved to ' . $destination);
});
before('deploy:drush', 'drush:config:backup');
