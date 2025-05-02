<?php

declare(strict_types=1);

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
        $this->processes = \array_values(\array_map(
            function (Process $process) : ProcessWrapper {
                return new ProcessWrapper($process);
            },
            $processes
        ));
    }

    /**
     * Returns processes that are not started yet.
     *
     * @return array<ProcessWrapper>
     */
    public function notStarted(int $max = null) : array
    {
        $processes = [];

        foreach ($this->processes as $nextProcess) {
            if ($nextProcess->started()) {
                continue;
            }

            $processes[] = $nextProcess;

            if ($max !== null) {
                if (\count($processes) >= $max) {
                    break;
                }
            }
        }

        return $processes;
    }

    /**
     * Returns count of processes that have been started but not finished yet.
     */
    public function unfinishedCount() : int
    {
        return \array_reduce(
            $this->processes,
            function (int $unfinishedCount, ProcessWrapper $nextProcess) : int {
                if ($nextProcess->started() && !$nextProcess->finished()) {
                    $unfinishedCount += 1;
                }

                return $unfinishedCount;
            },
            0
        );
    }

    public function succeeded() : int
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

    public function failed() : int
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

    public function notStartedCount() : int
    {
        return \array_reduce(
            $this->processes,
            function (int $notStartedCount, ProcessWrapper $nextProcess) : int {
                if (!$nextProcess->started()) {
                    $notStartedCount += 1;
                }

                return $notStartedCount;
            },
            0
        );
    }

    /**
     * Returns all processes in the pool.
     *
     * @return array<ProcessWrapper>
     */
    public function all() : array
    {
        return $this->processes;
    }

    /**
     * Returns processes that have been started but not finished yet.
     *
     * @return array<ProcessWrapper>
     */
    public function notFinished() : array
    {
        return \array_filter(
            $this->processes,
            function (ProcessWrapper $nextProcess) : bool {
                return $nextProcess->started() && !$nextProcess->finished();
            }
        );
    }
}
