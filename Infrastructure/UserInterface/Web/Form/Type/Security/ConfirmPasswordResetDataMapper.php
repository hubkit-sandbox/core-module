<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use function is_array;
use function iterator_to_array;

/**
 * @internal
 */
final class ConfirmPasswordResetDataMapper implements DataMapperInterface
{
    /** @var callable */
    private $commandBuilder;

    public function __construct(callable $commandBuilder)
    {
        $this->commandBuilder    = $commandBuilder;
    }

    public function mapDataToForms($data, $forms): void
    {
        $empty = $data === null || $data === [];

        if (! $empty && ! is_array($data)) {
            throw new UnexpectedTypeException($data, 'array or empty');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (! $empty && $config->getMapped()) {
                $form->setData($data[$config->getName()] ?? null);
            } else {
                $form->setData($form->getConfig()->getData());
            }
        }
    }

    public function mapFormsToData($forms, &$data): void
    {
        /** @var FormInterface[] $formsArray */
        $formsArray = iterator_to_array($forms);

        $data = ($this->commandBuilder)(
            $formsArray['reset_token']->getData(),
            (string) $formsArray['password']->getData()
        );
    }
}
