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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Common\Form\Handler;

use Exception;
use InvalidArgumentException;
use ParkManager\Module\CoreModule\Infrastructure\UserInterface\Web\Common\Form\Handler\CommandBusFormHandler;
use ParkManager\Module\CoreModule\Tests\Infrastructure\UserInterface\Web\Common\Form\Handler\Mock\StubCommand;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\HttpFoundation\Type\FormTypeHttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface as MessageBus;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ValidatorBuilder;
use Throwable;
use function explode;
use function iterator_to_array;

/**
 * @internal
 */
final class CommandBusFormHandlerTest extends TestCase
{
    public function its_constructable(): void
    {
        $form       = $this->createRealForm();
        $commandBus = $this->createMessageBus();
        $handler    = new CommandBusFormHandler($form, $commandBus);

        self::assertSame($form, $handler->getForm());
        $this->assertNoMessagesDispatches($commandBus);
    }

    private function createRealForm(?object $data = null): FormInterface
    {
        $formFactory = (new FormFactoryBuilder())
            ->addTypeExtension(new FormTypeHttpFoundationExtension())
            ->addExtension(new ValidatorExtension((new ValidatorBuilder())->getValidator()))
            ->addTypeExtension(
                new class() extends AbstractTypeExtension {
                    public function configureOptions(OptionsResolver $resolver): void
                    {
                        $resolver->setDefault('exception_mapping', []);
                    }

                    public static function getExtendedTypes(): iterable
                    {
                        yield FormType::class;
                    }
                }
            )
            ->getFormFactory();

        $profileContactFormType = $formFactory->createNamedBuilder('contact')
            ->add('email', TextType::class, ['required' => false])
            ->add('address', TextType::class, ['required' => false]);

        $profileFormType = $formFactory->createNamedBuilder('profile')
            ->add('name', TextType::class, ['required' => false])
            ->add($profileContactFormType);

        return $formFactory->createBuilder(FormType::class, $data)
            ->add('id', IntegerType::class, ['required' => false])
            ->add('username', TextType::class, ['required' => false])
            ->add($profileFormType)
            ->getForm();
    }

    private function createMessageBus(): SpyingMessageBus
    {
        return new SpyingMessageBus();
    }

    private function assertNoMessagesDispatches(object $commandBus): void
    {
        self::assertSame([], $commandBus->getDispatchedMessages());
    }

    /** @test */
    public function it_handles_non_submit_request(): void
    {
        $form       = $this->createRealForm();
        $commandBus = $this->createMessageBus();

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->handleRequest(Request::create('/'));

        self::assertFalse($handler->isReady());
        self::assertFalse($form->isSubmitted());
        $this->assertNoMessagesDispatches($commandBus);
    }

    /** @test */
    public function it_handles_submit_request_for_other_form(): void
    {
        $commandBus = $this->createMessageBus();
        $form       = $this->createRealForm(new StubCommand());

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->handleRequest(Request::create('/', 'POST'));

        self::assertFalse($handler->isReady());
        self::assertFalse($form->isSubmitted());
        $this->assertNoMessagesDispatches($commandBus);
    }

    /** @test */
    public function it_handles_submit_request_without_errors(): void
    {
        $form       = $this->createRealForm(new StubCommand());
        $commandBus = $this->createMessageBus();

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->handleRequest($request);

        self::assertTrue($handler->isReady());
        self::assertTrue($form->isSubmitted());
        self::assertEquals(
            $commandBus->getDispatchedMessages(),
            [
                new StubCommand(5, null, [
                    'name' => null,
                    'contact' => ['email' => null, 'address' => null],
                ]),
            ]
        );
    }

