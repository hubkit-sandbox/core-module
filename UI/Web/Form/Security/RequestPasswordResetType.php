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

namespace ParkManager\Module\CoreModule\UI\Web\Form\Security;

use ParkManager\Module\CoreModule\Application\Command\Security\RequestUserPasswordReset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use function iterator_to_array;

class RequestPasswordResetType extends AbstractType implements DataMapperInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setDataMapper($this)
            ->add('email', EmailType::class, ['label' => 'label.email'])
        ;
    }

    public function getBlockPrefix(): ?string
    {
        return 'request_user_password_reset';
    }

    public function mapDataToForms($data, $forms)
    {
        // No-op
    }

    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);
        /** @var FormInterface[] $forms */
        $data = new RequestUserPasswordReset((string) $forms['email']->getData());
    }
}
