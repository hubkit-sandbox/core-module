<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine\Client;

use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Infrastructure\Doctrine\DomainIdType;

final class ClientIdType extends DomainIdType
{
    public const NAME         = 'park_manager_client_id';
    public const OBJECT_CLASS = ClientId::class;
}
