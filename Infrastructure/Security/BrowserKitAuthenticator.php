<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use function strtr;

/**
 * The BrowserKitAuthenticator is only to be used during BrowserKit tests.
 */
final class BrowserKitAuthenticator extends AbstractGuardAuthenticator
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getCredentials(Request $request): array
    {
        return [
            'username' => $request->server->get('TEST_AUTH_USERNAME'),
            'password' => $request->server->get('TEST_AUTH_PASSWORD'),
            'password_new' => $request->server->get('TEST_AUTH_PASSWORD_NEW'),
        ];
    }

    /**
     * @param array        $credentials
     * @param UserProvider $userProvider
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?SecurityUser
    {
        $email = $credentials['username'];

        if ($email === null) {
            return null;
        }

        return $userProvider->loadUserByUsername($email);
    }

    /**
     * @param array        $credentials
     * @param SecurityUser $user
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        if (! $user->isEnabled()) {
            throw new AuthenticationException();
        }

        if (! $this->passwordEncoder->isPasswordValid($user, $credentials['password']) &&
            ($credentials['password_new'] !== null &&
             ! $this->passwordEncoder->isPasswordValid($user, $credentials['password_new']))
        ) {
            throw new BadCredentialsException();
        }

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new Response('Auth header required', 401);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }

    public function supports(Request $request): bool
    {
        return $request->server->has('TEST_AUTH_USERNAME');
    }
}
