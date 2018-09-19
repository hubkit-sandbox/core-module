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

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use function is_string;

abstract class DomainIdType extends GuidType
{
    public const NAME         = 'park_manager_domain_id';
    public const OBJECT_CLASS = null;

    final public function getName(): string
    {
        return static::NAME;
    }

    final public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        return $value->toString();
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        $class = static::OBJECT_CLASS;

        return $class::fromString($value);
    }
}
