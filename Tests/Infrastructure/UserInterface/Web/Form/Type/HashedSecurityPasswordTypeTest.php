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

use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Form\Type\Security\HashedPasswordType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 */
final class HashedSecurityPasswordTypeTest extends TypeTestCase
{
    use ValidatorExtensionTrait;

    protected function getExtensions(): array
    {
        return [
            $this->getValidatorExtension(),
        ];
    }

    /** @test */
    public function it_hashes_password(): void
    {
        $builder = $this->factory->createBuilder();
        $builder->add(
            'password',
            HashedPasswordType::class,
            [
                'algorithm' => static function (string $value) {
                    return 'encoded(' . $value . ')';
                },
            ]
        );

        $form = $builder->getForm();
        $form->submit(['password' => ['password' => 'Hello there']]);

        self::assertTrue($form->isValid());
        self::assertEquals(['password' => 'encoded(Hello there)'], $form->getData());
    }

    /** @test */
    public function it_gives_null_for_model_password(): void
    {
        $builder = $this->factory->createBuilder(FormType::class, ['name' => 'Ruby']);
        $builder->add('name', TextType::class);
        $builder->add(
            'password',
            HashedPasswordType::class,
            [
                'algorithm' => static function (string $value) {
                    return 'encoded(' . $value . ')';
                },
            ]
        );

        $form = $builder->getForm();

        self::assertFalse($form->isSubmitted());
        self::assertEquals(['name' => 'Ruby'], $form->getData());
    }

    /** @test */
    public function it_works_with_repeated_password(): void
    {
        $builder = $this->factory->createBuilder();
        $builder->add('password', HashedPasswordType::class, [
            'password_confirm' => true,
            'algorithm' => static function (string $value) {
                return 'encoded(' . $value . ')';
            },
        ]);

        $form = $builder->getForm();
        $form->submit(['password' => ['password' => ['first' => 'Hello there', 'second' => 'Hello there']]]);

        self::assertTrue($form->isValid());
        self::assertEquals(['password' => 'encoded(Hello there)'], $form->getData());
    }
}
