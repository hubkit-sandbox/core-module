<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Form\FormHandler\Tests;

use ParkManager\Module\CoreModule\Infrastructure\Web\Form\FormHandler\CommandBusFormHandler;
use ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Form\FormHandler\Tests\Mock\StubCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
use Symfony\Component\Messenger\MessageBusInterface as MessageBus;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ValidatorBuilder;
use function explode;
use function iterator_to_array;

/**
 * @internal
 */
final class CommandBusFormHandlerTest extends TestCase
{
    public function its_constructable()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm();
        $handler = new CommandBusFormHandler($form, $commandBus);

        self::assertSame($form, $handler->getForm());
    }

    /** @test */
    public function it_handles_non_submit_request()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm();
        $handler = new CommandBusFormHandler($form, $commandBus);

        $request = Request::create('/');
        $handler->handleRequest($request);

        self::assertFalse($handler->isReady());
        self::assertFalse($form->isSubmitted());
    }

    /** @test */
    public function it_handles_submit_request_for_other_form()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm(new StubCommand());
        $handler = new CommandBusFormHandler($form, $commandBus);

        $request = Request::create('/', 'POST');
        $handler->handleRequest($request);

        self::assertFalse($handler->isReady());
        self::assertFalse($form->isSubmitted());
    }

    /** @test */
    public function it_handles_submit_request_without_errors()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::which('id', 5))->shouldBeCalled();
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm(new StubCommand());
        $handler = new CommandBusFormHandler($form, $commandBus);

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);

        $handler->handleRequest($request);

        self::assertTrue($handler->isReady());
        self::assertTrue($form->isSubmitted());
    }

    /** @test */
    public function it_handles_submit_request_with_existing_errors()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm(new StubCommand());
        $handler = new CommandBusFormHandler($form, $commandBus);

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 'nope']);

        $handler->handleRequest($request);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());
        self::assertFalse($handler->isReady());

        $errors = $form->getErrors(true, true);

        self::assertCount(1, $errors);
    }

    /** @test */
    public function it_forbids_handling_more_then_once()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::which('id', 5))->shouldBeCalledTimes(1);
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm(new StubCommand());
        $handler = new CommandBusFormHandler($form, $commandBus);

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);

        $handler->handleRequest($request);

        $this->expectException(AlreadySubmittedException::class);
        $this->expectExceptionMessage('A form can only be handled once.');

        $handler->handleRequest($request);
    }

    /** @test */
    public function it_does_not_validate_command_if_submitting()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::which('id', 5))->shouldBeCalledTimes(1);
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm(new StubCommand());
        $handler = new CommandBusFormHandler($form, $commandBus, function () {
            throw new \InvalidArgumentException('This command is not invalid it is not.');
        });

        $request = Request::create('/', 'POST');
        $request->request->set($form->getName(), ['id' => 5]);
        $handler->handleRequest($request);

        self::assertTrue($handler->isReady());
        self::assertTrue($form->isSubmitted());
    }

    /** @test */
    public function it_validates_command_if_not_submitting()
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::any())->shouldNotBeCalled();
        $commandBus = $commandBusProphecy->reveal();

        $form    = $this->createRealForm(new StubCommand());
        $handler = new CommandBusFormHandler($form, $commandBus, function () {
            throw new \InvalidArgumentException('This command is not invalid it is not.');
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This command is not invalid it is not.');

        $request = Request::create('/', 'GET');
        $handler->handleRequest($request);
    }

    /**
     * @test
     * @dataProvider provideExceptions
     *
     * @param array<FormError[]> $expectedErrors
     */
    public function it_maps_command_bus_exceptions(\Exception $exception, array $expectedErrors)
    {
        $commandBusProphecy = $this->prophesize(MessageBus::class);
        $commandBusProphecy->dispatch(Argument::which('id', 5))->willThrow($exception);
        $commandBus = $commandBusProphecy->reveal();

        $form = $this->createRealForm(new StubCommand());

        $handler = new CommandBusFormHandler($form, $commandBus);
        $handler->mapException(
            \InvalidArgumentException::class,
            function (\Throwable $e) {
                return new FormError('Root problem is here', null, [], null, $e);
            }
        );
        $handler->mapException(
            \RuntimeException::class,
            function (\Throwable $e) {
                return [
                    null => new FormError('Root problem is here2', null, [], null, $e),
                    'username' => new FormError('Username problem is here', null, [], null, $e),
                ];
            }
        );
        $handler->setExceptionFallback(
            function (\Throwable $e) {
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

    public static function provideExceptions(): array
    {
        return [
            'root form error' => [
                $e = new \InvalidArgumentException('Epon'),
                [
                    null => [new FormError('Root problem is here', null, [], null, $e)],
                ],
            ],
            'sub form' => [
                $e = new \RuntimeException('Ah interesting'),
                [
                    null => [new FormError('Root problem is here2', null, [], null, $e)],
                    'username' => [new FormError('Username problem is here', null, [], null, $e)],
                ],
            ],
            'fallback for form' => [
                $e = new \Exception('You know nothing'),
                [
                    'profile.contact.email' => [new FormError('Contact Email problem is here', null, [], null, $e)],
                ],
            ],
        ];
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

                    public function getExtendedType(): string
                    {
                        return FormType::class;
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

        $form = $formFactory->createBuilder(FormType::class, $data)
            ->add('id', IntegerType::class, ['required' => false])
            ->add('username', TextType::class, ['required' => false])
            ->add($profileFormType)
            ->getForm();

        return $form;
    }
}
