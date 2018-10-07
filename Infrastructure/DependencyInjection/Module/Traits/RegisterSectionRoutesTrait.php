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

use Rollerworks\Bundle\RouteAutowiringBundle\RouteImporter;
use function file_exists;

trait RegisterSectionRoutesTrait
{
    /**
     * Registers the routes using the RouteImporter importer.
     *
     * Routing files are registers when they exists.
     */
    final protected function registerRoutes(RouteImporter $routeImporter, ?string $configDir): void
    {
        if (file_exists($configDir . '/routing/client.php')) {
            $routeImporter->import($configDir . '/routing/client.php', 'park_manager.client_section.root');
        }

        if (file_exists($configDir . '/routing/admin.php')) {
            $routeImporter->import($configDir . '/routing/admin.php', 'park_manager.admin_section.root');
        }

        if (file_exists($configDir . '/routing/api.php')) {
            $routeImporter->import($configDir . '/routing/api.php', 'park_manager.api_section.root');
        }
    }
}
