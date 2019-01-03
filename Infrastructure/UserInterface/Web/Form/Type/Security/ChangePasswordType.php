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

use Closure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface as EncoderFactory;
use Symfony\Component\Validator\Constraint;
use function Sodium\memzero;

class ChangePasswordType extends AbstractType
{
    /** @var EncoderFactory */
    private $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userClass = $options['user_class'];
        $builder
            ->setDataMapper(new ChangePasswordDataMapper($options['command_builder']))
            ->add('user_id', HiddenType::class, ['data' => $options['user_id']])
            ->add('password', HashedPasswordType::class, [
                'required' => true,
                'password_confirm' => true,
                'password_options' => [
                    'constraints' => $options['password_constraints'],
                ],
                'algorithm' => function (string $value) use ($userClass) {
                    $encoded = $this->encoderFactory->getEncoder($userClass)->encodePassword($value, '');

                    memzero($value);

                    return $encoded;
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['user_class', 'command_builder', 'user_id'])
            ->setDefault('password_constraints', [])
            ->setDefault('empty_data', null)
            ->setAllowedTypes('user_class', ['string'])
            ->setAllowedTypes('command_builder', [Closure::class])
            ->setAllowedTypes('password_constraints', [Constraint::class . '[]', Constraint::class])
        ;
    }

    public function getBlockPrefix(): ?string
    {
        return 'change_user_password';
    }
}
