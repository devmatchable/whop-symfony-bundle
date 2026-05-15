<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Matchable\Whop\Bundle\Controller\WhopWebhookController;
use Matchable\Whop\Bundle\Webhook\EventDispatchingWebhookHandler;
use Matchable\Whop\Bundle\Webhook\WhopWebhookHandlerInterface;
use Matchable\Whop\Webhook\WebhookVerifier;
use Matchable\Whop\WhopApiClient;
use Symfony\Component\HttpClient\Psr18Client;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // PSR-18 client wrapping the inner Symfony HTTP client. The inner alias
    // points at the framework's `http_client` by default; WhopBundle re-aliases
    // it when `whop.http_client` config is set.
    $services->alias('whop.inner_http_client', 'http_client');

    $services->set('whop.http_client', Psr18Client::class)
        ->args([service('whop.inner_http_client')]);

    // Psr18Client implements PSR-17 RequestFactory/StreamFactory, so it covers
    // all three SDK constructor slots and bypasses the SDK's php-http/discovery
    // fallback. nyholm/psr7 underpins it for actual PSR-7 message construction.
    // WhopApiClient and WebhookVerifier are the bundle's public API: consumers
    // autowire them by type-hint, so they are registered public to stay
    // retrievable even when nothing in the bundle itself depends on them.
    $services->set(WhopApiClient::class)
        ->public()
        ->args([
            service('whop.http_client'),
            param('whop.api_key'),
            param('whop.base_url'),
            service('whop.http_client'),
            service('whop.http_client'),
        ]);

    $services->set(WebhookVerifier::class)
        ->public()
        ->args([param('whop.webhook_secret')]);

    $services->set(EventDispatchingWebhookHandler::class)
        ->args([service('event_dispatcher')]);

    // Private: production code reaches the handler only through the controller's
    // constructor injection. Tests fetch it via the test service container.
    $services->alias(WhopWebhookHandlerInterface::class, EventDispatchingWebhookHandler::class);

    $services->set(WhopWebhookController::class)
        ->args([
            service(WebhookVerifier::class),
            service(WhopWebhookHandlerInterface::class),
        ])
        ->tag('controller.service_arguments');
};
