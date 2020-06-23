# Symfony Process Executor 

![Tests](https://github.com/norberttech/symfony-process-executor/workflows/Tests/badge.svg?branch=2.x)

Tiny library that simplifies launching multiple processes in parallel (or not). 

### Installation

```bash
composer require norberttech/symfony-process-executor
```

### Examples 

```php
<?php

use NorbertTech\SymfonyProcessExecutor\AsynchronousExecutor;
use NorbertTech\SymfonyProcessExecutor\ProcessPool;
use NorbertTech\SymfonyProcessExecutor\ProcessWrapper;
use Symfony\Component\Process\Process;

$processes = new ProcessPool(
    Process::fromShellCommandline('sleep 1 && echo 1'),
    Process::fromShellCommandline('sleep 2 && echo 2'),
    Process::fromShellCommandline('sleep 3 && echo 3'),
    Process::fromShellCommandline('sleep 4 && echo 4'),
    Process::fromShellCommandline('sleep 5 && echo 5')
);

$executor = new AsynchronousExecutor($processes);

$executor->execute();

$executor->waitForAllToFinish();

$executor->pool()->each(function (ProcessWrapper $processWrapper) {
    var_dump($processWrapper->exitCode());
    var_dump(\trim($processWrapper->output()));
    var_dump($processWrapper->executionTime()->inSeconds());
    var_dump($processWrapper->executionTime()->inMilliseconds());
    var_dump($processWrapper->executionTime()->microsecond());
    echo "----\n";
});

echo \sprintf("Successfully finished child processes: %d\n", $executor->pool()->withSuccessExitCode());
echo \sprintf("Failure finished child processes: %d\n", $executor->pool()->withFailureExitCode());
echo \sprintf("Total execution time [s]: %d\n", $executor->executionTime()->inSecondsPreciseString());
```

Output: 

```bash
php examples/async_multiple_success_processes.php 

int(0)
string(1) "1"
int(1)
int(1033)
int(1033295)
----
int(0)
string(1) "2"
int(2)
int(2064)
int(2064680)
----
int(0)
string(1) "3"
int(3)
int(3092)
int(3092137)
----
int(0)
string(1) "4"
int(4)
int(4026)
int(4026060)
----
int(0)
string(1) "5"
int(5)
int(5052)
int(5052531)
----
Successfully finished child processes: 5
Failure finished child processes: 0
Total execution time [s]: 5
```

## Tests

All tests for this library are written as phpt files, you can read more about it here https://qa.php.net/phpt_details.php. 

In order to launch full testsuite use composer

```php
composer tests
```
