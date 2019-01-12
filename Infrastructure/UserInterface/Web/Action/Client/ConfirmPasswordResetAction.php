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

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Action\Client;

use Closure;
use ParkManager\Module\CoreModule\Application\Command\Client\ConfirmPasswordReset;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Action\AbstractConfirmPasswordResetAction;

final class ConfirmPasswordResetAction extends AbstractConfirmPasswordResetAction
{
    protected function getTemplate(): string
    {
        return '@ParkManagerCore/client/password_reset_confirm.html.twig';
    }

    protected function createCommand(): Closure
    {
        return static function (SplitToken $splitToken, string $password) {
            return new ConfirmPasswordReset($splitToken, $password);
        };
    }

    protected function getLoginRoute(): string
    {
        return 'park_manager.client.security_login';
    }
}
