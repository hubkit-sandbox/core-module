<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type\Mocks;

use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use RuntimeException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/** @internal */
final class FakePasswordHashFactory implements EncoderFactoryInterface
{
    /** @var PasswordEncoderInterface */
    private $encoder;

    /** @var string */
    private $userClass;

    public function __construct(string $userClass = ClientUser::class)
    {
        $this->userClass = $userClass;
        $this->encoder   = new class() implements PasswordEncoderInterface {
            public function encodePassword($raw, $salt): string
            {
                return 'encoded(' . $raw . ')';
            }

            public function isPasswordValid($encoded, $raw, $salt): bool
            {
                return false;
            }
        };
    }

    public function getEncoder($user): PasswordEncoderInterface
    {
        if ($user !== $this->userClass) {
            throw new RuntimeException('Nope, that is not the right user.');
        }

        return $this->encoder;
    }
}
