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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Form\ConfirmationHandler;

use ParkManager\Module\CoreModule\Infrastructure\Web\Form\ConfirmationHandler\CheckedConfirmationHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function implode;
use function is_scalar;
use function sprintf;

/**
 * @internal
 */
final class CheckedConfirmationHandlerTest extends TestCase
{
    private const ID1 = '2108adf4-78e6-11e7-b6b3-acbc32b58315';

    /**
     * @test
     * @dataProvider provideValidValues
     */
    public function it_returns_request_was_submitted_for_post_request(string $reqValue, string $value)
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithValid($this->createTokenId([self::ID1]))
        );

        $confirmationHandler->configure('Confirm deleting', 'Are you sure?', $reqValue, 'Yes');
        $confirmationHandler->handleRequest($this->makePostRequest($value), ['id']);

        self::assertTrue($confirmationHandler->isConfirmed(), sprintf('"%s" does not match for "%s"', $value, $reqValue));
    }

    public function provideValidValues(): iterable
    {
        return [
            ['Everything', 'Everything'],
            ['everything', 'everything'],
            ['everything', 'Everything'],
            ['Everything', 'everything'],
            ['Everything', ' everything '],
            [' Everything ', 'everything'],
            ['üpperdag', 'üpperdag'],
            ['Dünderdag', 'DÜnderdag'],
            [' üpperdag', 'üpperdag'],
            ['2000', '2000'],
        ];
    }

    /** @test */
    public function it_returns_request_was_not_submitted_for_get_request()
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithValid($this->createTokenId([self::ID1]))
        );

        $confirmationHandler->handleRequest($this->makeGetRequest(), ['id']);

        self::assertFalse($confirmationHandler->isConfirmed());
    }

    /** @test */
    public function it_returns_request_was_not_submitted_when_CSRF_token_is_missing()
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithInvalid($this->createTokenId([self::ID1]), false)
        );

        $confirmationHandler->handleRequest($this->makePostRequestWithoutToken(), ['id']);

        self::assertFalse($confirmationHandler->isConfirmed());
    }

    /** @test */
    public function it_returns_request_was_not_submitted_when_CSRF_token_is_invalid()
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithInvalid($this->createTokenId([self::ID1]))
        );

        $confirmationHandler->handleRequest($this->makeInvalidPostRequest(), ['id']);

        self::assertFalse($confirmationHandler->isConfirmed());
    }

    /**
     * @test
     * @dataProvider provideInvalidValues
     *
     * @param mixed $value
     */
    public function it_returns_request_was_not_submitted_when_value_does_not_match($value)
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithValid($this->createTokenId([self::ID1]))
        );

        $confirmationHandler->configure('Confirm deleting', 'Are you sure?', 'Everything', 'Yes');
        $confirmationHandler->handleRequest($this->makePostRequest($value), ['id']);

        self::assertFalse($confirmationHandler->isConfirmed());
        self::assertEquals(
            '<form action="/user/1/delete"><h1>Confirm deleting</h1><p>Are you sure?</p>Value does not match expected &quot;Everything&quot;.<input type="hidden" name="_value" value="' .
            (is_scalar($value) ? (string) $value : '') .
            '"><input type="hidden" name="_token" value="valid-token"><button type="submit">Yes</button><a href="">Cancel</a></form>',
            $confirmationHandler->render('checked_confirm.html.twig')
        );
    }

    public function provideInvalidValues(): iterable
    {
        return [
            ['Nope'],
            ['7464435244'],
            [35244],
            [''],
            [null],
            [false],
            [[]],
        ];
    }

    /** @test */
    public function it_fails_when_checking_confirmation_without_handled_request()
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithValid($this->createTokenId([self::ID1]))
        );

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Unable perform operation, call handleRequest() first.');

        $confirmationHandler->isConfirmed();
    }

    /** @test */
    public function it_renders_template_with_token_validity()
    {
        $confirmationHandler = new CheckedConfirmationHandler(
            $this->createTwigEnvironment(),
            $this->createTokenManagerWithInvalid($this->createTokenId([self::ID1]))
        );
        $confirmationHandler->handleRequest($this->makeInvalidPostRequest(), ['id']);
        $confirmationHandler->configure('Confirm deleting', 'Are you sure?', 'Everything', 'Yes');
        $confirmationHandler->setCancelUrl('/user/1/show');

        self::assertFalse($confirmationHandler->isConfirmed());
        self::assertEquals(
            '<form action="/user/1/delete"><h1>Confirm deleting</h1><p>Are you sure?</p>Invalid CSRF token.<input type="hidden" name="_value" value=""><input type="hidden" name="_token" value="valid-token"><button type="submit">Yes</button><a href="/user/1/show">Cancel</a></form>',
            $confirmationHandler->render('checked_confirm.html.twig')
        );
    }

    /**
     * @param mixed $value
     * @param array $attributes
     */
    private function makePostRequest($value = 'everything', array $attributes = ['id' => self::ID1]): Request
    {
        $request = Request::create('/', 'POST');
        $request->request->set('_token', 'valid-token');
        $request->attributes->add($attributes);
        $request->request->set('_value', $value);

        return $request;
    }

    private function makeInvalidPostRequest(array $attributes = ['id' => self::ID1]): Request
    {
        $request = Request::create('/', 'POST');
        $request->attributes->add($attributes);
        $request->request->set('_token', 'wrong-token');

        return $request;
    }

    private function makePostRequestWithoutToken(array $attributes = ['id' => self::ID1]): Request
    {
        $request = Request::create('/', 'POST');
        $request->attributes->add($attributes);

        return $request;
    }

    private function makeGetRequest(array $attributes = ['id' => self::ID1]): Request
    {
        $request = Request::create('/');
        $request->attributes->add($attributes);

        return $request;
    }

    private function createTokenId(array $ids): string
    {
        return 'confirm.' . implode('~', $ids) . '~';
    }

    private function createTokenManagerWithInvalid(string $tokenId, bool $hasToken = true): CsrfTokenManagerInterface
    {
        $tokenManagerProphecy = $this->prophesize(CsrfTokenManagerInterface::class);
        $tokenManagerProphecy->getToken($tokenId)->willReturn(new CsrfToken($tokenId, 'valid-token'));
        $tokenManagerProphecy->isTokenValid(Argument::any())->willReturn(false);

        if ($hasToken) {
            $tokenManagerProphecy->removeToken($tokenId)->shouldBeCalled();
        }

        return $tokenManagerProphecy->reveal();
    }

    private function createTokenManagerWithValid(string $tokenId): CsrfTokenManagerInterface
    {
        $tokenManagerProphecy = $this->prophesize(CsrfTokenManagerInterface::class);
        $tokenManagerProphecy->getToken($tokenId)->willReturn(new CsrfToken($tokenId, 'valid-token'));
        $tokenManagerProphecy->isTokenValid(new CsrfToken($tokenId, 'valid-token'))->willReturn(true);

        return $tokenManagerProphecy->reveal();
    }

    private function createTwigEnvironment(): Environment
    {
        return new Environment(new FilesystemLoader([__DIR__ . '/templates'], __DIR__ . '/templates'), [
            'debug' => true,
            'strict_variables' => true,
        ]);
    }
}
