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

namespace NorbertTech\SymfonyProcessExecutor;

use Symfony\Component\Process\Process;

final class ProcessPool
{
    /**
     * @var array<int, ProcessWrapper>
     */
    private array $processes;

    public function __construct(Process ...$processes)
    {
        $this->processes = \array_map(
            function (Process $process) : ProcessWrapper {
                return new ProcessWrapper($process);
            },
            $processes
        );
    }

    public function unfinished() : int
    {
        return \array_reduce(
            $this->processes,
            function (int $unfinishedCount, ProcessWrapper $nextProcess) : int {
                $nextProcess->check();

                if ($nextProcess->finished()) {
                    $unfinishedCount -= 1;
                }

                return $unfinishedCount;
            },
            \count($this->processes)
        );
    }

    public function withSuccessExitCode() : int
    {
        return \array_reduce(
            $this->processes,
            function (int $withExitCode, ProcessWrapper $nextProcess) : int {
                if ($nextProcess->finished()) {
                    if ($nextProcess->exitCode() === 0) {
                        $withExitCode += 1;
                    }
                }

                return $withExitCode;
            },
            0
        );
    }

    public function withFailureExitCode() : int
    {
        return \array_reduce(
            $this->processes,
            function (int $withExitCode, ProcessWrapper $nextProcess) : int {
                if ($nextProcess->finished()) {
                    if ($nextProcess->exitCode() !== 0) {
                        $withExitCode += 1;
                    }
                }

                return $withExitCode;
            },
            0
        );
    }

    public function each(callable $callback) : void
    {
        \array_map($callback, $this->processes);
    }
}
