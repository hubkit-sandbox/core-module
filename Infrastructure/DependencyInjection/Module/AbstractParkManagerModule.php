<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function count;
use function file_exists;
use function get_class;
use function preg_replace;
use function sprintf;

abstract class AbstractParkManagerModule extends Bundle implements ParkManagerModule
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $extension = $this->createContainerExtension();

            if ($extension !== null) {
                if (! $extension instanceof ExtensionInterface) {
                    throw new \LogicException(
                        sprintf(
                            'Extension %s must implement Symfony\Component\DependencyInjection\Extension\ExtensionInterface.',
                            get_class($extension)
                        )
                    );
                }

                // Check the naming convention.
                // Park-Manager vendor Modules don't have to follow the alias convention
                $expectedAlias = Container::underscore(preg_replace('/Module$/', '', $this->getName()));

                if ($expectedAlias !== $extension->getAlias()) {
                    throw new \LogicException(
                        sprintf(
                            'Users will expect the alias of the default extension of a module to be the underscored version of the module name ("%s"). ' .
                            'You can override "AbstractParkManagerModule::getContainerExtension()" if you want to use "%s" or another alias.',
                            $expectedAlias,
                            $extension->getAlias()
                        )
                    );
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension !== false) {
            return $this->extension;
        }

        return null;
    }

    public function build(ContainerBuilder $container): void
    {
        $doctrineMapping = $this->getDoctrineOrmMappings();

        if (count($doctrineMapping) !== 0) {
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createXmlMappingDriver($doctrineMapping, $this->getDoctrineEmNames())
            );
        }
    }

    protected function getContainerExtensionClass(): string
    {
        return $this->getNamespace() . '\\Infrastructure\\DependencyInjection\\DependencyExtension';
    }

    /**
     * Return the list of EntityManager names (either [default]) to register the mappings for.
     *
     * @return string[]
     */
    protected function getDoctrineEmNames(): array
    {
        return [];
    }

    /**
     * Gets the mappings for Doctrine ORM (XML only).
     *
     * @return string[] [directory => namespace-prefix]
     */
    protected function getDoctrineOrmMappings(): array
    {
        $namespace = $this->getNamespace();
        $path      = $this->getPath() . '/Infrastructure/Doctrine/';

        $mappings = [];

        if (file_exists($path)) {
            foreach (new \DirectoryIterator($path) as $node) {
                if ($node->isDot()) {
                    continue;
                }

                $basename  = $node->getBasename();
                $directory = $path . $basename . '/Mapping';

                if (file_exists($directory)) {
                    $mappings[$directory] = $namespace . '\\Domain\\' . $basename;
                }
            }
        }

        return $mappings;
    }
}
