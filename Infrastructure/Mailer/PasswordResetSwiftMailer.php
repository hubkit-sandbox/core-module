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
use ParkManager\Module\CoreModule\Application\Service\PasswordResetMailer;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PasswordResetSwiftMailer implements PasswordResetMailer
{
    private $sender;
    private $urlGenerator;
    private $route;

    public function __construct(Sender $sender, UrlGeneratorInterface $urlGenerator, string $route)
    {
        $this->sender       = $sender;
        $this->urlGenerator = $urlGenerator;
        $this->route        = $route;
    }

    public function send(EmailAddress $emailAddress, SplitToken $splitToken, \DateTimeImmutable $tokenExpiration): void
    {
        $this->sender->send(
            '@ParkManagerCore\email\security\password_reset.twig',
            [$emailAddress->address() => $emailAddress->name()],
            ['url' => $this->getConfirmUrl($splitToken), 'expiration_date' => $tokenExpiration]
        );
    }

    private function getConfirmUrl(SplitToken $splitToken): string
    {
        return $this->urlGenerator->generate($this->route, ['token' => $splitToken->token()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
