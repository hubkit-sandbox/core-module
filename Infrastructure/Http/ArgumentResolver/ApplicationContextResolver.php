<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Http\ArgumentResolver;

use Generator;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\ApplicationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ApplicationContextResolver implements ArgumentValueResolverInterface
{
    /** @var ApplicationContext */
    private $context;

    public function __construct(ApplicationContext $context)
    {
        $this->context = $context;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return ! $argument->isVariadic() && $argument->getType() === ApplicationContext::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield $this->context;
    }
}
