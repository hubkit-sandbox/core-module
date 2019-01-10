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

namespace ParkManager\Module\CoreModule\Infrastructure\Mailer\Sender;

use ParkManager\Module\CoreModule\Application\Service\Mailer\RecipientEnvelope;

final class NullSender implements Sender
{
    public function send(string $template, array $variables, RecipientEnvelope ...$recipients): void
    {
    }

    public function sendWithAttachments(string $template, array $variables, array $attachments, RecipientEnvelope ...$recipients): void
    {
    }
}
