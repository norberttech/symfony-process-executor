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

use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use NorbertTech\SymfonyProcessExecutor\SynchronousExecutor;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

$processes = new ProcessPool(
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 1 > NUL && echo 1') : new Process('ping 127.0.0.1 -n 1 > NUL && echo 1'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 2 > NUL && echo 2') : new Process('ping 127.0.0.1 -n 2 > NUL && echo 2'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 3 > NUL && echo 3') : new Process('ping 127.0.0.1 -n 3 > NUL && echo 3'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 4 > NUL && echo 4') : new Process('ping 127.0.0.1 -n 4 > NUL && echo 4'),
    method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('ping 127.0.0.1 -n 5 > NUL && echo 5') : new Process('ping 127.0.0.1 -n 5 > NUL && echo 5'),
);

$executor = new SynchronousExecutor($processes);

$executor->execute();

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    var_dump($processWrapper->executionTime()->inMilliseconds());
    var_dump($processWrapper->executionTime()->microsecond());
    echo "----\n";
});

echo sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSecondsPrecise());
