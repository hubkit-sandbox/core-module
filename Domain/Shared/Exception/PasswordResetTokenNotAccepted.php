<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain\Shared\Exception;

use DomainException;
use Rollerworks\Component\SplitToken\SplitToken;
use Rollerworks\Component\SplitToken\SplitTokenValueHolder;

/**
 * PasswordResetTokenNotAccepted is thrown as a generic exception
 * when the Password token was not accepted.
 *
 * Do not disclose specific details as these could be abused!
 */
final class PasswordResetTokenNotAccepted extends DomainException
{
    private $storedToken;
    private $providedToken;

    public function __construct(?SplitTokenValueHolder $storedToken = null, ?SplitToken $providedToken = null)
    {
        parent::__construct('PasswordReset is invalid (expired, no result or verifier mismatch).');
        $this->storedToken   = $storedToken;
        $this->providedToken = $providedToken;
    }

    public function storedToken(): ?SplitTokenValueHolder
    {
        return $this->storedToken;
    }

    public function providedToken(): ?SplitToken
    {
        return $this->providedToken;
    }
}
