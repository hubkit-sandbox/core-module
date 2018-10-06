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

namespace ParkManager\Module\CoreModule\Domain\Shared;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ParkManager\Module\CoreModule\Domain\EventsRecordingEntity;
use ParkManager\Module\CoreModule\Domain\Shared\Event\PasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\User\Event\UserPasswordWasChanged;

/**
 * AbstractUser is a marker abstract-class used for the Administrator and User Model.
 *
 * DO NOT EXTEND FROM THIS CLASS DIRECTLY, extend from the Administrator
 * or User Model instead. Only use this class for generic type-hinting.
 */
abstract class AbstractUser extends EventsRecordingEntity
{
    public const DEFAULT_ROLE = 'ROLE_USER';

    /** @var AbstractUserId */
    protected $id;

    /** @var EmailAddress */
    protected $email;

    /** @var bool */
    protected $enabled = true;

    /** @var string|null */
    protected $password;

    /** @var Collection */
    protected $roles;

    /** @var SplitTokenValueHolder|null */
    protected $emailAddressChangeToken;

    /** @var SplitTokenValueHolder|null */
    protected $passwordResetToken;

    protected function __construct(AbstractUserId $id, EmailAddress $email)
    {
        $this->id    = $id;
        $this->email = $email;
        $this->roles = new ArrayCollection(static::getDefaultRoles());
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

    /**
     * Returns the hashed password.
     *
     * When empty a different authentication type is assumed.
     */
    public function password(): ?string
    {
        return $this->password;
    }

    /**
     * Change the user's password.
     *
     * Pass null When another authentication system is used.
     */
    public function changePassword(?string $password): void
    {
        if ($password !== null) {
            Assertion::notEmpty($password, 'Password can only null or a non-empty string.');
        }

        if ($this->password !== $password) {
            $this->password = $password;

            $this->recordThat(new UserPasswordWasChanged($this->id()));
        }
    }

    /**
     * Returns whether access are enabled (is allowed to login).
     */
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

    /**
     * Returns the roles granted to the user.
     *
     * @return string[]
     */
    public function roles(): array
    {
        return $this->roles->toArray();
    }

    public function addRole(string $role): void
    {
        if (! $this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains($role);
    }

    public function removeRole(string $role): void
    {
        Assertion::notInArray($role, self::getDefaultRoles(), 'Cannot remove default role "' . $role . '".');

        $this->roles->removeElement($role);
    }

    /**
     * Set the confirmation of e-mail address change information.
     *
     *
     * @return bool Returns false when a not expired confirmation-token was already set (for this address)
     *              true when the token was accepted and set
     */
    public function setConfirmationOfEmailAddressChange(EmailAddress $email, SplitTokenValueHolder $token): bool
    {
        if (! SplitTokenValueHolder::isEmpty($this->emailAddressChangeToken) &&
            ! $this->emailAddressChangeToken->isExpired() &&
            $this->emailAddressChangeToken->metadata()['email'] === $email->address()
        ) {
            return false;
        }

        $this->emailAddressChangeToken = $token->withMetadata(['email' => $email->address()]);

        return true;
    }

    /**
     * Tries to confirm the change of the e-mail address.
     *
     * When the confirmation was successful this should update the e-mail address
     * of the user with the e-mail address stored by the request.
     *
     * Note: When the token doesn't match, remove it. Do not allow even a second chance.
     *
     *
     * @return bool Returns true when the confirmation was accepted, false otherwise (token invalid/expired)
     */
    public function confirmEmailAddressChange(SplitToken $token): bool
    {
        if (SplitTokenValueHolder::isEmpty($this->emailAddressChangeToken)) {
            return false;
        }

        try {
            if ($token->matches($this->emailAddressChangeToken, $this->id()->toString())) {
                $this->changeEmail(new EmailAddress($this->emailAddressChangeToken->metadata()['email']));

                return true;
            }

            return false;
        } finally {
            $this->emailAddressChangeToken = null;
        }
    }

    /**
     * Sets the password reset token (for confirmation).
     *
     *
     * @return bool false when a confirmation-token was already set _and_ not expired,
     *              or when password resetting was disabled for this user.
     *              True when the token was accepted and set
     */
    public function setPasswordResetToken(SplitTokenValueHolder $token): bool
    {
        if (! SplitTokenValueHolder::isEmpty($this->passwordResetToken) && ! $this->passwordResetToken->isExpired()) {
            return false;
        }

        $this->passwordResetToken = $token;
        $this->recordThat(new PasswordResetWasRequested($this->id, $token->getToken()));

        return true;
    }

    /**
     * Tries to confirm password resetting.
     *
     * When the confirmation was successful this should update the password of the user.
     * When the user is disabled this should still return true and continue.
     *
     * Note: When the token doesn't match, remove it. Do not allow even a second chance.
     *
     *
     * @return bool Returns true when the reset was accepted, false otherwise (token invalid/expired)
     */
    public function confirmPasswordReset(SplitToken $token, string $passwordHash): bool
    {
        if (SplitTokenValueHolder::isEmpty($this->passwordResetToken)) {
            return false;
        }

        try {
            if ($token->matches($this->passwordResetToken, $this->id()->toString())) {
                $this->changePassword($passwordHash);

                return true;
            }

            return false;
        } finally {
            $this->passwordResetToken = null;
        }
    }

    public function passwordResetToken(): ?SplitTokenValueHolder
    {
        if (SplitTokenValueHolder::isEmpty($this->passwordResetToken) || $this->passwordResetToken->isExpired()) {
            return null;
        }

        return $this->passwordResetToken;
    }

    protected static function getDefaultRoles(): array
    {
        return [self::DEFAULT_ROLE];
    }
}
