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

    private bool $hadErrorHandler = false;

    private bool $hadExceptionHandler = false;

    /**
     * @param array<string, mixed> $whopConfig  config passed to the `whop` extension
     * @param \Closure|null        $extraConfig optional extra container configuration
     */
    public function __construct(
        private readonly array $whopConfig,
        private readonly ?\Closure $extraConfig = null,
    ) {
        $this->cacheId = uniqid('whop_', true);
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [new FrameworkBundle(), new WhopBundle()];
    }

    /**
     * FrameworkBundle::boot() registers a global error/exception handler that it
     * never restores. Snapshot the handler state so {@see shutdown()} can undo it
     * and PHPUnit's risky-test check stays clean.
     */
    public function boot(): void
    {
        $errorHandler = set_error_handler(static fn (): bool => false);
        restore_error_handler();
        $this->hadErrorHandler = null !== $errorHandler;

        $exceptionHandler = set_exception_handler(null);
        set_exception_handler($exceptionHandler);
        $this->hadExceptionHandler = null !== $exceptionHandler;

        parent::boot();
    }

    public function shutdown(): void
    {
        parent::shutdown();

        $errorHandler = set_error_handler(static fn (): bool => false);
        restore_error_handler();
        if (null !== $errorHandler && !$this->hadErrorHandler) {
            restore_error_handler();
        }

        $exceptionHandler = set_exception_handler(null);
        set_exception_handler($exceptionHandler);
        if (null !== $exceptionHandler && !$this->hadExceptionHandler) {
            restore_exception_handler();
        }
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
        $routes->import('@WhopBundle/config/routes.xml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/whop-bundle-tests/'.$this->cacheId.'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/whop-bundle-tests/'.$this->cacheId.'/log';
    }
}
