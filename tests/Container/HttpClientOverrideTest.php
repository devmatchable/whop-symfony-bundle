<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle\Tests\Container;

use Matchable\Whop\Bundle\Tests\Kernel\TestKernel;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HttpClientOverrideTest extends TestCase
{
    public function testPublicPsr18WrapperRoutesThroughUserSuppliedInnerClient(): void
    {
        $customClientId = 'app.test_whop_http_client';
        $expectedBody = 'whop-mock-response-body';
        $observedUrls = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use ($expectedBody, &$observedUrls): MockResponse {
            $observedUrls[] = $url;

            return new MockResponse($expectedBody);
        });

        $kernel = new TestKernel(
            [
                'api_key' => 'apik_test',
                'webhook_secret' => 'ws_test',
                'http_client' => $customClientId,
            ],
            static function (ContainerConfigurator $container) use ($customClientId): void {
                $container->services()
                    ->set($customClientId, MockHttpClient::class)
                    ->public()
                    ->synthetic();
            },
        );
        $kernel->boot();

        $container = $kernel->getContainer()->get('test.service_container');
        $container->set($customClientId, $mock);

        $whopHttpClient = $container->get('whop.http_client');
        self::assertInstanceOf(ClientInterface::class, $whopHttpClient);

        $response = $whopHttpClient->sendRequest(new Request('GET', 'https://example.test/whop'));

        self::assertSame($expectedBody, (string) $response->getBody());
        self::assertSame(['https://example.test/whop'], $observedUrls);

        $kernel->shutdown();
        (new Filesystem())->remove($kernel->getCacheDir());
    }
}
