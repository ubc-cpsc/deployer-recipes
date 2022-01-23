<?php

namespace Deployer;

add('recipes', ['cachetool']);

set('cachetool', '');
set('cachetool_args', '');
set('bin/cachetool', function () {
  return which('cachetool');
});

desc('Clears the file status cache, including the realpath cache');
task('cachetool:clear:stat', function () {
    $options = get('cachetool');
    $fullOptions = get('cachetool_args');

    if (strlen($fullOptions) > 0) {
        $options = "{$fullOptions}";
    } elseif (strlen($options) > 0) {
        $options = "--fcgi={$options}";
    }

    run("{{bin/cachetool}} stat:clear {$options}");
});

desc('Clearing OPCode cache');
task('cachetool:clear:opcache', function () {
    $options = get('cachetool');
    $fullOptions = get('cachetool_args');

    if (strlen($fullOptions) > 0) {
        $options = "{$fullOptions}";
    } elseif (strlen($options) > 0) {
        $options = "--fcgi={$options}";
    }

    run("{{bin/cachetool}} opcache:reset {$options}");
});

desc('Clearing APCu system cache');
task('cachetool:clear:apcu', function () {
    $options = get('cachetool');
    $fullOptions = get('cachetool_args');

    if (strlen($fullOptions) > 0) {
        $options = "{$fullOptions}";
    } elseif (strlen($options) > 0) {
        $options = "--fcgi={$options}";
    }

    run("{{bin/cachetool}} apcu:cache:clear {$options}");
});
