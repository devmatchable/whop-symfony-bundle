<?php

declare(strict_types=1);

namespace Matchable\Whop\Bundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class WhopBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('webhook_secret')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('base_url')
                    ->defaultValue('https://api.whop.com/api/v1')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('http_client')->defaultNull()->end()
                ->scalarNode('webhook_path')
                    ->defaultValue('/_whop/webhook')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    /**
     * @param array{api_key: string, webhook_secret: string, base_url: string, http_client: string|null, webhook_path: string} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('whop.api_key', $config['api_key'])
            ->set('whop.webhook_secret', $config['webhook_secret'])
            ->set('whop.base_url', $config['base_url'])
            ->set('whop.webhook_path', $config['webhook_path']);

        $container->import('../config/services.php');

        if (null !== $config['http_client']) {
            $builder->setAlias('whop.inner_http_client', $config['http_client']);
        }
    }
}
