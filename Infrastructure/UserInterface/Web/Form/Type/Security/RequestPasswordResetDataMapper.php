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

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormInterface;
use function iterator_to_array;

/**
 * @internal
 */
final class RequestPasswordResetDataMapper implements DataMapperInterface
{
    /** @var callable */
    private $commandBuilder;

    public function __construct(callable $commandBuilder)
    {
        $this->commandBuilder = $commandBuilder;
    }

    public function mapDataToForms($data, $forms)
    {
        // No-op
    }

    public function mapFormsToData($forms, &$data)
    {
        /** @var FormInterface[] $formsArray */
        $formsArray = iterator_to_array($forms);

        $data = ($this->commandBuilder)((string) $formsArray['email']->getData());
    }
}
