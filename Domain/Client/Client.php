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

namespace ParkManager\Module\CoreModule\Domain\Client;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientEmailAddressChangeWasRequested;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientNameWasChanged;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientPasswordWasChanged;
use ParkManager\Module\CoreModule\Domain\Client\Event\ClientWasRegistered;
use ParkManager\Module\CoreModule\Domain\Client\Exception\EmailChangeConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\Client\Exception\PasswordResetConfirmationRejected;
use ParkManager\Module\CoreModule\Domain\DomainEventsCollectionTrait;
use ParkManager\Module\CoreModule\Domain\RecordsDomainEvents;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use ParkManager\Module\CoreModule\Domain\Shared\SplitToken;
use ParkManager\Module\CoreModule\Domain\Shared\SplitTokenValueHolder;

class Client implements RecordsDomainEvents
{
    use DomainEventsCollectionTrait;

    public const DEFAULT_ROLES = ['ROLE_USER'];

    /** @var ClientId */
    protected $id;

    /** @var EmailAddress */
    protected $email;

    /** @var string */
    protected $displayName;

    /** @var bool */
    protected $enabled = true;

    /** @var Collection */
    protected $roles;

    /** @var SplitTokenValueHolder|null */
    protected $emailChangeToken;

    /** @var string|null */
    protected $password;

    /** @var bool */
    protected $passwordResetEnabled = true;

    /** @var SplitTokenValueHolder|null */
    protected $passwordResetToken;

    protected function __construct(ClientId $id, EmailAddress $email, string $displayName)
    {
        $this->id          = $id;
        $this->email       = $email;
        $this->displayName = $displayName;
        $this->roles       = new ArrayCollection(static::DEFAULT_ROLES);
    }

    public static function register(ClientId $id, EmailAddress $email, string $displayName, ?string $password = null): self
    {
        $client = new static($id, $email, $displayName);
        $client->recordThat(new ClientWasRegistered($id, $email, $displayName));
        $client->changePassword($password);

        return $client;
    }

    public function id()
    {
        return $this->id;
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function changeEmail(EmailAddress $email): void
    {
        $this->email = $email;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function requestEmailChange(EmailAddress $email, SplitToken $token): bool
    {
        if (! SplitTokenValueHolder::mayReplaceCurrentToken($this->emailChangeToken, ['email' => $email->address()])) {
            return false;
        }

        $this->emailChangeToken = $token->toValueHolder()->withMetadata(['email' => $email->address()]);
        $this->recordThat(new ClientEmailAddressChangeWasRequested($this->id, $token, $email));

        return true;
    }

    public function confirmEmailChange(SplitToken $token): void
    {
        try {
            if (! $token->matches($this->emailChangeToken)) {
                throw new EmailChangeConfirmationRejected();
            }

            $this->changeEmail(new EmailAddress($this->emailChangeToken->metadata()['email']));
        } finally {
            $this->emailChangeToken = null;
        }
    }

    public function changeName(string $displayName): void
    {
        if ($this->displayName !== $displayName) {
            $this->recordThat(new ClientNameWasChanged($this->id, $displayName));
            $this->displayName = $displayName;
        }
    }

    /**
     * Pass null When another authentication system is used.
     */
    public function changePassword(?string $password): void
    {
        if ($password !== null) {
            Assertion::notEmpty($password, 'Password can only null or a non-empty string.');
        }

        if ($this->password !== $password) {
            $this->password = $password;

            $this->recordThat(new ClientPasswordWasChanged($this->id, $password));
        }
    }

    /**
     * @return bool false when a token was already set _and_ not expired,
     *              or when password resetting was disabled for this client.
     *              True when the token was accepted and set
     */
    public function requestPasswordReset(SplitToken $token): bool
    {
        if (! $this->passwordResetEnabled) {
            return false;
        }

        if (! SplitTokenValueHolder::mayReplaceCurrentToken($this->passwordResetToken)) {
            return false;
        }

        $this->passwordResetToken = $token->toValueHolder();
        $this->recordThat(new ClientPasswordResetWasRequested($this->id, $token));

        return true;
    }

    public function confirmPasswordReset(SplitToken $token, string $newPassword): void
    {
        if (! $this->passwordResetEnabled) {
            return;
        }

        try {
            if (! $token->matches($this->passwordResetToken)) {
                throw new PasswordResetConfirmationRejected();
            }

            $this->changePassword($newPassword);
        } finally {
            $this->clearPasswordReset();
        }
    }

    public function clearPasswordReset(): void
    {
        $this->passwordResetToken = null;
    }

    public function disablePasswordReset(): void
    {
        $this->passwordResetEnabled = false;
        $this->passwordResetToken   = null;
    }

    public function enablePasswordReset(): void
    {
        $this->passwordResetEnabled = true;
    }

    public function passwordResetToken(): ?SplitTokenValueHolder
    {
        return $this->passwordResetToken;
    }
}
