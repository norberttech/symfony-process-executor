--TEST--
Starting processor twice
--FILE--
<?php

use NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
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
$executor->execute();

--EXPECTF--
Fatal error: Uncaught NorbertTech\SymfonyProcessExecutor\Exception\Exception: AsynchronousExecutor already started in %s
Stack trace:
#0 Standard input code(%d): NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor->execute()
#1 {main}
  thrown in %s on line %d
