<?php

/**
 * TriTan CMS Functions for Unit Testing
 *
 * @license GPLv3
 *
 * @since 0.9.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 *
 * Example usage:
 *
 *      it("should sum two numbers", 1+1==2);
 *      it("should display an red X for a failing test", 1+1==3);
 *
 * @param string $m Message.
 * @param mixed $p Callable.
 */
function it($m, $p)
{
    $d = debug_backtrace(0)[0];
    is_callable($p) and $p = $p();
    global $e;
    $e = $e || !$p;
    $o = "\e[3" . ($p ? "2m✔" : "1m✘") . "\e[36m It $m";
    fwrite($p ? STDOUT : STDERR, $p ? "$o\n" : "$o \e[1;37;41mFAIL: {$d['file']} #" . $d['line'] . "\e[0m\n");
}

register_shutdown_function(function () {
    global $e;
    $e and die(1);
});

/**
 *
 * Example usage:
 *
 *      it("should do a bunch of calculations", all([
 *          1+1 == 2,
 *          1+2 == 1249
 *      ]);
 *
 * @param array $ps Array of callables.
 */
function all(array $ps)
{
    return array_reduce($ps, function ($a, $p) {
        return $a && $p;
    }, true);
}

/**
 *
 * Example usage:
 *
 *      it("should pass when the expected exception is thrown",
 *          throws("InvalidArgumentException", function () {
 *              throw new InvalidArgumentException;
 *      }));
 *
 * @param string $exp Exception to check for.
 * @param \Closure $cb
 * @return boolean
 */
function throws($exp, \Closure $cb)
{
    try {
        $cb();
    } catch (\Exception $e) {
        return $e instanceof $exp;
    }
    return false;
}

/**
 *
 * Example usage:
 *
 *      it('should use SomeInterface to do Something', withMock(function () {
 *          $mock = Mockery::mock('SomeInterface');
 *          $mock->shouldReceive('someMethod')
 *              ->with('someValue')
 *              ->once()
 *              ->andReturn(true);
 *
 *          $sut = new SystemToTest($mock);
 *          $sut->test();
 *      }));
 *
 * @param \Closure $cb
 * @return boolean
 */
function withMock(\Closure $cb)
{
    $cb();
    try {
        Mockery::close();
    } catch (\Exception $e) {
        echo $e->getMessage()."\n";
        return false;
    }
    return true;
}
