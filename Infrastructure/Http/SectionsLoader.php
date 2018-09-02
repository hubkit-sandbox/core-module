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

namespace ParkManager\Module\CoreModule\Infrastructure\Http;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

final class SectionsLoader extends Loader
{
    private $loader;
    private $primaryHost;
    private $isSecure;

    /**
     * Constructor.
     *
     * @param LoaderResolverInterface $loader      Route loader resolver
     * @param string                  $primaryHost
     * @param bool                    $isSecure
     */
    public function __construct(LoaderResolverInterface $loader, ?string $primaryHost, bool $isSecure)
    {
        $this->loader = $loader;
        $this->primaryHost = $primaryHost;
        $this->isSecure = $isSecure;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();
        $collection->addCollection($this->loadAdminSection());
        $collection->addCollection($this->loadApiSection());
        $collection->addCollection($this->loadResource('park_manager.client_section.root'));

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'park_manager_sections_loader' === $type;
    }

    private function loadResource(string $resource): RouteCollection
    {
        $loader = $this->loader->resolve($resource, 'rollerworks_autowiring');
        /** @var RouteCollection $collection */
        $collection = $loader->load($resource, 'rollerworks_autowiring');

        if ($this->isSecure) {
            $collection->setSchemes(['https']);
        }

        return $collection;
    }

    private function loadAdminSection(): RouteCollection
    {
        $admin = $this->loadResource('park_manager.admin_section.root');
        $admin->addPrefix('admin/');

        if (null !== $this->primaryHost) {
            $admin->setHost($this->primaryHost);
        }

        return $admin;
    }

    private function loadApiSection(): RouteCollection
    {
        $api = $this->loadResource('park_manager.api_section.root');

        if (null !== $this->primaryHost) {
            $api->setHost('api.{host}');
            $api->addRequirements(['host' => '.+']);
            $api->addDefaults(['host' => $this->primaryHost]);
        } else {
            $api->addPrefix('api/');
        }

        return $api;
    }
}
