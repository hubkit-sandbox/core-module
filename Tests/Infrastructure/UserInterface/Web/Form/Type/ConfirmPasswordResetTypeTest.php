<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type;

use Closure;
use ParkManager\Module\CoreModule\Domain\Shared\Exception\PasswordResetTokenNotAccepted;
use ParkManager\Module\CoreModule\Infrastructure\Security\ClientUser;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\ConfirmPasswordResetType;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\SecurityUserHashedPasswordType;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\SplitTokenType;
use ParkManager\Module\CoreModule\Test\Infrastructure\UserInterface\Web\Form\TransformationFailureExtension;
use ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type\Mocks\FakePasswordHashFactory;
use Rollerworks\Bundle\MessageBusFormBundle\Test\MessageFormTestCase;
use Rollerworks\Component\SplitToken\FakeSplitTokenFactory;
use Rollerworks\Component\SplitToken\SplitToken;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Translation\IdentityTranslator;
use Throwable;

/**
 * @internal
 */
final class ConfirmPasswordResetTypeTest extends MessageFormTestCase
{
    use ValidatorExtensionTrait;

    /** @var FakeSplitTokenFactory */
    private $splitTokenFactory;

    /** @var FakePasswordHashFactory */
    private $encoderFactory;

    protected static function getCommandName(): string
    {
        return ConfirmUserPasswordReset::class;
    }

    protected function setUp(): void
    {
        $this->commandHandler    = static function (ConfirmUserPasswordReset $command) { };
        $this->splitTokenFactory = new FakeSplitTokenFactory();
        $this->encoderFactory    = new FakePasswordHashFactory();

        parent::setUp();
    }

    protected function getTypes(): array
    {
        return [
            $this->getMessageType(),
            new SplitTokenType($this->splitTokenFactory, new IdentityTranslator()),
            new SecurityUserHashedPasswordType($this->encoderFactory),
        ];
    }

    protected function getTypeExtensions(): array
    {
        return [
            new TransformationFailureExtension(),
        ];
    }

    /** @test */
    public function it_builds_a_confirm_command(): void
    {
        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form  = $this->factory->create(ConfirmPasswordResetType::class, ['reset_token' => $token], [
            'command_bus' => 'command_bus',
            'command_message_factory' => $this->getCommandBuilder(),
            'user_class' => ClientUser::class,
        ]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']],
            'reset_token' => FakeSplitTokenFactory::FULL_TOKEN,
        ]);

        self::assertTrue($form->isValid());
        self::assertEquals(new ConfirmUserPasswordReset($token, 'encoded(Hello there)'), $this->dispatchedCommand);

        $formViewVars = $form->createView()->vars;
        self::assertArrayHasKey('token_invalid', $formViewVars);
        self::assertFalse($formViewVars['token_invalid']);
    }

    /** @test */
    public function it_gives_null_for_model_password(): void
    {
        $form = $this->factory->create(ConfirmPasswordResetType::class, null, [
            'command_bus' => 'command_bus',
            'command_message_factory' => $this->getCommandBuilder(),
            'user_class' => ClientUser::class,
        ]);

        self::assertFalse($form->isSubmitted());
        self::assertNull($form->getData());
    }

    /** @test */
    public function it_sets_the_invalid_token_view_variable(): void
    {
        $form = $this->factory->create(ConfirmPasswordResetType::class, ['reset_token' => 'NopeNopeNopeNopeNope'], [
            'command_bus' => 'command_bus',
            'command_message_factory' => $this->getCommandBuilder(),
            'user_class' => ClientUser::class,
        ]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']],
            'reset_token' => 'NopeNopeNopeNopeNope',
        ]);

        $this->assertFormHasErrors($form, [
            '' => [
                new FormError('password_reset.invalid_token', 'password_reset.invalid_token', ['{{ value }}' => 'NopeNopeNopeNopeNope']),
            ],
        ]);

        $formViewVars = $form->createView()->vars;
        self::assertArrayHasKey('token_invalid', $formViewVars);
        self::assertTrue($formViewVars['token_invalid']);
    }

    /**
     * @test
     * @dataProvider provideErrors
     */
    public function it_handles_errors(Throwable $error, $expectedErrors): void
    {
        $this->commandHandler = static function () use ($error) {
            throw $error;
        };

        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form = $this->factory->create(ConfirmPasswordResetType::class, ['reset_token' => $token], [
            'command_bus' => 'command_bus',
            'command_message_factory' => $this->getCommandBuilder(),
            'user_class' => ClientUser::class,
        ]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']],
            'reset_token' => FakeSplitTokenFactory::FULL_TOKEN,
        ]);

        $this->assertFormHasErrors($form, $expectedErrors);
    }

    public function provideErrors(): iterable
    {
        yield 'PasswordResetTokenNotAccepted with token' => [
            new PasswordResetTokenNotAccepted((new FakeSplitTokenFactory())->generate()->toValueHolder()),
            [
                new FormError('password_reset.invalid_token'),
            ],
        ];

        yield 'PasswordResetTokenNotAccepted without token' => [
            new PasswordResetTokenNotAccepted(),
            [
                new FormError('password_reset.no_token'),
            ],
        ];

        yield 'Access disabled' => [
            new DisabledException(),
            [
                new FormError('password_reset.access_disabled'),
            ],
        ];
    }

    private function getCommandBuilder(): Closure
    {
        return static function (array $data) {
            return new ConfirmUserPasswordReset($data['reset_token'], $data['password']);
        };
    }
}

class ConfirmUserPasswordReset
{
    /** @var SplitToken */
    private $token;

    /** @var string */
    private $password;

    public function __construct(SplitToken $token, string $password)
    {
        $this->token    = $token;
        $this->password = $password;
    }

    public function token(): SplitToken
    {
        return $this->token;
    }

    public function password(): string
    {
        return $this->password;
    }
}
