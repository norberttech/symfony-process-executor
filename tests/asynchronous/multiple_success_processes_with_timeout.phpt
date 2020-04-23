--TEST--
Starting multiple processes with defined timeout
--FILE--
<?php

use NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use NorbertTech\SymfonyProcessExecutor\Time;
use Symfony\Component\Process\Process;

require __DIR__ . '/../../vendor/autoload.php';

$processes = new ProcessPool(
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 1') : new Process('sleep 1 && echo 1'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 2 && echo 2') : new Process('sleep 2 && echo 2'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 3 && echo 3') : new Process('sleep 3 && echo 3'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 4 && echo 4') : new Process('sleep 4 && echo 4'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 5 && echo 5') : new Process('sleep 5 && echo 5'),
);

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
int(0)
string(1) "1"
int(1)
----
int(0)
string(1) "2"
int(2)
----
int(0)
string(1) "3"
int(3)
----
int(143)
string(0) ""
int(3)
----
int(143)
string(0) ""
int(3)
----
Successfully finished child processes: 3
Failure finished child processes: 2
Total execution time [s]: 3