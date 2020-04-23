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

final class SynchronousExecutor
{
    /**
     * @var ProcessPool
     */
    private $pool;

    /**
     * @var null|float
     */
    private $startedAt = null;

    /**
     * @var null|float
     */
    private $finishedAt = null;

    public function __construct(ProcessPool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param Time|null $sleep - sleep time between checking out running processes
     * @param Time|null $timeout - timeout, after this time all processes are going to be killed
     * @throws Exception
     */
    public function execute(Time $sleep = null, Time $timeout = null) : void
    {
        if ($this->startedAt) {
            throw new Exception('SynchronousExecutor already started');
        }

        $sleep = $sleep ?: Time::fromMilliseconds(100);
        $total = Time::fromMicroseconds(0);

        $this->startedAt = microtime(true);

        $this->pool->each(function (ProcessWrapper $process) use ($sleep, &$total, $timeout) {
            $process->start();
            $process->check();

            if ($timeout) {
                if ($total->greaterThan($timeout)) {
                    $process->kill();
                }
            }

            while (!$process->finished()) {
                usleep($sleep->microseconds());

                $total = $total->add($sleep);

                if ($timeout) {
                    if ($total->greaterThan($timeout)) {
                        $process->kill();
                    }
                }

                $process->check();
            }
        });

        $this->finishedAt = microtime(true);
    }

    /**
     * @return ProcessPool
     */
    public function pool() : ProcessPool
    {
        return $this->pool;
    }

    /**
     * @return Time|null
     */
    public function executionTime() : ?Time
    {
        if ($this->startedAt !== null && $this->finishedAt !== null) {
            return Time::fromSecondMicrosecondsFloat($this->finishedAt - $this->startedAt);
        }

        return null;
    }
}
