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
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\ConfirmPasswordResetType;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\SecurityUserHashedPasswordType;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\SplitTokenType;
use ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Form\Type\Mocks\FakePasswordHashFactory;
use Rollerworks\Component\SplitToken\FakeSplitTokenFactory;
use Rollerworks\Component\SplitToken\SplitToken;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Translation\IdentityTranslator;

/**
 * @internal
 */
final class ConfirmPasswordResetTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    /** @var FakeSplitTokenFactory */
    private $splitTokenFactory;

    /** @var FakePasswordHashFactory */
    private $encoderFactory;

    protected function setUp(): void
    {
        $this->splitTokenFactory = new FakeSplitTokenFactory();
        $this->encoderFactory    = new FakePasswordHashFactory();

        parent::setUp();
    }

    protected function getTypes(): array
    {
        return [
            new SplitTokenType($this->splitTokenFactory, new IdentityTranslator()),
            new SecurityUserHashedPasswordType($this->encoderFactory),
        ];
    }

    /** @test */
    public function it_builds_a_confirm_command(): void
    {
        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form  = $this->factory->create(ConfirmPasswordResetType::class, ['reset_token' => $token], [
            'command_builder' => $this->getCommandBuilder(),
            'user_class' => ClientUser::class,
        ]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']],
            'reset_token' => FakeSplitTokenFactory::FULL_TOKEN,
        ]);

        self::assertTrue($form->isValid());
        self::assertEquals(new ConfirmUserPasswordReset($token, 'encoded(Hello there)'), $form->getData());
    }

    /** @test */
    public function it_gives_null_for_model_password(): void
    {
        $form = $this->factory->create(ConfirmPasswordResetType::class, null, [
            'command_builder' => $this->getCommandBuilder(),
            'user_class' => ClientUser::class,
        ]);

        self::assertFalse($form->isSubmitted());
        self::assertNull($form->getData());
    }

    private function getCommandBuilder(): Closure
    {
        return static function ($token, $password) {
            return new ConfirmUserPasswordReset($token, $password);
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
