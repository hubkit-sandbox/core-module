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

namespace ParkManager\Module\CoreModule\Infrastructure\Mailer;

use DateTimeImmutable;
use ParkManager\Component\Mailer\Sender;
use ParkManager\Module\CoreModule\Application\Service\Mailer\ClientPasswordResetMailer;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Domain\Client\ClientRepository;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ClientPasswordResetSwiftMailer implements ClientPasswordResetMailer
{
    /** @var ClientRepository */
    private $repository;

    /** @var Sender */
    private $sender;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(ClientRepository $repository, Sender $sender, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository   = $repository;
        $this->sender       = $sender;
        $this->urlGenerator = $urlGenerator;
    }

    public function send(ClientId $id, SplitToken $splitToken, DateTimeImmutable $tokenExpiration): void
    {
        $client = $this->repository->get($id);
        $emailAddress = $client->email();

        $this->sender->send(
            '@ParkManager/email/client/security/password_reset.twig',
            [$emailAddress->address() => $emailAddress->name()],
            ['url' => $this->getConfirmUrl($splitToken), 'expiration_date' => $tokenExpiration]
        );
    }

    private function getConfirmUrl(SplitToken $splitToken): string
    {
        return $this->urlGenerator->generate(
            'park_manager.client.security_confirm_password_reset',
            ['token' => $splitToken->token()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
