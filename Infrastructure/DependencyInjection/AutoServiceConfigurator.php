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

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\DefaultsConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

final class AutoServiceConfigurator
{
    private const INTERFACES_IGNORE_LIST = [
        ObjectRepository::class,
        Selectable::class,
    ];

    /** @var DefaultsConfigurator|ServicesConfigurator */
    private $di;

    /**
     * @param DefaultsConfigurator|ServicesConfigurator $di
     */
    public function __construct($di)
    {
        $this->di = $di;
    }

    /**
     * Registers a service, and creates aliases for all implemented interfaces.
     *
     * Note: If an id is given the class aliased explicitly
     * to ensure autowiring works as expected.
     *
     * @param string      $id    The service id
     * @param string|null $class The class of the service, or null when $id is also the class name
     */
    public function set(string $id, ?string $class = null): ServiceConfigurator
    {
        if ($class === null) {
            $class = $id;
        } else {
            // If an id was given alias the class explicitly
            // to ensure autowiring works as expected
            $this->di->alias($class, $id);
        }

        foreach (class_implements($class) as $interface) {
            if (in_array($interface, self::INTERFACES_IGNORE_LIST, true)) {
                continue;
            }

            $this->di->alias($interface, $id);
        }

        return $this->di->set($id, $class);
    }
}
