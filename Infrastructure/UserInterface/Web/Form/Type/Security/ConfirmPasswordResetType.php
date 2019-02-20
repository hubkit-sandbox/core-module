<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security;

use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use Rollerworks\Bundle\MessageBusFormBundle\Type\MessageFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Validator\Constraint;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;

final class ConfirmPasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reset_token', SplitTokenType::class, ['invalid_message' => 'password_reset.invalid_token'])
            ->add('password', SecurityUserHashedPasswordType::class, [
                'required' => true,
                'password_confirm' => true,
                'password_constraints' => $options['password_constraints'],
                'user_class' => $options['user_class'],
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['token_invalid'] = false;

        foreach ($form->getErrors() as $error) {
            if ($error instanceof FormError && $error->getOrigin()->getName() === 'reset_token') {
                $view->vars['token_invalid'] = true;

                break;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['user_class'])
            ->setDefault('password_constraints', [])
            ->setDefault('exception_mapping', [
                PasswordResetTokenNotAccepted::class => static function (PasswordResetTokenNotAccepted $e, Translator $translator) {
                    if ($e->storedToken() === null) {
                        return new FormError($translator->trans('password_reset.no_token', [], 'validators'), 'password_reset.no_token', [], null, $e);
                    }

                    return new FormError($translator->trans('password_reset.invalid_token', [], 'validators'), 'password_reset.invalid_token', [], null, $e);
                },
                DisabledException::class => static function (DisabledException $e, Translator $translator) {
                    return new FormError($translator->trans('password_reset.access_disabled', [], 'validators'), null, [], null, $e);
                },
            ])
            ->setAllowedTypes('user_class', ['string'])
            ->setAllowedTypes('password_constraints', ['array', Constraint::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'confirm_user_password_reset';
    }

    public function getParent(): string
    {
        return MessageFormType::class;
    }
}
