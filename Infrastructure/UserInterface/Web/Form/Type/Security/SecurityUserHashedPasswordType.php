<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface as EncoderFactory;
use function Sodium\memzero;

final class SecurityUserHashedPasswordType extends AbstractType
{
    /** @var EncoderFactory */
    private $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['user_class'])
            ->setDefault('algorithm', function (Options $options) {
                $userClass = $options['user_class'];

                return function ($value) use ($userClass) {
                    $encoded = $this->encoderFactory->getEncoder($userClass)->encodePassword($value, '');

                    memzero($value);

                    return $encoded;
                };
            })
            ->setAllowedTypes('user_class', ['string']);
    }

    public function getParent(): string
    {
        return HashedPasswordType::class;
    }
}
