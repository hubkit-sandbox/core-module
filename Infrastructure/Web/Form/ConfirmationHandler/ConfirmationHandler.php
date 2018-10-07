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

namespace ParkManager\Module\CoreModule\Infrastructure\Web\Form\ConfirmationHandler;

/**
 * The ConfirmationHandler helps with safely handling the confirmation
 * of a specific action (mainly ensuring a CSRF token was used).
 */
final class ConfirmationHandler extends BaseConfirmationHandler
{
    /**
     * Configure the confirmation handler for usage.
     *
     * @param string $title          The title of the confirmation (eg. "park_manager.module.action.confirm.title")
     * @param string $message        The message of the confirmation (eg. "park_manager.module.action.confirm.body")
     * @param string $yesButtonLabel The label of the confirmation button (should contain a clear indication about what
     *                               will happen "Remove user")
     *
     * @return ConfirmationHandler
     */
    public function configure(string $title, string $message, string $yesButtonLabel): self
    {
        $this->templateContext['title']         = $title;
        $this->templateContext['message']       = $message;
        $this->templateContext['yes_btn_label'] = $yesButtonLabel;

        return $this;
    }

    /**
     * Returns whether the action was confirmed (and has a valid token).
     */
    public function isConfirmed(): bool
    {
        $this->guardNeedsRequest();

        if (! $this->request->isMethod('POST') || ! $this->checkToken()) {
            return false;
        }

        return true;
    }
}
