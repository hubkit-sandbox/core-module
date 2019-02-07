<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module;

use LogicException;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\Traits\ServiceLoaderTrait;
use ReflectionClass;
use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function dirname;
use function file_exists;
use function is_dir;
use function realpath;
use function sprintf;
use function substr;

/**
 * The ParkManagerModuleDependencyExtension provides an addition
 * to the DependencyInjection Extension by wiring some
 * configurations automatically.
 *
 * This extension loads Routes, templates, translations,
 * and Doctrine DBAL Types (if the RegistersDoctrineDbalTypes interface is implemented).
 *
 * Templates: Infrastructure/Resources/templates and UI/Web/Resources/templates
 * Translations: Infrastructure/Resources/translations and UI/Web/Resources/translations
 * Services: Infrastructure/Resources/config/services/
 * Routes: Infrastructure/Resources/config
 *
 * Use the ServiceLoaderTrait if you only need the Services loader.
 */
abstract class ParkManagerModuleDependencyExtension extends Extension implements PrependExtensionInterface
{
    use ServiceLoaderTrait;

    /** @var string|null */
    protected $moduleDir;

    /** @var string|null */
    protected $moduleNamespace;

    /**
     * Name of this Module (with vendor namespace).
     *
     * @return string either AcmeWebhosting
     */
    abstract public function getModuleName(): string;

    /**
     * Configures a number of common operations.
     * Use loadModule() to load additional configurations.
     *
     * @internal
     *
     * @param array[] $configs
     */
    final public function load(array $configs, ContainerBuilder $container): void
    {
        $this->initModuleDirectory();

        $routeImporter = new RouteImporter($container);
        $routeImporter->addObjectResource($this);
        $this->registerRoutes($routeImporter, realpath($this->moduleDir . '/Infrastructure/Resources/config') ?: null);

        $loader = $this->getServiceLoader($container, $this->moduleDir . '/Infrastructure/Resources/config');
        $this->loadModule($configs, $container, $loader);
    }

    /**
     * Configures the translator paths, templates paths, and DomainId
     * DBAL types. Use prependExtra() to prepend extension configurations.
     *
     * Note: Registers only when directory or methods exist.
     *
     * @internal
     */
    final public function prepend(ContainerBuilder $container): void
    {
        $this->initModuleDirectory();
        $resourcesDirectory = $this->moduleDir . '/Infrastructure/Resources';

        if (is_dir($resourcesDirectory . '/translations')) {
            $container->prependExtensionConfig('framework', [
                'translator' => [
                    'paths' => [$resourcesDirectory . '/translations'],
                ],
            ]);
        }

        if (is_dir($this->moduleDir . '/Infrastructure/Web/Resources/translations')) {
            $container->prependExtensionConfig('framework', [
                'translator' => [
                    'paths' => [$this->moduleDir . '/Infrastructure/Web/Resources/translations'],
                ],
            ]);
        }

        if (is_dir($resourcesDirectory . '/templates')) {
            $container->prependExtensionConfig('twig', [
                'paths' => [$resourcesDirectory . '/templates' => $this->getModuleName()],
            ]);
        }

        if (is_dir($this->moduleDir . '/Infrastructure/Web/Resources/templates')) {
            $container->prependExtensionConfig('twig', [
                'paths' => [$this->moduleDir . '/Infrastructure/Web/Resources/templates' => $this->getModuleName()],
            ]);
        }

        if ($this instanceof RegistersDoctrineDbalTypes) {
            $this->registerDoctrineDbalTypes($container, $this->moduleDir);
        }

        $this->prependExtra($container);
    }

    /**
     * Loads a specific configuration.
     *
     * @param array[]         $configs The configs (unprocessed)
     * @param LoaderInterface $loader  Service definitions loader for "all" supported types
     *                                 including Glob, Directory, Closure and ini
     */
    protected function loadModule(array $configs, ContainerBuilder $container, LoaderInterface $loader): void
    {
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * prepend() is final, use this method instead.
     */
    protected function prependExtra(ContainerBuilder $container): void
    {
    }

    /**
     * Registers the routes using the RouteImporter importer.
     *
     * Use the following slots for sections:
     *
     * * 'park_manager.client_section.root': Client section root
     * * 'park_manager.admin_section.root': Admin section root
     * * 'park_manager.api_section.root': API (both client and admin)
     *
     * Or use 'park_manager.root' to import at the root (/)
     * of the routing scheme (only for error pages and utils).
     *
     * Example:
     *
     *   $routeImporter->import($configDir.'/routing/client.php', 'park_manager.client_section.root');
     *   $routeImporter->import($configDir.'/routing/admin.php', 'park_manager.admin_section.root');
     *
     * @param string $configDir Full path of Resources/config directory
     *                          (null when missing)
     */
    protected function registerRoutes(RouteImporter $routeImporter, ?string $configDir): void
    {
    }

    final protected function initModuleDirectory(): void
    {
        if ($this->moduleDir === null) {
            $r = new ReflectionClass(static::class);
            $namespace = $r->getNamespaceName();

            if (substr($namespace, -35) !== '\\Infrastructure\\DependencyInjection') {
                throw new LogicException(sprintf('The namespace "%s" is expected to end with "\\Infrastructure\\DependencyInjection".', $namespace));
            }

            $this->moduleNamespace = substr($namespace, 0, -35);
            $this->moduleDir = dirname($r->getFileName(), 3);
        }
    }

    protected function registerMessageBusHandlers(LoaderInterface $loader): void
    {
        /** @var GlobFileLoader $resolver */
        $resolver = $loader->getResolver()->resolve('*', 'glob');

        if (file_exists($this->moduleDir . '/Application/Command')) {
            $resolver->registerClasses(
                (new Definition())->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setPrivate(true)
                    ->addTag('messenger.message_handler', ['bus' => 'park_manager.command_bus']),
                $this->moduleNamespace . '\\Application\\Command\\',
                $this->moduleDir . '/Application/Command/**/*Handler.php'
            );
        }

        if (file_exists($this->moduleDir . '/Application/Query')) {
            $resolver->registerClasses(
                (new Definition())->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setPrivate(true)
                    ->addTag('messenger.message_handler', ['bus' => 'park_manager.query_bus']),
                $this->moduleNamespace . '\\Application\\Query\\',
                $this->moduleDir . '/Application/Query/**/*Handler.php'
            );
        }
    }

    protected function registerWebUIActions(LoaderInterface $loader): void
    {
        /** @var GlobFileLoader $resolver */
        $resolver = $loader->getResolver()->resolve('*', 'glob');

        if (file_exists($this->moduleDir . '/Infrastructure/UserInterface/Web/Action')) {
            $resolver->registerClasses(
                (new Definition())->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setPrivate(true),
                $this->moduleNamespace . '\\Infrastructure\\UserInterface\\Web\\Action\\',
                $this->moduleDir . '/Infrastructure/UserInterface/Web/Action/**/*.php'
            );
        }
    }
}
