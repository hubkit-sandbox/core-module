<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type;

use Rollerworks\Bundle\MessageBusFormBundle\Type\MessageFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DefaultMessageBusExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        yield MessageFormType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('command_bus', static function (Options $options, $value) {
            return $value ?? 'park_manager.command_bus';
        });
        $resolver->setDefault('query_bus', static function (Options $options, $value) {
            return $value ?? 'park_manager.query_bus';
        });
    }
}
