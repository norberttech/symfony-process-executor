--TEST--
Starting multiple processes without defined timeout
--FILE--
<?php

\error_reporting(E_ALL ^ E_DEPRECATED);

use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use NorbertTech\SymfonyProcessExecutor\SynchronousExecutor;
use Symfony\Component\Process\Process;

require __DIR__ . '/../../vendor/autoload.php';

$processes = new ProcessPool(
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 1') : new Process('sleep 1 && echo 1'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 2') : new Process('sleep 1 && echo 2'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 3') : new Process('sleep 1 && echo 3'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 4') : new Process('sleep 1 && echo 4'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 5') : new Process('sleep 1 && echo 5'),
);

$executor = new SynchronousExecutor($processes);

$executor->execute();

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    echo "----\n";
});

echo sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSeconds());

--EXPECT--
int(0)
string(1) "1"
int(1)
----
int(0)
string(1) "2"
int(1)
----
int(0)
string(1) "3"
int(1)
----
int(0)
string(1) "4"
int(1)
----
int(0)
string(1) "5"
int(1)
----
Successfully finished child processes: 5
Failure finished child processes: 0
Total execution time [s]: 5