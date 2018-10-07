<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Constraint that asserts that the Traversable it is applied to contains
 * not value of a given type.
 */
class TraversableNotContains extends Constraint
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
        $success = true;

        foreach ($other as $item) {
            if ($this->constraint->evaluate($item, '', true)) {
                $success = false;

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
        return 'contains not value of type "' . $this->type . '"';
    }
}
