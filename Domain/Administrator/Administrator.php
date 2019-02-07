<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain\Administrator;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorNameWasChanged;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorPasswordResetWasRequested;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorPasswordWasChanged;
use ParkManager\Module\CoreModule\Domain\Administrator\Event\AdministratorWasRegistered;
use ParkManager\Module\CoreModule\Domain\Administrator\Exception\CannotDisableSuperAdministrator;
use ParkManager\Module\CoreModule\Domain\DomainEventsCollectionTrait;
use ParkManager\Module\CoreModule\Domain\RecordsDomainEvents;
use ParkManager\Module\CoreModule\Domain\Shared\EmailAddress;
use Rollerworks\Component\SplitToken\SplitToken;
use Rollerworks\Component\SplitToken\SplitTokenValueHolder;

/**
 * @final
 */
class Administrator implements RecordsDomainEvents
{
    use DomainEventsCollectionTrait;

    /** @var AdministratorId */
    private $id;

    /** @var EmailAddress */
    private $email;

    /** @var string */
    private $displayName;

    /** @var bool */
    private $loginEnabled = true;

    /** @var Collection */
    private $roles;

    /** @var string|null */
    private $password;

    /** @var SplitTokenValueHolder|null */
    private $passwordResetToken;

    public const DEFAULT_ROLES = ['ROLE_ADMIN'];

    private function __construct(AdministratorId $id, EmailAddress $email, string $displayName)
    {
        $this->id          = $id;
        $this->email       = $email;
        $this->roles       = new ArrayCollection(self::DEFAULT_ROLES);
        $this->displayName = $displayName;
    }

    public static function register(AdministratorId $id, EmailAddress $email, string $displayName, ?string $password = null): self
    {
        $user = new self($id, $email, $displayName);
        $user->recordThat(new AdministratorWasRegistered($id, $email, $displayName));
        $user->changePassword($password);

        return $user;
    }

    public function getId(): AdministratorId
    {
        return $this->id;
    }

    public function getEmailAddress(): EmailAddress
    {
        return $this->email;
    }

    public function changeEmail(EmailAddress $email): void
    {
        $this->email = $email;
    }

    public function changeName(string $displayName): void
    {
        if ($this->displayName !== $displayName) {
            $this->recordThat(new AdministratorNameWasChanged($this->id, $displayName));
            $this->displayName = $displayName;
        }
    }

    public function isLoginEnabled(): bool
    {
        return $this->loginEnabled;
    }

    public function disableLogin(): void
    {
        if ($this->hasRole('ROLE_SUPER_ADMIN')) {
            throw new CannotDisableSuperAdministrator($this->id);
        }

        $this->loginEnabled = false;
    }

    public function enableLogin(): void
    {
        $this->loginEnabled = true;
    }

    /**
     * @return string[]
     */
    public function getRoles(): iterable
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
        Assertion::notInArray($role, self::DEFAULT_ROLES, 'Cannot remove default role "' . $role . '".');

        $this->roles->removeElement($role);
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

            $this->recordThat(new AdministratorPasswordWasChanged($this->id, $password));
        }
    }

    public function requestPasswordReset(SplitToken $token): bool
    {
        $this->passwordResetToken = $token->toValueHolder();
        $this->recordThat(new AdministratorPasswordResetWasRequested($this->id, $token));

        return true;
    }

    /**
     * Tries to confirm password resetting.
     *
     * Note: Make sure to always store the Entity after calling this method.
     * Even if this method returned false.
     *
     * When the confirmation was successful this updates the password of the user.
     * When the user is disabled this still returns true and continues.
     * When the token doesn't match, it's removed. We do not allow a second chance.
     *
     * @return bool Returns true when the reset was accepted, false otherwise (token invalid/expired)
     */
    public function confirmPasswordReset(SplitToken $token, string $newPassword): bool
    {
        if (SplitTokenValueHolder::isEmpty($this->passwordResetToken)) {
            $this->passwordResetToken = null;

            return false;
        }

        try {
            if ($token->matches($this->passwordResetToken)) {
                $this->changePassword($newPassword);

                return true;
            }

            return false;
        } finally {
            $this->passwordResetToken = null;
        }
    }

    public function getPasswordResetToken(): ?SplitTokenValueHolder
    {
        return $this->passwordResetToken;
    }

    public function clearPasswordReset(): void
    {
        $this->passwordResetToken = null;
    }
}
