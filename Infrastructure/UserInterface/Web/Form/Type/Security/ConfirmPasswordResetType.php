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
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface as EncoderFactory;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Validator\Constraint;
use function Sodium\memzero;

class ConfirmPasswordResetType extends AbstractType
{
    /** @var SplitTokenFactory */
    private $splitTokenFactory;

    /** @var EncoderFactory */
    private $encoderFactory;

    public function __construct(SplitTokenFactory $splitTokenFactory, EncoderFactory $encoderFactory)
    {
        $this->splitTokenFactory = $splitTokenFactory;
        $this->encoderFactory    = $encoderFactory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userClass = $options['user_class'];
        $builder
            ->setDataMapper(new ConfirmPasswordResetDataMapper($this->splitTokenFactory, $options['command_builder']))
            ->add('reset_token', HiddenType::class, ['data' => $options['token']->token()->getString()])
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
            ->setRequired(['token', 'user_class', 'command_builder'])
            ->setDefault('password_constraints', [])
            ->setDefault('exception_mapping', [
                PasswordResetTokenNotAccepted::class => function (PasswordResetTokenNotAccepted $e) {
                    return new FormError('password_reset.invalid_token', null, [], null, $e);
                },
                DisabledException::class => function (DisabledException $e) {
                    return new FormError('password_reset.access_disabled', null, [], null, $e);
                },
            ])
            ->setAllowedTypes('token', [SplitToken::class])
            ->setAllowedTypes('user_class', ['string'])
            ->setAllowedTypes('command_builder', [Closure::class])
            ->setAllowedTypes('password_constraints', ['array', Constraint::class])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'confirm_user_password_reset';
    }
}
