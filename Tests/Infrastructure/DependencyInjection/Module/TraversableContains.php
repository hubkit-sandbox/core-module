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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Constraint that asserts that the Traversable it is applied to contains
 * only values of a given type.
 */
class TraversableContains extends Constraint
{
    protected $constraint;
    protected $type;

    public function __construct(string $type, bool $isNativeType = true)
    {
        parent::__construct();

        if ($isNativeType) {
            $this->constraint = new IsType($type);
        } else {
            $this->constraint = new IsInstanceOf(
                $type
            );
        }

        $this->type = $type;
    }

    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = false;

        foreach ($other as $item) {
            if ($this->constraint->evaluate($item, '', true)) {
                $success = true;

                break;
            }
        }

        if ($returnResult) {
            return $success;
        }

        if (! $success) {
            $this->fail($other, $description);
        }
    }

    public function toString(): string
    {
        return 'contains value of type "' . $this->type . '"';
    }
}
