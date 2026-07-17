<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$phpFiles = [
    $projectRoot . '/autoload.php',
    ...glob($projectRoot . '/recipes/*.php'),
    ...glob(__DIR__ . '/fixtures/*.php'),
];

foreach ($phpFiles as $phpFile) {
    runCommand([PHP_BINARY, '-l', $phpFile], $projectRoot);
}

$deployer = $projectRoot . '/vendor/bin/dep';
$recipes = [
    'base' => 'deploy',
    'cachetool' => 'cachetool:clear:stat',
    'drupal' => 'deploy',
    'drupal7' => 'deploy',
    'drupal8' => 'deploy',
    'laravel' => 'deploy',
];

foreach ($recipes as $recipe => $task) {
    runCommand(
        [PHP_BINARY, $deployer, '--file=' . __DIR__ . "/fixtures/$recipe.php", 'tree', $task],
        $projectRoot,
    );
}

require $projectRoot . '/vendor/autoload.php';

$application = new Symfony\Component\Console\Application('Deployer recipe smoke tests');
$deployerApplication = new Deployer\Deployer($application);
$deployerApplication->input = new Symfony\Component\Console\Input\ArrayInput([]);
$deployerApplication->output = new Symfony\Component\Console\Output\BufferedOutput();
set_include_path($projectRoot . '/vendor/deployer/deployer' . PATH_SEPARATOR . get_include_path());

require $projectRoot . '/recipes/base.php';

$context = new Deployer\Task\Context(new Deployer\Host\Localhost('smoke'));
Deployer\Task\Context::push($context);

try {
    $phpPath = Deployer\whichLocally('php');
    if ($phpPath === '') {
        throw new RuntimeException('whichLocally() did not locate a known executable.');
    }

    try {
        Deployer\whichLocally('php; printf injected');
    } catch (Deployer\Exception\RunException) {
        $unsafeNameWasQuoted = true;
    }

    if (!isset($unsafeNameWasQuoted)) {
        throw new RuntimeException('Unsafe executable name was interpreted by the shell.');
    }
} finally {
    Deployer\Task\Context::pop();
}

fwrite(STDOUT, "All Deployer recipe smoke tests passed.\n");

/**
 * @param list<string> $command
 */
function runCommand(array $command, string $workingDirectory): void
{
    $process = proc_open(
        $command,
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        $workingDirectory,
    );

    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start: ' . implode(' ', $command));
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        fwrite(STDERR, $stdout . $stderr);
        throw new RuntimeException(
            sprintf('Command failed with exit code %d: %s', $exitCode, implode(' ', $command)),
        );
    }
}
