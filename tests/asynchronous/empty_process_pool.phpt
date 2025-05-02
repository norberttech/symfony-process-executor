--TEST--
Starting executor with empty process pool
--FILE--
<?php

\error_reporting(E_ALL ^ E_DEPRECATED);

use NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use Aeon\Calendar\TimeUnit;

require __DIR__ . '/../../vendor/autoload.php';

$processes = new ProcessPool();

$executor = new AsynchronousExecutor($processes, TimeUnit::milliseconds(100), TimeUnit::milliseconds(3000));

$executor->execute();

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(\trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    echo "----\n";
});

echo \sprintf("Successfully finished child processes: %d\n", $executor->pool()->succeeded());
echo \sprintf("Failure finished child processes: %d\n", $executor->pool()->failed());
echo \sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSeconds());

--EXPECT--
Successfully finished child processes: 0
Failure finished child processes: 0
Total execution time [s]: 0