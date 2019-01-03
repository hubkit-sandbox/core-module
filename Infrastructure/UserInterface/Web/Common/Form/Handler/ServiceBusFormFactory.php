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

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\Form\Handler;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\MessageBusInterface as MessageBus;

final class ServiceBusFormFactory
{
    private $formFactory;
    private $queryBus;
    private $commandBus;
    private $commandValidator;

    public function __construct(FormFactoryInterface $formFactory, MessageBus $queryBus, MessageBus $commandBus, ?callable $commandValidator = null)
    {
        $this->formFactory      = $formFactory;
        $this->queryBus         = $queryBus;
        $this->commandBus       = $commandBus;
        $this->commandValidator = $commandValidator;
    }

    /**
     * Creates a new FormHandler for handling a Command.
     *
     * The Command must be provided as the Form data.
     * Use {@link \Symfony\Component\Form\FormEvents::PRE_SET_DATA} to convert
     * the initial data to a correct Command object.
     *
     * @param mixed $data The initial form-data (or a Command object)
     */
    public function createForCommand(string $formType, $data, array $formOptions = []): FormHandler
    {
        $form    = $this->formFactory->create($formType, $data, $formOptions);
        $handler = new CommandBusFormHandler($form, $this->commandBus, $this->commandValidator);

        $this->configureMappingByForm($form, $handler);

        return $handler;
    }

    /**
     * Creates a new FormHandler for handling a Query, to allow modifying existing data.
     *
     * The QueryBus first handles the Query, and then passes the returned
     * data to the Form as initial-data. The Form must transform this form-data
     * a Command.
     *
     * Use {@link \Symfony\Component\Form\FormEvents::PRE_SET_DATA} to convert
     * the initial data to a correct Command object.
     *
     * @param object $query The Query message object
     */
    public function createForQuery(string $formType, object $query, array $formOptions = []): FormHandler
    {
        $form    = $this->formFactory->create($formType, $this->queryBus->dispatch($query), $formOptions);
        $handler = new CommandBusFormHandler($form, $this->commandBus, $this->commandValidator);

        $this->configureMappingByForm($form, $handler);

        return $handler;
    }

    private function configureMappingByForm(FormInterface $form, FormHandler $handler): void
    {
        foreach ($form->getConfig()->getOption('exception_mapping', []) as $exceptionClass => $formatter) {
            if ($exceptionClass === '*') {
                $handler->setExceptionFallback($formatter);
            } else {
                $handler->mapException($exceptionClass, $formatter);
            }
        }
    }
}
