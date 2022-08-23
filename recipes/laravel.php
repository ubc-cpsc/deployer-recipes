<?php

namespace Deployer;

// Include artisan tasks.
import('recipe/laravel.php');

// Overwrite deploy task.
require_once __DIR__ . '/base.php';

add('recipes', ['laravel']);

// Override shared directories.
set('shared_dirs', [
  'bootstrap/cache',
  'storage',
]);
set('shared_files', ['.env']);

// Build the vendor directory locally.
desc('Run artisan commands');
task('deploy:artisan', function () {
  invoke('artisan:storage:link');
  invoke('artisan:optimize:clear');
  invoke('artisan:config:cache');
  invoke('artisan:migrate');
  invoke('artisan:route:cache');
  invoke('artisan:event:cache');
});

/**
 * Create storage directories.
 */
desc('Make initial storage directories.');
task('deploy:create_storage_dirs', function () {
  $writable_dirs = [
    'bootstrap/cache',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
  ];

  $sharedPath = "{{deploy_path}}/shared";
  foreach ($writable_dirs as $dir) {
    // Check if shared dir does not exist.
    if (!test("[ -d $sharedPath/$dir ]")) {
      // Create shared dir if it does not exist.
      run("umask 0002; mkdir -p $sharedPath/$dir");
    }
  }

})->once();
// Before deploy:shared since deploy:symlink is a local task call too and we
// only want this on remote servers.
after('deploy:shared', 'deploy:create_storage_dirs');

// Additional deploy steps for Laravel.
after('deploy:create_storage_dirs', 'deploy:artisan');

/**
 * Helper tasks overrides from laravel-deployer.
 */
desc('Disable maintenance mode');
task('artisan:up', artisan('up', ['runInCurrent', 'showOutput']));

desc('Enable maintenance mode');
task('artisan:down', artisan('down', ['runInCurrent', 'showOutput']));

desc('Execute artisan migrate');
task('artisan:migrate', artisan('migrate --force', ['skipIfNoEnv']))->once();

desc('Execute artisan migrate:fresh');
task('artisan:migrate:fresh', artisan('migrate:fresh --force'));

desc('Execute artisan migrate:rollback');
task('artisan:migrate:rollback', artisan('migrate:rollback --force', ['showOutput']));

desc('Execute artisan migrate:status');
task('artisan:migrate:status', artisan('migrate:status', ['showOutput']));

desc('Execute artisan db:seed');
task('artisan:db:seed', artisan('db:seed --force', ['showOutput']));

desc('Execute artisan cache:clear');
task('artisan:cache:clear', artisan('cache:clear'));

desc('Execute artisan config:clear');
task('artisan:config:clear', artisan('config:clear'));

desc('Execute artisan config:cache');
task('artisan:config:cache', artisan('config:cache'));

desc('Execute artisan route:cache');
task('artisan:route:cache', artisan('route:cache'));

desc('Execute artisan view:clear');
task('artisan:view:clear', artisan('view:clear'));

desc('Execute artisan view:cache');
task('artisan:view:cache', artisan('view:cache', ['min' => 5.6]));

desc('Execute artisan optimize');
task('artisan:optimize', artisan('optimize', ['min' => 5.7]));

desc('Execute artisan optimize:clear');
task('artisan:optimize:clear', artisan('optimize:clear', ['min' => 5.7]));

desc('Execute artisan queue:restart');
task('artisan:queue:restart', artisan('queue:restart'));

desc('Execute artisan storage:link');
task('artisan:storage:link', artisan('storage:link', ['min' => 5.3]));

desc('Execute artisan horizon:assets');
task('artisan:horizon:assets', artisan('horizon:assets'));

desc('Execute artisan horizon:terminate');
task('artisan:horizon:terminate', artisan('horizon:terminate'));

desc('Execute artisan telescope:clear');
task('artisan:telescope:clear', artisan('telescope:clear'));

desc('Execute artisan telescope:prune');
task('artisan:telescope:prune', artisan('telescope:prune'));

desc('Execute artisan telescope:publish');
task('artisan:telescope:publish', artisan('telescope:publish'));

desc('Execute artisan nova:publish');
task('artisan:nova:publish', artisan('nova:publish'));

desc('Execute artisan event:clear');
task('artisan:event:clear', artisan('event:clear', ['min' => '5.8.9']));

desc('Execute artisan event:cache');
task('artisan:event:cache', artisan('event:cache', ['min' => '5.8.9']));
