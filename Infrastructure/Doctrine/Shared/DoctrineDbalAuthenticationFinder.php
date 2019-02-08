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

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine\Shared;

use Doctrine\DBAL\Connection;
use ParkManager\Module\CoreModule\Application\Service\Finder\Shared\AuthenticationFinder;
use ParkManager\Module\CoreModule\Application\Service\Finder\Shared\SecurityAuthenticationData;
use function is_array;
use function json_decode;

final class DoctrineDbalAuthenticationFinder implements AuthenticationFinder
{
    /** @var Connection */
    private $connection;

    /** @var string */
    private $table;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function findAuthenticationByEmail(string $email): ?SecurityAuthenticationData
    {
        $data = $this->connection->fetchAssoc('SELECT id, auth_password, login_enabled, roles FROM ' . $this->table . ' WHERE email_address = ?', [$email]);

        if (! is_array($data)) {
            return null;
        }

        return new SecurityAuthenticationData(
            $data['id'],
            $data['auth_password'] === '' ? null : $data['auth_password'],
            (bool) $data['login_enabled'],
            json_decode($data['roles'], true)
        );
    }

    public function findAuthenticationById(string $id): ?SecurityAuthenticationData
    {
        $data = $this->connection->fetchAssoc('SELECT id, auth_password, login_enabled, roles FROM ' . $this->table . ' WHERE id = ?', [$id]);

        if (! is_array($data)) {
            return null;
        }

        return new SecurityAuthenticationData(
            $data['id'],
            $data['auth_password'] === '' ? null : $data['auth_password'],
            (bool) $data['login_enabled'],
            json_decode($data['roles'], true)
        );
    }
}
