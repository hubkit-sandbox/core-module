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

namespace ParkManager\Module\CoreModule\Tests\UI\Web\Form\Security;

use ParkManager\Module\CoreModule\Application\Command\Security\ConfirmUserPasswordReset;
use ParkManager\Module\CoreModule\Infrastructure\Web\Form\Security\ConfirmPasswordResetType;
use ParkManager\Module\CoreModule\Test\Crypto\FakeSplitTokenFactory;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
final class ConfirmPasswordResetTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    private $splitTokenFactory;

    protected function getExtensions()
    {
        return [
            $this->getValidatorExtension(),
        ];
    }

    protected function setUp()
    {
        $this->splitTokenFactory = new FakeSplitTokenFactory();
        parent::setUp();
    }

    protected function getTypes()
    {
        return [
            new ConfirmPasswordResetType($this->splitTokenFactory),
        ];
    }

    /** @test */
    public function it_hashes_password()
    {
        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form  = $this->factory->create(ConfirmPasswordResetType::class, null, ['token' => $token]);
        $form->submit([
            'password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']],
            'reset_token' => FakeSplitTokenFactory::FULL_TOKEN,
        ]);

        self::assertTrue($form->isValid());
        self::assertEquals(new ConfirmUserPasswordReset($token, 'encoded(Hello there)'), $form->getData());
    }

    /** @test */
    public function it_does_not_accept_invalid_input()
    {
        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form  = $this->factory->create(ConfirmPasswordResetType::class, null, ['token' => $token]);
        $form->submit([
            'password' => 'Hello there',
            'reset_token' => FakeSplitTokenFactory::FULL_TOKEN,
        ]);

        self::assertEquals(new ConfirmUserPasswordReset($token, ''), $form->getData());
    }

    /** @test */
    public function it_does_not_accept_invalid_token()
    {
        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form  = $this->factory->create(ConfirmPasswordResetType::class, null, ['token' => $token]);
        $form->submit(['password' => 'Hello there']);

        self::assertNull($form->getData());
    }

    /** @test */
    public function it_gives_null_for_model_password()
    {
        $token = $this->splitTokenFactory->fromString(FakeSplitTokenFactory::FULL_TOKEN);
        $form  = $this->factory->create(ConfirmPasswordResetType::class, null, ['token' => $token]);

        self::assertFalse($form->isSubmitted());
        self::assertNull($form->getData());
    }
}
