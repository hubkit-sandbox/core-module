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

namespace ParkManager\Module\CoreModule\Infrastructure\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface as UrlGenerator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface as UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface as CsrfTokenManager;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class FormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $csrfTokenManager;
    private $passwordEncoder;
    private $urlGenerator;
    private $loginRoute;
    private $defaultSuccessRoute;

    public function __construct(CsrfTokenManager $csrfTokenManager, UserPasswordEncoder $passwordEncoder, UrlGenerator $urlGenerator, string $loginRoute, string $defaultSuccessRoute = '/')
    {
        $this->csrfTokenManager    = $csrfTokenManager;
        $this->passwordEncoder     = $passwordEncoder;
        $this->urlGenerator        = $urlGenerator;
        $this->loginRoute          = $loginRoute;
        $this->defaultSuccessRoute = $defaultSuccessRoute;
    }

    public function getCredentials(Request $request): array
    {
        $csrfToken = $request->request->get('_csrf_token');

        if ($this->csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $csrfToken)) === false) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }

        $email = $request->request->get('_email');

        if ($request->hasSession()) {
            $request->getSession()->set(Security::LAST_USERNAME, $email);
        }

        return [
            'email' => $email,
            'password' => $request->request->get('_password'),
        ];
    }

    /**
     * @param array        $credentials
     * @param UserProvider $userProvider
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?SecurityUser
    {
        $email = $credentials['email'];

        if ($email === null) {
            return null;
        }

        return $userProvider->loadUserByUsername($email);
    }

    /**
     * @param mixed        $credentials
     * @param SecurityUser $user
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        if (! $this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            throw new BadCredentialsException();
        }

        if (! $user->isEnabled()) {
            throw new AuthenticationException();
        }

        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        $targetPath = null;

        if ($request->getSession() instanceof SessionInterface) {
            $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        }

        if (! $targetPath) {
            $targetPath = $this->urlGenerator->generate($this->defaultSuccessRoute);
        }

        return new RedirectResponse($targetPath);
    }

    public function supports(Request $request)
    {
        return $request->request->has('_email');
    }

    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate($this->loginRoute);
    }
}
