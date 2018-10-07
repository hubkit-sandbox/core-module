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

namespace ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\Traits;

use Doctrine\DBAL\Types\Type as DbalType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use function class_exists;
use function file_exists;
use function is_subclass_of;
use function mb_substr;
use function preg_replace;
use function str_replace;

/**
 * Helps with automatically registering Doctrine DBAL types.
 *
 * Note: Implement the RegistersDoctrineDbalTypes interface to make this detection
 * work in the ParkManagerModuleDependencyExtension.
 */
trait DoctrineDbalTypesConfiguratorTrait
{
    /**
     * Registers the Doctrine DBAL Types (located in Module/Infrastructure/Doctrine).
     *
     * Overwrite this method to skip/change registering.
     * All types are assumed to be commented.
     */
    public function registerDoctrineDbalTypes(ContainerBuilder $container, string $moduleDirectory): void
    {
        if (! file_exists($moduleDirectory . '/Infrastructure/Doctrine')) {
            return;
        }

        $finder = new Finder();
        $finder->in($moduleDirectory . '/Infrastructure/Doctrine');
        $finder->name('*.php');
        $finder->files();

        $namespace = preg_replace('/\\\DependencyInjection\\\DependencyExtension$/', '', static::class) . '\\Doctrine\\';
        $types     = [];

        foreach ($finder as $node) {
            $className = $namespace . str_replace('/', '\\', mb_substr($node->getRelativePathname(), 0, -4));

            if (class_exists($className) && is_subclass_of($className, DbalType::class)) {
                $r = new \ReflectionClass($className);

                if ($r->isAbstract() || $r->isInterface() || $r->isTrait()) {
                    continue;
                }

                /** @var DbalType $type */
                $type                    = $r->newInstanceWithoutConstructor();
                $types[$type->getName()] = ['class' => $className, 'commented' => true];
            }
        }

        $container->prependExtensionConfig('doctrine', [
            'dbal' => ['types' => $types],
        ]);
    }
}
