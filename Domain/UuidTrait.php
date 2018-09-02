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

namespace ParkManager\Module\CoreModule\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * An Identity holds a single UUID value.
 *
 * Use this trait any in ValueObject that uniquely identifies an Entity.
 */
trait UuidTrait
{
    private $value;
    private $stringValue;

    protected function __construct(UuidInterface $value)
    {
        $this->value = $value;
        $this->stringValue = $value->toString();
    }

    public static function create()
    {
        return new static(Uuid::uuid4());
    }

    public static function fromString(string $value)
    {
        return new static(Uuid::fromString($value));
    }

    public function __toString(): string
    {
        return $this->stringValue;
    }

    public function toString(): string
    {
        return $this->stringValue;
    }

    public function equals($identity): bool
    {
        if (!$identity instanceof self) {
            return false;
        }

        return $this->value->equals($identity->value);
    }

    public function serialize(): string
    {
        return $this->stringValue;
    }

    public function unserialize($serialized): void
    {
        $this->value = Uuid::fromString($serialized);
        $this->stringValue = $this->value->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->stringValue;
    }
}
