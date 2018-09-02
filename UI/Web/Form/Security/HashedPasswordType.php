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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HashedPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordOptions = $options['password_options'] + ['required' => $options['required']];
        $passwordOptions['attr'] = array_merge(
            $passwordOptions['attr'] ?? [],
            [
                'autocomplete' => 'off',
                'autocorrect' => 'off',
                'autocapitalize' => 'off',
                'spellcheck' => 'false',
            ]
        );

        if ($options['password_confirm']) {
            $builder->add('password', RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'password_not_the_same',
                    'first_options' => ['label' => 'label.password'],
                    'second_options' => ['label' => 'label.password2'],
                ] + $passwordOptions
            );
        } else {
            $builder->add('password', PasswordType::class, $passwordOptions);
        }

        $encoder = $options['algorithm'];
        $builder->get('password')->addModelTransformer(
            new CallbackTransformer(
                // Password is always null (as by convention)
                function () {
                    return null;
                },
                function ($value) use ($encoder): ?string {
                    if (null === $value) {
                        return null;
                    }

                    if (!\is_string($value)) {
                        throw new TransformationFailedException('Expected string got "'.\gettype($value).'"');
                    }

                    $encodePassword = $encoder($value);
                    sodium_memzero($value);

                    return $encodePassword;
                }
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['algorithm']);
        $resolver->setDefaults([
            'inherit_data' => true,
            'password_options' => [],
            'password_confirm' => false,
        ]);

        $resolver->setAllowedTypes('algorithm', 'callable');
        $resolver->setAllowedTypes('password_options', ['array']);
        $resolver->setAllowedTypes('password_confirm', ['bool']);
    }
}
