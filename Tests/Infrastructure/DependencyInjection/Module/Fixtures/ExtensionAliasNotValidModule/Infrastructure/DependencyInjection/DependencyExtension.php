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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\ExtensionAliasNotValidModule\Infrastructure\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DependencyExtension extends Extension
{
    public function getAlias()
    {
        return 'extension_valid_is_not';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
    }
}