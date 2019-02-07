<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Doctrine\Shared;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;
use function is_resource;
use function json_decode;
use function json_encode;
use function stream_get_contents;

final class ArrayCollectionType extends JsonType
{
    /**
     * @param Collection|null $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value->toArray());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ArrayCollection
    {
        if ($value === null || $value === '') {
            return new ArrayCollection();
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        return new ArrayCollection(json_decode($value, true));
    }

    public function getName(): string
    {
        return 'array_collection';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
