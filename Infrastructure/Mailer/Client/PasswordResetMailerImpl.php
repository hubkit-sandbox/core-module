<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Mailer\Client;

use DateTimeImmutable;
use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\PasswordResetMailer;
use ParkManager\Module\CoreModule\Application\Service\Mailer\Client\RecipientEnvelopeFactory;
use ParkManager\Module\CoreModule\Domain\Client\ClientId;
use ParkManager\Module\CoreModule\Infrastructure\Mailer\Sender\Sender;
use Rollerworks\Component\SplitToken\SplitToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as UrlGenerator;

final class PasswordResetMailerImpl implements PasswordResetMailer
{
    /** @var Sender */
    private $sender;

    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var RecipientEnvelopeFactory */
    private $envelopeFactory;

    public function __construct(Sender $sender, UrlGenerator $urlGenerator, RecipientEnvelopeFactory $envelopeFactory)
    {
        $this->sender          = $sender;
        $this->urlGenerator    = $urlGenerator;
        $this->envelopeFactory = $envelopeFactory;
    }

    public function send(ClientId $id, SplitToken $splitToken, DateTimeImmutable $tokenExpiration): void
    {
        $this->sender->send(
            '@ParkManagerCore/email/client/security/password_reset.twig',
            [
                'url' => $this->urlGenerator->generate(
                    'park_manager.client.security_confirm_password_reset',
                    ['token' => $splitToken->token()],
                    UrlGenerator::ABSOLUTE_URL
                ),
                'expiration_date' => $tokenExpiration,
            ],
            $this->envelopeFactory->create($id)
        );
    }
}
