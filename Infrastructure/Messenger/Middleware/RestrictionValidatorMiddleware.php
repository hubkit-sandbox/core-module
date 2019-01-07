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

namespace ParkManager\Module\CoreModule\Infrastructure\Messenger\Middleware;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

/**
 * Lazily executes a chain of restriction-validators till one throws an exception.
 *
 * A restriction could be package-limitation or limitation of current "plan".
 *
 * This validator should not be used determine the correctness of a message
 * or if the current user is allowed to dispatch this message.
 */
final class RestrictionValidatorMiddleware implements MiddlewareInterface
{
    private $validatorsContainer;
    private $validatorClasses;

    /**
     * @param string[] $validatorClasses A container with only RestrictionValidator services
     */
    public function __construct(ContainerInterface $validatorsContainer, array $validatorClasses)
    {
        $this->validatorsContainer = $validatorsContainer;
        $this->validatorClasses    = $validatorClasses;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        foreach ($this->validatorClasses as $class) {
            if ($class::accepts($message)) {
                $this->validatorsContainer->get($class)->validate($message);
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
