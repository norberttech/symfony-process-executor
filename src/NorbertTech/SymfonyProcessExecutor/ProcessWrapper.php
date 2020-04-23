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

use NorbertTech\SymfonyProcessExecutor\Exception\Exception;
use Symfony\Component\Process\Process;

final class ProcessWrapper
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var null|string
     */
    private $output = null;

    /**
     * @var null|int
     */
    private $exitCode = null;

    /**
     * @var null|int
     */
    private $pid = null;

    /**
     * @var null|float
     */
    private $startedAt = null;

    /**
     * @var null|float
     */
    private $finishedAt = null;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * @return bool
     */
    public function started() : bool
    {
        return $this->pid !== null;
    }

    public function finished() : bool
    {
        return $this->exitCode !== null;
    }

    /**
     * @return Process
     */
    public function process() : Process
    {
        return $this->process;
    }

    /**
     * @throws Exception
     */
    public function start() : void
    {
        if ($this->started()) {
            return ;
        }

        if ($this->finished()) {
            return ;
        }

        $this->startedAt = microtime(true);
        $this->process->start();
        $this->pid = $this->process->getPid();

        if ($this->pid === null) {
            throw new Exception(\sprintf('Can\'t get pid for process %s', $this->process->getCommandLine()));
        }
    }

    public function kill() : void
    {
        if (!$this->started()) {
            return ;
        }

        if ($this->finished()) {
            return ;
        }

        $this->finishedAt = microtime(true);
        $this->exitCode = $this->process->stop(0);
        $this->output = $this->process->getOutput();
    }

    public function check() : void
    {
        if (!$this->started()) {
            return ;
        }

        if ($this->finished()) {
            return ;
        }

        if ($this->process->isRunning()) {
            return ;
        }

        $this->finishedAt = microtime(true);
        $this->output = $this->process->getOutput();
        $this->exitCode = $this->process->getExitCode();
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

    /**
     * @return Time|null
     */
    public function executionTime() : ?Time
    {
        if (!$this->finished()) {
            return null;
        }

        return Time::fromSecondMicrosecondsFloat($this->finishedAt - $this->startedAt);
    }
}
