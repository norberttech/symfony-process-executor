--TEST--
Starting multiple processes without defined timeout
--FILE--
<?php

\error_reporting(E_ALL ^ E_DEPRECATED);

use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use NorbertTech\SymfonyProcessExecutor\SynchronousExecutor;
use Symfony\Component\Process\Process;
use Aeon\Calendar\TimeUnit;

require __DIR__ . '/../../vendor/autoload.php';

$processes = new ProcessPool(
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 1') : new Process('sleep 1 && echo 1'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 2') : new Process('sleep 1 && echo 2'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 3') : new Process('sleep 1 && echo 3'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 4') : new Process('sleep 1 && echo 4'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 5') : new Process('sleep 1 && echo 5'),
);

$executor = new SynchronousExecutor($processes);

$executor->execute(TimeUnit::milliseconds(10), TimeUnit::milliseconds(3000));

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode() != 0 ? "failed" : "succeed");
    var_dump(trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    echo "----\n";
});

echo sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSeconds());

--EXPECT--
string(7) "succeed"
string(1) "1"
int(1)
----
string(7) "succeed"
string(1) "2"
int(1)
----
string(7) "succeed"
string(1) "3"
int(1)
----
string(6) "failed"
string(0) ""
int(0)
----
string(6) "failed"
string(0) ""
int(0)
----
Successfully finished child processes: 3
Failure finished child processes: 2
Total execution time [s]: 3