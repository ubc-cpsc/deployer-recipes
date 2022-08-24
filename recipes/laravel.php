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
before('deploy:symlink', 'deploy:artisan');

/**
 * Database and migrations.
 */
desc('Seeds the database with records');
task('artisan:db:seed', artisan('db:seed --force', ['showOutput']))->once();

desc('Runs the database migrations');
task('artisan:migrate', artisan('migrate --force', ['skipIfNoEnv']))->once();

desc('Drops all tables and re-run all migrations');
task('artisan:migrate:fresh', artisan('migrate:fresh --force'))->once();

desc('Rollbacks the last database migration');
task('artisan:migrate:rollback', artisan('migrate:rollback --force', ['showOutput']))->once();

desc('Shows the status of each migration');
task('artisan:migrate:status', artisan('migrate:status', ['showOutput']))->once();
