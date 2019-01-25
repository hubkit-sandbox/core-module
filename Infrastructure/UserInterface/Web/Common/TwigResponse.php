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

namespace ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common;

use InvalidArgumentException;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\Form\Handler\FormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use function is_array;
use function sprintf;

class TwigResponse extends Response
{
    private $template;
    private $variables;

    /**
     * @param array|Form|FormHandler $variables A Form or FormHandler object is passed as [form => createView()]
     */
    public function __construct(string $template, $variables = [], int $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->setTemplateVariables($variables);

        $this->template = $template;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param array|Form|FormHandler $variables A Form or FormHandler object is passed as [form => createView()]
     */
    public function setTemplateVariables($variables): void
    {
        if (! is_array($variables)) {
            if (! ($variables instanceof Form) && ! ($variables instanceof FormHandler)) {
                throw new InvalidArgumentException(sprintf('TwigResponse $variables expects an array, %s or %s object.', Form::class, FormHandler::class));
            }

            $variables = ['form' => $variables->createView()];
        }

        $this->variables = $variables;
    }

    public function getTemplateVariables(): array
    {
        return $this->variables;
    }
}
