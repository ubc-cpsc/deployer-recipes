<?php

namespace Deployer;

// Include artisan tasks.
require_once 'recipe/laravel.php';

// Overwrite deploy task.
require_once __DIR__ . '/base.php';

// Build the vendor directory locally.
task('deploy:artisan', function () {
  invoke('artisan:storage:link');
  invoke('artisan:optimize:clear');
  invoke('artisan:config:cache');
  invoke('artisan:migrate');
  invoke('artisan:route:cache');
});


// Additional deploy steps for Laravel.
before('deploy:symlink', 'deploy:artisan');
