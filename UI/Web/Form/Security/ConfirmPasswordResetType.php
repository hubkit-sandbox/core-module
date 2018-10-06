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

use ParkManager\Module\CoreModule\Application\Service\Crypto\Argon2SplitTokenFactory;
use ParkManager\Module\CoreModule\Application\Command\Security\ConfirmUserPasswordReset;
use ParkManager\Module\CoreModule\Application\Service\Crypto\SplitTokenFactory;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\UserLoginIsDisabled;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use function iterator_to_array;

class ConfirmPasswordResetType extends AbstractType implements DataMapperInterface
{
    private $splitTokenFactory;

    public function __construct(?SplitTokenFactory $splitTokenFactory = null)
    {
        $this->splitTokenFactory = $splitTokenFactory ?? new Argon2SplitTokenFactory();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setDataMapper($this)
            ->add('reset_token', HiddenType::class, ['data' => $options['token']->token()->getString()])
            ->add('password', HashedPasswordType::class, [
                'required' => true,
                'password_confirm' => true,
                'password_options' => [
                    'constraints' => $options['password_constraints'],
                ],
                'algorithm' => function (string $value) {
                    return 'encoded(' . $value . ')';
                }, // FIXME This needs an actual service
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('token')
            ->setDefault('password_constraints', [])
            ->setAllowedTypes('token', [SplitToken::class])
            ->setAllowedTypes('password_constraints', ['array', Constraint::class])
            ->setDefault('exception_mapping', [
                PasswordResetTokenNotAccepted::class => function (PasswordResetTokenNotAccepted $e) {
                    return new FormError('password_reset.invalid_token', null, [], null, $e);
                },
                UserLoginIsDisabled::class => function (UserLoginIsDisabled $e) {
                    return new FormError('password_reset.access_disabled', null, [], null, $e);
                },
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'confirm_user_password_reset';
    }

    public function mapDataToForms($data, $forms)
    {
        // No-op
    }

    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);
        /** @var FormInterface[] $forms */
        try {
            $token = (string) $forms['reset_token']->getData();
            $token = ($this->splitTokenFactory)->fromString($token);
        } catch (\RuntimeException $e) {
            throw new TransformationFailedException('Invalid token', 0, $e);
        }

        $data = new ConfirmUserPasswordReset($token, (string) $forms['password']->getData());
    }
}
