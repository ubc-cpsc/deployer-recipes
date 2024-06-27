<?php

namespace Deployer;

// Include artisan tasks.
import('recipe/laravel.php');

// Overwrite deploy task.
require_once __DIR__ . '/base.php';

add('recipes', ['laravel']);

set('writable_dirs', [
  'bootstrap/cache',
]);

// Override shared directories.
set('shared_dirs', ['storage']);
set('shared_files', ['.env']);

// Build the vendor directory locally.
desc('Run artisan commands');
task('deploy:artisan', function () {
  // Create the symbolic links configured for the application.
  invoke('artisan:storage:link');

  // Remove all bootstrap/cache files.
  invoke('artisan:optimize:clear');

  // Don't build bootstrap/cache as per https://stackoverflow.com/questions/29729543/how-to-stop-laravel-5-from-caching-configurations/29729639#29729639
  // invoke('artisan:config:cache');

  // Create a route cache file for faster route registration.
  // Disabled because when built by artisan it creates "405 Method Not Allowed"
  // errors.
  // invoke('artisan:route:cache');

  // Discover and cache the application's events and listeners.
  invoke('artisan:event:cache');

  // Compile all the application's Blade templates.
  invoke('artisan:view:cache');
});

// Additional deploy steps for Laravel.
before('deploy:symlink', 'deploy:artisan');
// Run the database migrations, once only after caches cleared.
after('deploy:artisan', 'artisan:migrate');

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
