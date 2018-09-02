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

namespace ParkManager\Module\CoreModule\Domain\Administrator;

use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorWasRegistered;
use ParkManager\Module\CoreModule\Domain\Shared\AbstractUser;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

/**
 * @final
 *
 * @method AdministratorId id()
 */
class Administrator extends AbstractUser
{
    private $displayName;

    public static function registerWith(AdministratorId $id, EmailAddress $email, string $displayName, ?string $password = null): self
    {
        $user = new static($id, $email, $displayName);
        $user->recordThat(new AdministratorWasRegistered($id, $email, $displayName));
        $user->changePassword($password);

        return $user;
    }

    protected function __construct(AdministratorId $id, EmailAddress $email, string $displayName)
    {
        parent::__construct($id, $email);

        $this->displayName = $displayName;
    }

    public function changeName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    protected static function getDefaultRoles(): array
    {
        return [self::DEFAULT_ROLE, 'ROLE_ADMIN'];
    }
}
