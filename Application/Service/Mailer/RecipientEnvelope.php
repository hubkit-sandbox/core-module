<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Service\Mailer;

use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;

// Return the Slender
final class RecipientEnvelope
{
    public const ENCRYPTION_GPG = 'gpg';

    public const ENCRYPTION_SMIME = 'smime';

    /** @var EmailAddress */
    private $address;

    /** @var string|null */
    private $locale;

    /** @var string|null */
    private $encryptionKey;

    /** @var string|null */
    private $encryptionType;

    /** @var int */
    private $priority = 3;

    /** @var EmailAddress|null */
    private $readConfirmation;

    public function __construct(EmailAddress $address)
    {
        $this->address = $address;
    }

    public function withLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function withMessageEncryption(string $publicKey, string $type): self
    {
        $this->encryptionKey  = $publicKey;
        $this->encryptionType = $type;

        return $this;
    }

    public function withNoMessageEncryption(): self
    {
        $this->encryptionKey  = null;
        $this->encryptionType = null;

        return $this;
    }

    public function withPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function withReadConfirmationTo(?EmailAddress $address): self
    {
        $this->readConfirmation = $address;

        return $this;
    }

    public function getAddress(): EmailAddress
    {
        return $this->address;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }

    public function getEncryptionType(): string
    {
        return $this->encryptionType;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getReadConfirmation(): ?EmailAddress
    {
        return $this->readConfirmation;
    }
}
