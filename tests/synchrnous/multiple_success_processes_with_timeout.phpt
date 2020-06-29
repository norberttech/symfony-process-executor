--TEST--
Starting multiple processes without defined timeout
--FILE--
<?php

use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use NorbertTech\SymfonyProcessExecutor\SynchronousExecutor;
use Symfony\Component\Process\Process;
use Aeon\Calendar\TimeUnit;

require __DIR__ . '/../../vendor/autoload.php';

if (\strncasecmp(PHP_OS, 'WIN', 3) == 0) {
    $processes = new ProcessPool(
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 1 > NUL && echo 1') : new Process('ping 127.0.0.1 -n 1 > NUL && echo 1'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 2 > NUL && echo 2') : new Process('ping 127.0.0.1 -n 2 > NUL && echo 2'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 3 > NUL && echo 3') : new Process('ping 127.0.0.1 -n 3 > NUL && echo 3'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 4 > NUL && echo 4') : new Process('ping 127.0.0.1 -n 4 > NUL && echo 4'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 5 > NUL && echo 5') : new Process('ping 127.0.0.1 -n 5 > NUL && echo 5'),
    );
} else {
    $processes = new ProcessPool(
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 1') : new Process('sleep 1 && echo 1'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 2') : new Process('sleep 1 && echo 2'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 3') : new Process('sleep 1 && echo 3'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 4') : new Process('sleep 1 && echo 4'),
        method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 5') : new Process('sleep 1 && echo 5'),
    );
}

$executor = new SynchronousExecutor($processes);

$executor->execute(TimeUnit::milliseconds(10), TimeUnit::milliseconds(3000));

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
int(143)
string(0) ""
int(0)
----
int(143)
string(0) ""
int(0)
----
Successfully finished child processes: 3
Failure finished child processes: 2
Total execution time [s]: 3