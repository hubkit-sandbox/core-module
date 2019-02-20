<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type;

use Closure;
use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\ChangePasswordType;
use ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type\Mocks\FakePasswordHashFactory;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
final class ChangePasswordTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    /** @var FakePasswordHashFactory */
    private $encoderFactory;

    protected function getExtensions(): array
    {
        return [
            $this->getValidatorExtension(),
        ];
    }

    protected function setUp(): void
    {
        $this->encoderFactory = new FakePasswordHashFactory();

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
