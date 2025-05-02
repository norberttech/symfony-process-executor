<?php

declare(strict_types=1);

namespace NorbertTech\SymfonyProcessExecutor;

use Aeon\Calendar\Stopwatch;
use Aeon\Calendar\TimeUnit;
use NorbertTech\SymfonyProcessExecutor\Exception\Exception;

final class AsynchronousExecutor implements Executor
{
    /**
     * @param ProcessPool $pool - pool of processes to execute
     * @param ?TimeUnit $sleep - sleep time between checking out running processes
     * @param ?TimeUnit $timeout - timeout, after this time all processes are going to be killed
     * @param ?int $batchSize - how many processes should be executed in parallel, when null, all processes are executed in parallel
     */
    public function __construct(private readonly ProcessPool $pool, private readonly ?TimeUnit $sleep = null, private readonly ?TimeUnit $timeout = null, private ?int $batchSize = null, private readonly Stopwatch $stopwatch = new Stopwatch())
    {
        if ($this->stopwatch->isStarted()) {
            throw new Exception('AsynchronousExecutor already started');
        }
    }

    /**
     * @throws Exception
     */
    public function execute() : void
    {
        if ($this->stopwatch->isStarted()) {
            throw new Exception('AsynchronousExecutor already started');
        }

        $sleep = $this->sleep ?: TimeUnit::milliseconds(100);
        $total = TimeUnit::seconds(0);

        $this->stopwatch->start();

        while ($this->pool->notStartedCount() > 0) {
            foreach ($this->pool->notStarted($this->batchSize) as $process) {
                $process->start();
            }

            while ($this->pool->unfinishedCount() > 0) {
                foreach ($this->pool->notFinished() as $process) {
                    $process->check();
                }

                \Aeon\Sleep\sleep($sleep);

                $total = $total->add($sleep);

                if ($this->timeout) {
                    if ($total->isGreaterThan($this->timeout)) {
                        $this->pool->each(function (ProcessWrapper $process) : void {
                            $process->kill();
                        });
                    }
                }
            }
        }

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
