<?php

declare(strict_types=1);

namespace NorbertTech\SymfonyProcessExecutor;

use Aeon\Calendar\Stopwatch;
use Aeon\Calendar\TimeUnit;
use NorbertTech\SymfonyProcessExecutor\Exception\Exception;

final class SynchronousExecutor implements Executor
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

    public function waitForAllToFinish(TimeUnit $sleep = null, TimeUnit $timeout = null) : void
    {
        // Do nothing, all processes are already finished
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
