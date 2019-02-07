<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Domain\Shared;

use ParkManager\Module\CoreModule\Domain\Shared\Exception\MalformedEmailAddress;
use const IDNA_DEFAULT;
use const INTL_IDNA_VARIANT_UTS46;
use const MB_CASE_LOWER;
use function idn_to_utf8;
use function mb_convert_case;
use function mb_strlen;
use function mb_strrpos;
use function mb_substr;
use function sprintf;

final class EmailAddress
{
    private $address;
    private $canonical;
    private $name;

    private $label = '';

    public function __construct(string $address, ?string $name = null)
    {
        $this->address   = $address;
        $this->canonical = $this->canonicalize($address, $this->label);
        $this->name      = $name;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function toString(): string
    {
        return $this->address;
    }

    public function __toString(): string
    {
        return $this->address;
    }

    public function canonical(): string
    {
        return $this->canonical;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    private function canonicalize(string $address, string &$label): string
    {
        $atPos = mb_strrpos($address, '@', 0, 'UTF-8');

        if ($atPos === false) {
            throw new MalformedEmailAddress(sprintf('Malformed e-mail address "%s" (missing @)', $address));
        }

        // The label is only used for information, but still points to the same
        // inbox. Keeping this would make it possible to reuse the same address
        // for the same user, leading to all kinds of trouble.
        $local = mb_substr($address, 0, $atPos, 'UTF-8');
        $local = $this->extractLabel($local, $label);

        $domain = mb_substr($address, $atPos + 1);
        $domain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46, $idnaInfo);

        if ($idnaInfo['errors'] !== 0) {
            throw new MalformedEmailAddress(sprintf('Malformed e-mail address "%s" (IDN Error reported %s)', $address, $idnaInfo['errors']));
        }

        // While not officially required (as the local part is case-sensitive) it's generally
        // better to lowercase the local part also to prevent spoofing and typo's
        // (and nobody uses case-sensitive addresses ¯\_(ツ)_/¯ )

        return mb_convert_case($local . '@' . $domain, MB_CASE_LOWER, 'UTF-8');
    }

    private function extractLabel(string $local, string &$label): string
    {
        $labelPos = mb_strrpos($local, '+', 0, 'UTF-8');

        if ($labelPos !== false) {
            $label = mb_substr($local, ++$labelPos, $labelEnd = mb_strlen($local, 'UTF-8') - $labelPos, 'UTF-8');
            $local = mb_substr($local, 0, $labelEnd - 1, 'UTF-8');
        }

        return $local;
    }
}
