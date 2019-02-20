<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Test\Infrastructure\UserInterface\Web\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use function array_replace;
use function gettype;
use function is_scalar;
use function strtr;

class TransformationFailureListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => ['convertTransformationFailureToFormError', -1024],
        ];
    }

    public function convertTransformationFailureToFormError(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->getTransformationFailure() === null || ! $form->isValid()) {
            return;
        }

        foreach ($form as $child) {
            if (! $child->isSynchronized()) {
                return;
            }
        }

        $clientDataAsString = is_scalar($form->getViewData()) ? (string) $form->getViewData() : gettype($form->getViewData());
        $config             = $form->getConfig();

        $messageTemplate   = $config->getOption('invalid_message');
        $messageParameters = array_replace(['{{ value }}' => $clientDataAsString], $config->getOption('invalid_message_parameters'));
        $message           = strtr($messageTemplate, $messageParameters);

        $form->addError(new FormError($message, $messageTemplate, $messageParameters, null, $form->getTransformationFailure()));
    }
}
