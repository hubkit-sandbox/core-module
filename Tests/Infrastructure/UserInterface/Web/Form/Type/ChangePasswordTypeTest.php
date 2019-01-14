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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type;

use Closure;
use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\ChangePasswordType;
use RuntimeException;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * @internal
 */
final class ChangePasswordTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    protected function getExtensions(): array
    {
        return [
            $this->getValidatorExtension(),
        ];
    }

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
            new ChangePasswordType($this->encoderFactory),
        ];
    }

    /** @test */
    public function it_hashes_password(): void
    {
        $form  = $this->factory->create(ChangePasswordType::class, null, [
            'user_class' => ClientUser::class,
            'command_builder' => $this->getCommandBuilder(),
            'user_id' => 1,
        ]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']],
        ]);

        self::assertTrue($form->isValid());
        self::assertEquals(new ChangeUserPassword('1', 'encoded(Hello there)'), $form->getData());
    }

    /** @test */
    public function it_does_not_change_user_id(): void
    {
        $form  = $this->factory->create(ChangePasswordType::class, null, [
            'user_class' => ClientUser::class,
            'command_builder' => $this->getCommandBuilder(),
            'user_id' => 1,
        ]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there'], 'user_id' => '2'],
        ]);

        self::assertTrue($form->isValid());
        self::assertEquals(new ChangeUserPassword('1', 'encoded(Hello there)'), $form->getData());
    }

    /** @test */
    public function it_does_not_accept_invalid_input(): void
    {
        $form  = $this->factory->create(ChangePasswordType::class, null, [
            'user_class' => ClientUser::class,
            'command_builder' => $this->getCommandBuilder(),
            'user_id' => 1,
        ]);
        $form->submit(['password' => 'Hello there']);

        self::assertEquals(new ChangeUserPassword('1', ''), $form->getData());
    }

    /** @test */
    public function it_gives_null_for_model_password(): void
    {
        $form = $this->factory->create(ChangePasswordType::class, null, [
            'user_class' => ClientUser::class,
            'command_builder' => $this->getCommandBuilder(),
            'user_id' => 1,
        ]);

        self::assertFalse($form->isSubmitted());
        self::assertNull($form->getData());
    }

    private function getCommandBuilder(): Closure
    {
        return static function ($token, $password) {
            return new ChangeUserPassword($token, $password);
        };
    }
}

class ChangeUserPassword
{
    /** @var string */
    public $id;

    /** @var string */
    public $password;

    public function __construct(string $id, string $password)
    {
        $this->id       = $id;
        $this->password = $password;
    }
}
