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

use Aeon\Calendar\Stopwatch;
use Aeon\Calendar\TimeUnit;
use NorbertTech\SymfonyProcessExecutor\Exception\Exception;
use Symfony\Component\Process\Process;

final class ProcessWrapper
{
    private Process $process;

    private ?string $output = null;

    private ?int $exitCode = null;

    private ?int $pid = null;

    private Stopwatch $stopwatch;

    public function __construct(Process $process)
    {
        $this->process = $process;
        $this->stopwatch = new Stopwatch();
    }

    public function started() : bool
    {
        return $this->pid !== null;
    }

    public function finished() : bool
    {
        return $this->exitCode !== null;
    }

    public function process() : Process
    {
        return $this->process;
    }

    public function start() : void
    {
        if ($this->started()) {
            return;
        }

        if ($this->finished()) {
            return;
        }

        $this->stopwatch->start();
        $this->process->start();
        $this->pid = $this->process->getPid();

        if ($this->pid === null) {
            throw new Exception(\sprintf('Can\'t get pid for process %s', $this->process->getCommandLine()));
        }
    }

    public function kill() : void
    {
        if (!$this->started()) {
            return;
        }

        if ($this->finished()) {
            return;
        }

        $this->exitCode = $this->process->stop(0);
        $this->output = $this->process->getOutput();
        $this->stopwatch->stop();
    }

    public function check() : void
    {
        if (!$this->started()) {
            return;
        }

        if ($this->finished()) {
            return;
        }

        if ($this->process->isRunning()) {
            return;
        }

        $this->output = $this->process->getOutput();
        $this->exitCode = $this->process->getExitCode();
        $this->stopwatch->stop();
    }

    public function output() : ?string
    {
        return $this->output;
    }

    public function exitCode() : ?int
    {
        return $this->exitCode;
    }

    public function pid() : ?int
    {
        return $this->pid;
    }

    public function executionTime() : TimeUnit
    {
        return $this->stopwatch->totalElapsedTime();
    }
}
