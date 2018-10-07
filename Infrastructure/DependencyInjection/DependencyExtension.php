<?php

declare(strict_types=1);

/*
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This file is part of the Park-Manager project.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\DependencyInjection;

use ParkManager\Bridge\Twig\EventListener\TwigResponseListener;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\ParkManagerModuleDependencyExtension;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\RegistersDoctrineDbalTypes;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\Traits\DoctrineDbalTypesConfiguratorTrait;
use ParkManager\Module\CoreModule\Infrastructure\Twig\AppContextGlobal;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function class_exists;

final class DependencyExtension extends ParkManagerModuleDependencyExtension implements RegistersDoctrineDbalTypes
{
    use DoctrineDbalTypesConfiguratorTrait;

    public const EXTENSION_ALIAS = 'park_manager';

    public function getAlias(): string
    {
        return self::EXTENSION_ALIAS;
    }

    public function getModuleName(): string
    {
        return 'ParkManagerCore';
    }

    protected function loadModule(array $configs, ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load('services.php');
        $loader->load('services/*.php', 'glob');

        if (class_exists(TwigResponseListener::class)) {
            $container->register(TwigResponseListener::class)
                ->addTag('kernel.event_subscriber')
                ->setArgument(0, ServiceLocatorTagPass::register($container, ['twig' => new Reference('twig')]));
        }
    }

    protected function prependExtra(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'globals' => ['app_context' => '@' . AppContextGlobal::class],
        ]);
    }

    protected function registerRoutes(RouteImporter $routeImporter, ?string $configDir): void
    {
        $routeImporter->import($configDir . '/routing/administrator.php', 'park_manager.admin_section.root');
        $routeImporter->import($configDir . '/routing/client.php', 'park_manager.client_section.root');
    }
}
