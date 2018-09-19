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

namespace ParkManager\Module\CoreModule\Infrastructure\DependencyInjection;

use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use function serialize;
use function unserialize;

/**
 * Tracks if a specific env-variable was changed.
 */
final class EnvVariableResource implements SelfCheckingResourceInterface
{
    private $resource;
    private $value;

    public function __construct(string $envName)
    {
        $this->resource = $envName;
        $this->value    = $_ENV[$envName] ?? null;
    }

    public function __toString(): string
    {
        return 'env:' . $this->resource;
    }

    public function isFresh($timestamp): bool
    {
        return ($_ENV[$this->resource] ?? null) === $this->value;
    }

    public function serialize()
    {
        return serialize([$this->resource, $this->value]);
    }

    public function unserialize($serialized)
    {
        list($this->resource, $this->value) = unserialize($serialized, ['allowed_classes' => false]);
    }
}
