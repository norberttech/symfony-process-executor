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

final class SynchronousExecutor
{
    private ProcessPool $pool;

    private Stopwatch $stopwatch;

    public function __construct(ProcessPool $pool)
    {
        $this->pool = $pool;
        $this->stopwatch = new Stopwatch();
    }

    /**
     * @param null|TimeUnit $sleep - sleep time between checking out running processes
     * @param null|TimeUnit $timeout - timeout, after this time all processes are going to be killed
     *
     * @throws Exception
     */
    public function execute(TimeUnit $sleep = null, TimeUnit $timeout = null) : void
    {
        if ($this->stopwatch->isStarted()) {
            throw new Exception('SynchronousExecutor already started');
        }

        $sleep = $sleep ?: TimeUnit::milliseconds(100);
        $total = TimeUnit::seconds(0);
        $this->stopwatch->start();

        $this->pool->each(function (ProcessWrapper $process) use ($sleep, &$total, $timeout) : void {
            /** @var TimeUnit $total */
            $process->start();
            $process->check();

            if ($timeout) {
                if ($total->isGreaterThan($timeout)) {
                    $process->kill();
                }
            }

            while (!$process->finished()) {
                \Aeon\Sleep\sleep($sleep);

                $total = $total->add($sleep);

                if ($timeout) {
                    if ($total->isGreaterThan($timeout)) {
                        $process->kill();
                    }
                }

                $process->check();
            }
        });

        $this->stopwatch->stop();
    }

    public function pool() : ProcessPool
    {
        return $this->pool;
    }

    public function executionTime() : TimeUnit
    {
        return $this->stopwatch->totalElapsedTime();
    }
}
