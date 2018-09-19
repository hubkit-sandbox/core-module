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

use ParkManager\Module\CoreModule\Domain\User\UserRepository;
use ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Action\HttpResponseAssertions;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function sprintf;

return;
/**
 * @internal
 *
 * @group functional
 */
abstract class ChangePasswordActionTestCase extends WebTestCase
{
    /**
     * @test
     * @slowThreshold 1000 This uses Argon password hashing
     */
    public function it_changes_password()
    {
        $client = self::createClient([], [
            'TEST_AUTH_USERNAME' => $this->getUsername(),
            'TEST_AUTH_PASSWORD' => $this->getCurrentPassword(),
            'TEST_AUTH_PASSWORD_NEW' => 'yea-my-new-password',
        ]);
        $this->givenUserExists($client);

        $crawler = $client->request('GET', $this->getActionUri($client));
        HttpResponseAssertions::assertRequestWasSuccessful($client);

        $form = $crawler->selectButton('button.change_password')->form();
        $form->setValues([
            $this->getFormName() . '[cur_password]' => $this->getCurrentPassword(),
            $this->getFormName() . '[new_password][first]' => 'yea-my-new-password',
            $this->getFormName() . '[new_password][second]' => 'yea-my-new-password',
        ]);

        $client->submit($form);
        HttpResponseAssertions::assertRequestWasRedirected($client, $this->getRedirectUri($client));
    }

    /**
     * @test
     * @slowThreshold 1000 This uses Argon password hashing
     */
    public function it_requires_current_password_is_provided()
    {
        $client = self::createClient([], [
            'TEST_AUTH_USERNAME' => $this->getUsername(),
            'TEST_AUTH_PASSWORD' => $this->getCurrentPassword(),
        ]);
        $this->givenUserExists($client);

        $crawler = $client->request('GET', $this->getActionUri($client));
        HttpResponseAssertions::assertRequestWasSuccessful($client);

        $form = $crawler->selectButton('button.change_password')->form();
        $form->setValues([
            $this->getFormName() . '[cur_password]' => 'Nope',
            $this->getFormName() . '[new_password][first]' => 'yea-my-new-password',
            $this->getFormName() . '[new_password][second]' => 'yea-my-new-password',
        ]);

        $client->submit($form);
        HttpResponseAssertions::assertRequestStatus($client);
        self::assertContains('This value should be the user&#039;s current password.', $client->getInternalResponse()->getContent());
    }

    protected function getFormName(): string
    {
        return 'change_user_password';
    }

    protected function getUsername(): string
    {
        return 'jogn@example.com';
    }

    protected function getCurrentPassword(): string
    {
        return 'Gh?fs=%dgs_aa2ahh-9';
    }

    protected function getCurrentPasswordHash(): string
    {
        return '$argon2i$v=19$m=32768,t=4,p=1$YYV41xT1WThZybbLUbluZA$AjjPfEQqv1Y4xOWh1rMo6ZEu5P6Q3c6tJrpIOUzhk+Y';
    }

    abstract protected function getActionUri(Client $client): string;

    abstract protected function getRedirectUri(Client $client): string;

    abstract protected function getRepositoryServiceId(): string;

    protected function givenUserExists(Client $client)
    {
        /** @var UserRepository $repository */
        $repository = $client->getContainer()->get($this->getRepositoryServiceId());
        $user       = $repository->findByEmailAddress($this->getUsername());

        if ($user === null) {
            $this->fail(sprintf('User with e-mail address %s is not registered. Are fixtures loaded?', $this->getUsername()));
        }

        $user->changePassword($this->getCurrentPasswordHash());
        $repository->save($user);

        $em = $client->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->flush();

        $connection = $em->getConnection();

        // Copied from \Doctrine\DBAL\Connection::commitAll()
        while ($connection->getTransactionNestingLevel() !== 0) {
            if ($connection->isAutoCommit() === false && $connection->getTransactionNestingLevel() === 1) {
                // When in no auto-commit mode, the last nesting commit immediately starts a new transaction.
                // Therefore we need to do the final commit here and then leave to avoid an infinite loop.
                $connection->commit();

                return;
            }

            $connection->commit();
        }
    }
}
