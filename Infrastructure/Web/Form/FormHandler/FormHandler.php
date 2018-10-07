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

namespace ParkManager\Module\CoreModule\Infrastructure\Web\Form\FormHandler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * The FormHandler handles the submission of a Symfony Form to a
 * Park-Manager ServiceBus configuration.
 */
interface FormHandler
{
    /**
     * Maps a an exception class to a form (by property-path).
     *
     * Note: The property-path is about the form, 'profile.username'
     * not 'profile.children.username'.
     *
     * @param string   $exceptionClass Fully qualified exception class-name
     * @param callable $formatter      closure callback to produce one or more FormErrors,
     *                                 expected to return an array or {@link \Symfony\Component\Form\FormError}
     */
    public function mapException(string $exceptionClass, callable $formatter);

    /**
     * Handles all unmapped exceptions, either to accept multiple exception classes.
     *
     * Caution: This must not be used to log or collect exception messages!
     * When null or void is returned by the formatter the exception is re-thrown.
     *
     * @param callable $formatter closure callback to produce one or more FormErrors,
     *                            expected to return an array or {@link FormError}
     */
    public function setExceptionFallback(callable $formatter);

    /**
     * Get access to the internal Form instance (could be un-submitted).
     */
    public function getForm(): FormInterface;

    /**
     * Handle the Request for the form, and handles Command if valid.
     *
     * If the form is submitted and valid, this handles command execution.
     *
     * * Checks Form validity before handling (submitted and valid)
     * * Exceptions are mapped to Forms (if possible)
     * * Command handling result is returned (if any).
     *
     * @param mixed $request
     *
     * @return mixed The result returned by the Command execution (if any)
     */
    public function handleRequest($request);

    /**
     * Alias for getForm()->createView().
     */
    public function createView(): FormView;

    /**
     * Returns whether the handling process was successful.
     *
     * - Form is submitted
     * - Form is valid
     * - Command was handled (without errors)
     */
    public function isReady(): bool;
}
