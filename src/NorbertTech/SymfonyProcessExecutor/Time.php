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

final class Time
{
    private const MILLISECOND_IN_MICROSECONDS = 1000;

    private const SECOND_IN_MICROSECONDS = self::MILLISECOND_IN_MICROSECONDS * 1000;

    /**
     * @var int
     */
    private $microseconds;

    private function __construct(int $microseconds)
    {
        $this->microseconds = $microseconds;
    }

    public static function fromMicroseconds(int $microseconds) : self
    {
        return new self($microseconds);
    }

    public static function fromSecondMicrosecondsFloat(float $secondsMicrosecondsFloat) : self
    {
        return new self((int) ($secondsMicrosecondsFloat * self::SECOND_IN_MICROSECONDS));
    }

    public static function fromMilliseconds(int $milliseconds) : self
    {
        return new self($milliseconds * self::MILLISECOND_IN_MICROSECONDS);
    }

    public static function fromSeconds(int $seconds) : self
    {
        return new self($seconds * self::SECOND_IN_MICROSECONDS);
    }

    public function add(self $time) : self
    {
        return new self($this->microseconds + $time->microseconds());
    }

    public function greaterThan(Time $timeout) : bool
    {
        return $this->microseconds > $timeout->microseconds;
    }

    public function seconds() : int
    {
        return (int) ($this->microseconds / self::SECOND_IN_MICROSECONDS);
    }

    public function milliseconds() : int
    {
        return (int) ($this->microseconds / self::MILLISECOND_IN_MICROSECONDS);
    }

    public function microseconds() : int
    {
        return $this->microseconds;
    }
}
