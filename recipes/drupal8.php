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

desc('Execute drush update & config import');
task('deploy:drush', function () {
    // https://www.drush.org/latest/deploycommand/
    invoke('drush:deploy');
})->once();

desc('Execute drush config:export to backup previous config');
task('drush:config:backup', function() {
    $destination = has('previous_release') ? '{{previous_release}}' : '{{release_path}}';
    // Execute all within the previous release or current release directory.
    within($destination, function () use ($destination) {
        validateDrushEnvironmentFile();

        // Skip when there are no tables in the database.
        if (run("./vendor/bin/drush sql:query 'SHOW TABLES'") === '') {
            writeln("<fg=yellow;options=bold;>Warning: </><fg=yellow;>Your database is empty! Skipping...</>");
            return;
        }
        run("mkdir -p ./config/backup");
        run("./vendor/bin/drush -y config:export --destination=$destination/config/backup");
        writeln("Backup saved to $destination/config/backup");
    });
});
before('deploy:drush', 'drush:config:backup');
