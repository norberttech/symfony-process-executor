<?php

declare(strict_types=1);

namespace Tests\Unit\NorbertTech\SymfonyProcessExecutor;

use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class ProcessPollTest extends TestCase
{
    public function test_not_started_processes() : void
    {
        $pool = new ProcessPool(
            \method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('echo 1') : new Process('echo 1'),
            \method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('echo 2') : new Process('echo 2'),
            \method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('echo 3') : new Process('echo 3'),
            \method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('echo 4') : new Process('echo 4'),
            \method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('echo 5') : new Process('echo 5'),
            \method_exists(Process::class, 'fromShellCommandline') ? Process::fromShellCommandline('echo 6') : new Process('echo 6'),
        );

        $this->assertCount(6, $pool->notStarted());
        $this->assertCount(6, $pool->notStarted(8));
        $this->assertCount(2, $pool->notStarted(2));
        $this->assertSame(0, $pool->unfinishedCount());
        $this->assertSame(6, $pool->notStartedCount());
    }

    public function test_started_processes() : void
    {
        $pool = new ProcessPool(
            $process = $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
        );

        $process->expects($this->once())
            ->method('getPid')
            ->willReturn(1);

        $process->method('isRunning')
            ->willReturn(false);

        $process->method('getExitCode')
            ->willReturn(0);

        $pool->all()[0]->start();
        $pool->all()[0]->check();

        $this->assertCount(5, $pool->notStarted());
        $this->assertCount(5, $pool->notStarted(8));
        $this->assertCount(2, $pool->notStarted(2));
        $this->assertSame(0, $pool->unfinishedCount());
        $this->assertSame(5, $pool->notStartedCount());
        $this->assertSame(1, $pool->succeeded());
        $this->assertSame(0, $pool->failed());
    }

    public function test_unfinished_processes() : void
    {
        $pool = new ProcessPool(
            $process = $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
            $this->createMock(Process::class),
        );

        $process->expects($this->once())
            ->method('getPid')
            ->willReturn(1);

        $process->method('isRunning')
            ->willReturn(true);

        $process->method('getExitCode')
            ->willReturn(null);

        $pool->all()[0]->start();
        $pool->all()[0]->check();

        $this->assertSame(1, $pool->unfinishedCount());
    }
}
