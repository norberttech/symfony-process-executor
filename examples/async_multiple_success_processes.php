<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony Process Executor library.
 *
 * (c) Norbert Orzechowicz <contact@norbert.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

$processes = new ProcessPool(
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 1 && echo 1') : new Process('sleep 1 && echo 1'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 2 && echo 2') : new Process('sleep 2 && echo 2'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 3 && echo 3') : new Process('sleep 3 && echo 3'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 4 && echo 4') : new Process('sleep 4 && echo 4'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('sleep 5 && echo 5') : new Process('sleep 5 && echo 5'),
);

$executor = new AsynchronousExecutor($processes);

$executor->execute();

$executor->waitForAllToFinish();

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(\trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    var_dump($processWrapper->executionTime()->inMilliseconds());
    var_dump($processWrapper->executionTime()->microsecond());
    echo "----\n";
});

echo \sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo \sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo \sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSecondsPrecise());
