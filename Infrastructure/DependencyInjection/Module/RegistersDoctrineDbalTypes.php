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

namespace ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Marker interface for the DoctrineDbalTypesConfiguratorTrait.
 */
interface RegistersDoctrineDbalTypes
{
    public function registerDoctrineDbalTypes(ContainerBuilder $container, string $moduleDirectory): void;
}
