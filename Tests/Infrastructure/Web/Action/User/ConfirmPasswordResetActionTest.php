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

return;
/**
 * @internal
 * @group functional
 */
class ConfirmPasswordResetActionTest extends ConfirmPasswordResetActionTestCase
{
    protected function getRepositoryServiceId(): string
    {
        return 'park_manager.repository.administrator';
    }

    protected function getEntryUri(string $token): string
    {
        return '/admin/resetting/confirm/' . $token;
    }

    protected function getLoginUri(): string
    {
        return '/admin/login';
    }

    protected function getUserId(): string
    {
        return '571f67a0-11ea-4229-968a-856af108d342';
    }

    protected function getOnSuccessUri(): string
    {
        return '/admin/';
    }
}
