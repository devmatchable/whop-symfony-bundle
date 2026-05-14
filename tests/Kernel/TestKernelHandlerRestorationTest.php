<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Kernel;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Pins {@see TestKernel}'s error/exception-handler drain-and-restore behaviour.
 *
 * FrameworkBundle::boot() leaves global handlers registered; TestKernel snapshots
 * and rebuilds the stacks so PHPUnit's risky-test check stays clean. If a future
 * PHPUnit/Symfony change breaks that, this surfaces as a clear failure rather than
 * a flapping risky-test warning.
 */
final class TestKernelHandlerRestorationTest extends TestCase
{
    public function testHandlerStacksAreRestoredAfterShutdown(): void
    {
        $sentinelError = static fn (): bool => false;
        $sentinelException = static fn (\Throwable $e): null => null;

        set_error_handler($sentinelError);
        set_exception_handler($sentinelException);

        try {
            $kernel = new TestKernel(['api_key' => 'apik_test', 'webhook_secret' => 'ws_test']);
            $kernel->boot();
            $dir = $kernel->getCacheDir();
            $kernel->shutdown();
            (new Filesystem())->remove($dir);

            $currentError = set_error_handler(static fn (): bool => false);
            restore_error_handler();
            self::assertSame($sentinelError, $currentError, 'TestKernel did not restore the error handler stack.');

            $currentException = set_exception_handler(static fn (\Throwable $e): null => null);
            restore_exception_handler();
            self::assertSame($sentinelException, $currentException, 'TestKernel did not restore the exception handler stack.');
        } finally {
            restore_exception_handler();
            restore_error_handler();
        }
    }
}
