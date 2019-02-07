<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type\Security;

use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\SecurityUserHashedPasswordType;
use RuntimeException;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @internal
 */
final class SecurityUserHashedPasswordTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    protected function setUp(): void
    {
        $encoder = new class() implements PasswordEncoderInterface {
            public function encodePassword($raw, $salt): string
            {
                return 'encoded(' . $raw . ')';
            }

            public function isPasswordValid($encoded, $raw, $salt): bool
            {
                return false;
            }
        };

        $this->encoderFactory = new class($encoder) implements EncoderFactoryInterface {
            private $encoder;

            public function __construct($encoder)
            {
                $this->encoder = $encoder;
            }

            public function getEncoder($user): PasswordEncoderInterface
            {
                if ($user !== ClientUser::class) {
                    throw new RuntimeException('Nope, that is not the right user.');
                }

                return $this->encoder;
            }
        };

        parent::setUp();
    }

    protected function getTypes(): array
    {
        return [
            new SecurityUserHashedPasswordType($this->encoderFactory),
        ];
    }

    /** @test */
    public function it_hashes_password(): void
    {
        $form = $this->factory->createBuilder()
            ->add('password', SecurityUserHashedPasswordType::class, ['user_class' => ClientUser::class])
            ->getForm();

        $form->submit([
            'password' => ['password' => 'Hello there'],
        ]);

        self::assertTrue($form->isValid());
        self::assertEquals(['password' => 'encoded(Hello there)'], $form->getData());
    }
}
