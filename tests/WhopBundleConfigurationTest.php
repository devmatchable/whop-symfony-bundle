<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests;

use Matchable\Whop\Bundle\Tests\Kernel\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

final class WhopBundleConfigurationTest extends TestCase
{
    public function testItBootsWithValidConfigurationAndAppliesDefaults(): void
    {
        $kernel = new TestKernel(['api_key' => 'apik_test', 'webhook_secret' => 'ws_test']);
        $kernel->boot();

        $container = $kernel->getContainer();
        self::assertSame('apik_test', $container->getParameter('whop.api_key'));
        self::assertSame('ws_test', $container->getParameter('whop.webhook_secret'));
        self::assertSame('https://api.whop.com/api/v1', $container->getParameter('whop.base_url'));
        self::assertSame('/_whop/webhook', $container->getParameter('whop.webhook_path'));

        (new Filesystem())->remove($kernel->getCacheDir());
        $kernel->shutdown();
    }

    public function testItRejectsMissingApiKey(): void
    {
        $kernel = new TestKernel(['webhook_secret' => 'ws_test']);

        $this->expectException(InvalidConfigurationException::class);

        try {
            $kernel->boot();
        } finally {
            (new Filesystem())->remove($kernel->getCacheDir());
        }
    }
}
