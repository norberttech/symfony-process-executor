--TEST--
Starting executor with empty process pool
--FILE--
<?php

use NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use NorbertTech\SymfonyProcessExecutor\Time;

require __DIR__ . '/../../vendor/autoload.php';

$processes = new ProcessPool();

$executor = new AsynchronousExecutor($processes);

$executor->execute();

$executor->waitForAllToFinish(Time::fromMilliseconds(100), Time::fromMilliseconds(3000));

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(\trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->seconds());
    echo "----\n";
});

echo \sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo \sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo \sprintf("Total execution time [s]: %d\n", $executor->executionTime()->seconds());

--EXPECT--
Successfully finished child processes: 0
Failure finished child processes: 0
Total execution time [s]: 0