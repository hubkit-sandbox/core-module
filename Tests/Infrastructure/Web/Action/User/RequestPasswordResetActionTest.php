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
final class RequestPasswordResetActionTest extends RequestPasswordResetActionTestCase
{
    protected function getEntryUri(): string
    {
        return '/admin/resetting';
    }

    protected function getFormName(): string
    {
        return 'request_user_password_reset';
    }

    protected function getEmailAddress(): string
    {
        return 'janE@example.com';
    }

    protected function getLoginUri(): string
    {
        return '/admin/login';
    }
}
