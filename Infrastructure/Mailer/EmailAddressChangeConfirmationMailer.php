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

use ParkManager\Component\Mailer\Sender;
use ParkManager\Component\Security\Token\SplitToken;
use ParkManager\Module\CoreModule\Application\Service\EmailAddressChangeConfirmationMailer as EmailAddressChangeConfirmationMailerBase;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EmailAddressChangeConfirmationMailer implements EmailAddressChangeConfirmationMailerBase
{
    private $sender;
    private $urlGenerator;
    private $confirmChangeRoute;

    public function __construct(Sender $mailer, UrlGeneratorInterface $urlGenerator, string $route)
    {
        $this->sender             = $mailer;
        $this->urlGenerator       = $urlGenerator;
        $this->confirmChangeRoute = $route;
    }

    public function send(EmailAddress $emailAddress, SplitToken $splitToken, \DateTimeImmutable $tokenExpiration): void
    {
        $this->sender->send(
            '@ParkManagerCore\email\confirm_email_address_change.twig',
            [$emailAddress->address() => $emailAddress->name()],
            ['url' => $this->getConfirmUrl($splitToken), 'expiration_date' => $tokenExpiration]
        );
    }

    private function getConfirmUrl(SplitToken $splitToken): string
    {
        return $this->urlGenerator->generate($this->confirmChangeRoute, ['token' => $splitToken->token()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
