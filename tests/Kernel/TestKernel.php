<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Kernel;

use Matchable\Whop\Bundle\WhopBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private readonly string $cacheId;

    /** @var list<callable> */
    private array $errorHandlerSnapshot = [];

    /** @var list<callable> */
    private array $exceptionHandlerSnapshot = [];

    /**
     * @param array<string, mixed> $whopConfig  config passed to the `whop` extension
     * @param \Closure|null        $extraConfig optional extra container configuration
     */
    public function __construct(
        private readonly array $whopConfig,
        private readonly ?\Closure $extraConfig = null,
    ) {
        $this->cacheId = uniqid('whop_', true);

        // FrameworkBundle::boot() and DebugHandlersListener register global
        // error/exception handlers that are never restored. Snapshot the full
        // handler stacks now so shutdown() can rebuild them exactly and PHPUnit's
        // risky-test check (which compares the whole stack) stays clean.
        $this->errorHandlerSnapshot = self::drainErrorHandlers();
        foreach ($this->errorHandlerSnapshot as $handler) {
            set_error_handler($handler);
        }

        $this->exceptionHandlerSnapshot = self::drainExceptionHandlers();
        foreach ($this->exceptionHandlerSnapshot as $handler) {
            set_exception_handler($handler);
        }

        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new WhopBundle()];
    }

    public function shutdown(): void
    {
        parent::shutdown();

        // Drain whatever the kernel left on the handler stacks and rebuild the
        // exact state captured in the constructor.
        self::drainErrorHandlers();
        foreach ($this->errorHandlerSnapshot as $handler) {
            set_error_handler($handler);
        }

        self::drainExceptionHandlers();
        foreach ($this->exceptionHandlerSnapshot as $handler) {
            set_exception_handler($handler);
        }
    }

    /**
     * Pops every registered error handler and returns them bottom-to-top.
     *
     * @return list<callable>
     */
    private static function drainErrorHandlers(): array
    {
        $handlers = [];
        while (true) {
            $handler = set_error_handler(static fn (): bool => false);
            restore_error_handler();
            if (null === $handler) {
                break;
            }
            $handlers[] = $handler;
            restore_error_handler();
        }

        return array_reverse($handlers);
    }

    /**
     * Pops every registered exception handler and returns them bottom-to-top.
     *
     * @return list<callable>
     */
    private static function drainExceptionHandlers(): array
    {
        $handlers = [];
        while (true) {
            $handler = set_exception_handler(static fn (\Throwable $e): null => null);
            restore_exception_handler();
            if (null === $handler) {
                break;
            }
            $handlers[] = $handler;
            restore_exception_handler();
        }

        return array_reverse($handlers);
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'handle_all_throwables' => true,
            'http_client' => [],
            'router' => ['utf8' => true],
        ]);
        $container->extension('whop', $this->whopConfig);

        if (null !== $this->extraConfig) {
            ($this->extraConfig)($container);
        }
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('@WhopBundle/config/routes.php');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/whop-bundle-tests/'.$this->cacheId.'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/whop-bundle-tests/'.$this->cacheId.'/log';
    }

    /**
     * Keep FrameworkBundle's auto-generated `reference.php` out of the repo by
     * pointing the kernel config dir at the per-run temp directory.
     */
    public function getConfigDir(): string
    {
        return sys_get_temp_dir().'/whop-bundle-tests/'.$this->cacheId.'/config';
    }
}
