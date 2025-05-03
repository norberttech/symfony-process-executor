<?php

declare(strict_types=1);

namespace NorbertTech\SymfonyProcessExecutor;

use Aeon\Calendar\Stopwatch;
use Aeon\Calendar\TimeUnit;
use NorbertTech\SymfonyProcessExecutor\Exception\Exception;

final class SynchronousExecutor implements Executor
{
    /**
     * @param ProcessPool $pool - pool of processes to execute
     * @param ?TimeUnit $sleep - sleep time between checking out running processes
     * @param ?TimeUnit $timeout - timeout, after this time all processes are going to be killed
     */
    public function __construct(private readonly ProcessPool $pool, private readonly ?TimeUnit $sleep = null, private readonly ?TimeUnit $timeout = null, private readonly Stopwatch $stopwatch = new Stopwatch())
    {
        if ($this->stopwatch->isStarted()) {
            throw new Exception('SynchronousExecutor already started');
        }
    }

    /**
     * @throws Exception
     */
    public function execute() : void
    {
        if ($this->stopwatch->isStarted()) {
            throw new Exception('SynchronousExecutor already started');
        }

        $this->stopwatch->start();
        $sleep = $this->sleep ?: TimeUnit::milliseconds(100);
        $total = TimeUnit::seconds(0);

        $this->pool->each(function (ProcessWrapper $process) use ($sleep, &$total) : void {
            /** @var TimeUnit $total */
            $process->start();
            $process->check();

            if ($this->timeout) {
                if ($total->isGreaterThan($this->timeout)) {
                    $process->kill();
                }
            }

            while (!$process->finished()) {
                \Aeon\Sleep\sleep($sleep);

                $total = $total->add($sleep);

                if ($this->timeout) {
                    if ($total->isGreaterThan($this->timeout)) {
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
