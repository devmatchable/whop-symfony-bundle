<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use Matchable\Whop\Bundle\Controller\WhopWebhookController;

return static function (RoutingConfigurator $routes): void {
    $routes->add('whop_webhook', '%whop.webhook_path%')
        ->controller(WhopWebhookController::class)
        ->methods(['POST']);
};
