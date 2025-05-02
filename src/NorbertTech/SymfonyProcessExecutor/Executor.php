<?php

declare(strict_types=1);

namespace NorbertTech\SymfonyProcessExecutor;

use Aeon\Calendar\TimeUnit;

interface Executor
{
    public function execute() : void;

    public function pool() : ProcessPool;

    public function executionTime() : TimeUnit;
}
