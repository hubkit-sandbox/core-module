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

namespace ParkManager\Module\CoreModule\Test\Domain;

use PHPUnit\Framework\Assert;
use ReflectionMethod;
use ReflectionObject;
use function get_class;
use function is_object;
use function method_exists;

final class DomainMessageAssertion
{
    public static function assertMessagesAreEqual(object $expected, object $actual): void
    {
        Assert::assertInstanceOf(get_class($expected), $actual);

        foreach (self::findPublicEventMethods($expected) as $method) {
            $result       = $expected->{$method}();
            $secondResult = $actual->{$method}();

            if (is_object($result) && method_exists($result, 'equals') && $result->equals($secondResult)) {
                continue;
            }

            Assert::assertEquals($result, $secondResult);
        }
    }

    private static function findPublicEventMethods(object $event): iterable
    {
        foreach ((new ReflectionObject($event))->getMethods(ReflectionMethod::IS_PUBLIC) as $methodReflection) {
            if ($methodReflection->isStatic() || $methodReflection->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            yield $methodReflection->name;
        }
    }
}
