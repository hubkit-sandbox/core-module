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

namespace ParkManager\Module\CoreModule\Infrastructure\Twig;

use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\ApplicationContext;

/**
 * AppContextGlobal gives access to the ApplicationContext with limited information.
 *
 * It is prohibited to set the active section using the globals.
 */
final class AppContextGlobal
{
    private $applicationContext;

    public function __construct(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    public function getRoutePrefix(): string
    {
        return $this->applicationContext->getRouteNamePrefix();
    }

    public function getActiveSection(): string
    {
        return $this->applicationContext->getActiveSection();
    }

    public function isPrivateSection(): bool
    {
        return $this->applicationContext->isPrivateSection();
    }
}