    /** @test */
    public function it_handles_submit_request_with_existing_errors(): void
    {
        $form       = $this->createRealForm(new StubCommand());
        $commandBus = $this->createMessageBus();

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 'nope']);

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->handleRequest($request);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
        self::assertFalse($handler->isReady());

        $errors = $form->getErrors(true, true);

        self::assertCount(1, $errors);
        $this->assertNoMessagesDispatches($commandBus);
    }

    /** @test */
    public function it_forbids_handling_more_then_once(): void
    {
        $form       = $this->createRealForm(new StubCommand());
        $commandBus = $this->createMessageBus();

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->handleRequest($request);

        self::assertEquals(
            $commandBus->getDispatchedMessages(),
            [
                new StubCommand(5, null, [
                    'name' => null,
                    'contact' => ['email' => null, 'address' => null],
                ]),
            ]
        );

        $this->expectException(AlreadySubmittedException::class);
        $this->expectExceptionMessage('A form can only be handled once.');

        $handler->handleRequest($request);
    }

    /** @test */
    public function it_does_not_validate_command_if_submitting(): void
    {
        $form       = $this->createRealForm(new StubCommand());
        $commandBus = $this->createMessageBus();

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);

        $handler = new CommandBusFormHandler($form, $commandBus, static function () {
            throw new InvalidArgumentException('This command is not invalid it is not.');
        });
        $handler->handleRequest($request);

        self::assertTrue($handler->isReady());
        self::assertTrue($form->isSubmitted());
        self::assertEquals(
            $commandBus->getDispatchedMessages(),
            [
                new StubCommand(5, null, [
                    'name' => null,
                    'contact' => ['email' => null, 'address' => null],
                ]),
            ]
        );
    }

    /** @test */
    public function it_validates_command_if_not_submitting(): void
    {
        $handler    = new CommandBusFormHandler(
            $this->createRealForm(new StubCommand()),
            $commandBus = $this->createMessageBus(),
            static function () {
                throw new InvalidArgumentException('This command is not invalid it is not.');
            }
        );

        try {
            $request = Request::create('/');
            $handler->handleRequest($request);

            $this->fail('Exception was expected.');
        } catch (InvalidArgumentException $e) {
            self::assertSame('This command is not invalid it is not.', $e->getMessage());
            $this->assertNoMessagesDispatches($commandBus);
        }
    }

    /**
     * @param array<FormError[]> $expectedErrors
     *
     * @test
     * @dataProvider provideExceptions
     */
    public function it_maps_command_bus_exceptions(Throwable $exception, array $expectedErrors): void
    {
        $form       = $this->createRealForm(new StubCommand());
        $commandBus = $this->createExceptionThrowingMessageBus($exception);

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->mapException(
            InvalidArgumentException::class,
            static function (Throwable $e) {
                return new FormError('Root problem is here', null, [], null, $e);
            }
        );
        $handler->mapException(
            RuntimeException::class,
            static function (Throwable $e) {
                return [
                    null => new FormError('Root problem is here2', null, [], null, $e),
                    'username' => new FormError('Username problem is here', null, [], null, $e),
                ];
            }
        );
        $handler->setExceptionFallback(
            static function (Throwable $e) {
                return [
                    'profile.contact.email' => new FormError('Contact Email problem is here', null, [], null, $e),
                ];
            }
        );

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);
        $handler->handleRequest($request);

        self::assertFalse($handler->isReady());
        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());

        foreach ($expectedErrors as $formPath => $formErrors) {
            $formPath    = (string) $formPath;
            $currentForm = $form;

            if ($formPath !== '') {
                foreach (explode('.', $formPath) as $child) {
                    $currentForm = $currentForm->get($child);
                }
            }

            /** @var FormError $error */
            foreach ($formErrors as $error) {
                $error->setOrigin($currentForm);
            }

            self::assertEquals($formErrors, iterator_to_array($currentForm->getErrors()));
        }
    }

    private function createExceptionThrowingMessageBus(Throwable $e)
    {
        return new class($e) implements MessageBus {
            private $e;

            public function __construct(Throwable $e)
            {
                $this->e = $e;
            }

            public function dispatch($message): Envelope
            {
                throw $this->e;
            }
        };
    }

    public static function provideExceptions(): iterable
    {
        yield 'root form error' => [
            $e = new InvalidArgumentException('Epon'),
            [
                null => [new FormError('Root problem is here', null, [], null, $e)],
            ],
        ];

        yield 'sub form' => [
            $e = new RuntimeException('Ah interesting'),
            [
                null => [new FormError('Root problem is here2', null, [], null, $e)],
                'username' => [new FormError('Username problem is here', null, [], null, $e)],
            ],
        ];

        yield 'fallback for form' => [
            $e = new Exception('You know nothing'),
            [
                'profile.contact.email' => [new FormError('Contact Email problem is here', null, [], null, $e)],
            ],
        ];
    }
}

class SpyingMessageBus implements MessageBus
{
    private $dispatchedMessages = [];

    public function dispatch($message): Envelope
    {
        $this->dispatchedMessages[] = $message;

        if (! $message instanceof Envelope) {
            $message = new Envelope($message);
        }

        return $message;
    }

    public function getDispatchedMessages(): array
    {
        return $this->dispatchedMessages;
    }
}
