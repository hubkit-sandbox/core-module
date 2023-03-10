<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule;

use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\DependencyExtension;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\EnvVariableResource;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\AbstractParkManagerModule;
use ParkManager\Module\CoreModule\Infrastructure\Http\CookiesRequestMatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpFoundation\RequestMatcher;
use function realpath;

// Gary was here
class ParkManagerCoreModule extends AbstractParkManagerModule
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new DependencyExtension();
        }

        return $this->extension;
    }

    public static function setAppConfiguration(ContainerBuilder $container): void
    {
        $container->addResource(new EnvVariableResource('PRIMARY_HOST'));
        $container->addResource(new EnvVariableResource('ENABLE_HTTPS'));

        $isSecure    = (($_ENV['ENABLE_HTTPS'] ?? 'false') === 'true');
        $primaryHost = $_ENV['PRIMARY_HOST'] ?? null;

        $container->setParameter('park_manager.config.primary_host', $_ENV['PRIMARY_HOST'] ?? '');
        $container->setParameter('park_manager.config.requires_channel', $isSecure ? 'https' : null);
        $container->setParameter('park_manager.config.is_secure', $isSecure);

        $container->register('park_manager.section.admin.request_matcher', RequestMatcher::class)->setArguments(['^/admin/']);
        $container->register('park_manager.section.client.request_matcher', RequestMatcher::class)->setArguments(['^/(?!(api|admin)/)']);
        $container->register('park_manager.section.api.request_matcher', RequestMatcher::class)->setArguments(['/api']);

        $container->register('park_manager.section.private.request_matcher', CookiesRequestMatcher::class)
            ->setArguments(['^/(?!(api|admin)/)'])
            ->addMethodCall('matchCookies', [['_private_section' => '^true$']]);

        if ($primaryHost !== null) {
            $container->getDefinition('park_manager.section.admin.request_matcher')->setArgument(1, $primaryHost);
            $container->getDefinition('park_manager.section.private.request_matcher')->setArgument(1, $primaryHost);
            $container->getDefinition('park_manager.section.api.request_matcher')->setArguments(['/', '^api\.']);
        }
    }

    protected function getDoctrineOrmMappings(): array
    {
        $mapping = parent::getDoctrineOrmMappings();
        $mapping[realpath(__DIR__ . '/Infrastructure/Doctrine/SecurityMapping')] = 'Rollerworks\\Component\\SplitToken';
        return $mapping;
    }
}
