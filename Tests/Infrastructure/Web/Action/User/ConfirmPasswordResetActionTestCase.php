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

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Action\User;

use ParkManager\Component\Security\Token\SplitToken;
use ParkManager\Module\CoreModule\Domain\User\User;
use ParkManager\Module\CoreModule\Domain\User\UserId;
use ParkManager\Module\CoreModule\Domain\User\UserRepository;
use ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Action\HttpResponseAssertions;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

return;
/**
 * @internal
 * @group functional
 */
abstract class ConfirmPasswordResetActionTestCase extends WebTestCase
{
    /**
     * @test
     * @slowThreshold 2000 This uses Argon password hashing
     */
    public function its_accessible_by_anonymous()
    {
        $client = self::createClient();
        $token  = SplitToken::generate($this->getUserId());
        $user   = $this->givenUserExistsAndHasToken($client, $token);

        $crawler = $client->request('GET', $this->getEntryUri($token->token()));
        HttpResponseAssertions::assertRequestWasSuccessful($client);

        $form = $crawler->selectButton('button.change_password')->form();
        $form->setValues([
            $this->getFormName() . '[password][first]' => 'new-password',
            $this->getFormName() . '[password][second]' => 'new-password',
        ]);

        $client->submit($form);
        HttpResponseAssertions::assertRequestWasRedirected($client, $this->getLoginUri());

        // Now let's try to login with the new password.
        $crawler = $client->request('GET', $this->getLoginUri());

        $form = $crawler->selectButton('button.login')->form();
        $form->setValues([
            '_email' => $user->email(),
            '_password' => 'new-password',
        ]);

        $client->submit($form);
        HttpResponseAssertions::assertRequestWasRedirected($client, $this->getOnSuccessUri());

        $authToken = $client->getContainer()->get('security.token_storage')->getToken();
        self::assertNotNull($authToken);
        self::assertTrue($authToken->isAuthenticated());
    }

    /** @test */
    public function it_gives_not_found_on_invalid_token()
    {
        $client = self::createClient();
        $client->request('GET', $this->getEntryUri('foo-bar-no-car'));

        HttpResponseAssertions::assertRequestStatus($client, 404);
        self::assertContains('password_reset.error.invalid_token', $client->getResponse()->getContent());
    }

    /** @test */
    public function it_gives_not_found_when_no_results_were_found()
    {
        $client = self::createClient();
        $client->request('GET', $this->getEntryUri('H46VaCeI-DtDoW7i_ZhtTzx39ObsQJjADCUbQhSMw1cn3sHQamoDfFY3'));

        HttpResponseAssertions::assertRequestStatus($client, 404);
        self::assertContains('password_reset.error.no_token', $client->getResponse()->getContent());
    }

    protected function getFormName(): string
    {
        return 'confirm_user_password_reset';
    }

    abstract protected function getRepositoryServiceId(): string;

    abstract protected function getEntryUri(string $token): string;

    abstract protected function getLoginUri(): string;

    abstract protected function getUserId(): string;

    abstract protected function getOnSuccessUri(): string;

    private function givenUserExistsAndHasToken(Client $client, SplitToken $token): User
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get($this->getRepositoryServiceId());
        /** @var User $user */
        $user = $repository->get(UserId::fromString($this->getUserId()));
        // First reset (with an invalid token to ensure a new token is accepted)
        // then set a new token.
        $user->confirmPasswordReset(
            SplitToken::fromString('H46VaCeI-DtDoW7i_ZhtTzx39ObsQJjADCUbQhSMw1cn3sHQamoDfFY3'),
            'wrong'
        );
        $user->setPasswordResetToken($token->toValueHolder());
        $user->changePassword('impossible-to-use');
        $repository->save($user);

        return $user;
    }
}
