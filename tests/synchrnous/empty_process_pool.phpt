--TEST--
Starting executor with empty process pool
--FILE--
<?php

\error_reporting(E_ALL ^ E_DEPRECATED);

use NorbertTech\SymfonyProcessExecutor\SynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;

require __DIR__ . '/../../vendor/autoload.php';

$processes = new ProcessPool();

$executor = new SynchronousExecutor($processes);

$executor->execute();

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(\trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    echo "----\n";
});

echo \sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo \sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo \sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSeconds());

--EXPECT--
Successfully finished child processes: 0
Failure finished child processes: 0
Total execution time [s]: 0