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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Action\User;

use Symfony\Bundle\FrameworkBundle\Client;

return;
/**
 * @internal
 *
 * @group functional
 */
final class ChangePasswordActionTest extends ChangePasswordActionTestCase
{
    protected function getActionUri(Client $client): string
    {
        return $client->getContainer()->get('router')->generate('park_manager.administrator.change_password');
    }

    protected function getRedirectUri(Client $client): string
    {
        return $client->getContainer()->get('router')->generate('park_manager.admin.home');
    }

    protected function getRepositoryServiceId(): string
    {
        return 'park_manager.repository.administrator';
    }
}
