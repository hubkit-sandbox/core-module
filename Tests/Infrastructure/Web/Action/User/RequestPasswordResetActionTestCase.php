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

use ParkManager\Module\CoreModule\Tests\Infrastructure\Web\Action\HttpResponseAssertions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

return;
/**
 * @internal
 *
 * @group functional
 */
abstract class RequestPasswordResetActionTestCase extends WebTestCase
{
    /**
     * @test
     * @slowThreshold 1000 This uses Argon password hashing
     */
    public function its_accessible_by_anonymous()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', $this->getEntryUri());
        HttpResponseAssertions::assertRequestWasSuccessful($client);

        $form = $crawler->selectButton('password_reset.submit_button')->form();
        $form->setValues([$this->getFormName().'[email]' => $this->getEmailAddress()]);

        $client->submit($form);
        HttpResponseAssertions::assertRequestWasRedirected($client, $this->getLoginUri());
    }

    protected function getFormName(): string
    {
        return 'request_user_password_reset';
    }

    abstract protected function getEntryUri(): string;

    abstract protected function getLoginUri(): string;

    abstract protected function getEmailAddress(): string;
}
