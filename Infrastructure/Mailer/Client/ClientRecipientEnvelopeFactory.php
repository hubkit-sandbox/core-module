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

namespace ParkManager\Module\CoreModule\Infrastructure\Mailer\Client;

use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\RecipientEnvelopeFactory;
use ParkManager\Module\CoreModule\Application\Service\Mailer\RecipientEnvelope;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

final class ClientRecipientEnvelopeFactory implements RecipientEnvelopeFactory
{
    /** @var ClientRepository */
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(ClientId $id): RecipientEnvelope
    {
        return new RecipientEnvelope($this->repository->get($id)->email());
    }

    public function createWith(ClientId $id, EmailAddress $email): RecipientEnvelope
    {
        return new RecipientEnvelope($email);
    }
}
