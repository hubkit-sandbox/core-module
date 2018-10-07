<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Web;

use ParkManager\Module\CoreModule\Infrastructure\Web\Form\FormHandler\FormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use function is_array;
use function sprintf;

class TwigResponse extends Response
{
    private $template;
    private $variables;

    /**
     * @var Environment|null
     */
    private $twig;

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
                throw new \InvalidArgumentException(sprintf('TwigResponse $variables expects an array, %s or %s object.', Form::class, FormHandler::class));
            }

            $variables = ['form' => $variables->createView()];
        }

        $this->variables = $variables;
    }

    public function getTemplateVariables(): array
    {
        return $this->variables;
    }

    public function setRenderer(Environment $twig): void
    {
        $this->twig = $twig;
    }

    public function sendContent()
    {
        if ($this->content !== '') {
            echo $this->content;

            return $this;
        }

        if ($this->twig === null) {
            throw new \RuntimeException(sprintf('No Twig renderer set for response with template "%s".', $this->template));
        }

        echo $this->twig->render($this->template, $this->variables);

        return $this;
    }
}
