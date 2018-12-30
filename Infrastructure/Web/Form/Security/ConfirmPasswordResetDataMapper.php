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

namespace ParkManager\Module\CoreModule\Infrastructure\Web\Form\Security;

use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormInterface;
use function iterator_to_array;

/**
 * @internal
 */
final class ConfirmPasswordResetDataMapper implements DataMapperInterface
{
    /** @var SplitTokenFactory */
    private $splitTokenFactory;

    /** @var callable */
    private $commandBuilder;

    public function __construct(SplitTokenFactory $splitTokenFactory, callable $commandBuilder)
    {
        $this->splitTokenFactory = $splitTokenFactory;
        $this->commandBuilder    = $commandBuilder;
    }

    public function mapDataToForms($data, $forms)
    {
        // No-op
    }

    public function mapFormsToData($forms, &$data)
    {
        /** @var FormInterface[] $formsArray */
        $formsArray = iterator_to_array($forms);

        try {
            $token = (string) $formsArray['reset_token']->getData();
            $token = ($this->splitTokenFactory)->fromString($token);
        } catch (\RuntimeException $e) {
            throw new TransformationFailedException('Invalid token', 0, $e);
        }

        $data = ($this->commandBuilder)($token, (string) $formsArray['password']->getData());
    }
}
