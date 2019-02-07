<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Test\Infrastructure\UserInterface;

use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\VarDumper\VarDumper;

final class HttpResponseAssertions
{
    public static function assertRequestWasSuccessful(Client $client): void
    {
        if (! $client->getResponse()->isSuccessful()) {
            Assert::fail(
                'Last request was not successful (statusCode: ]200 - 300]):' .
                VarDumper::dump($client->getInternalRequest()) .
                VarDumper::dump($client->getInternalResponse())
            );
        }

        Assert::assertTrue(true);
    }

    public static function assertRequestStatus(Client $client, int $statusCode = 200): void
    {
        if ($statusCode !== $client->getResponse()->getStatusCode()) {
            Assert::assertEquals(
                $statusCode,
                $client->getResponse()->getStatusCode(),
                'Last response did not match status-code. ' .
                VarDumper::dump($client->getInternalRequest()) .
                VarDumper::dump($client->getInternalResponse())
            );
        }

        Assert::assertTrue(true);
    }

    public static function assertRequestWasRedirected(Client $client, string ...$expectedUrls): void
    {
        foreach ($expectedUrls as $expectedUrl) {
            if (! $client->getResponse()->isRedirect($expectedUrl)) {
                Assert::fail(
                    'Last request was not a redirect to: ' . $expectedUrl .
                    VarDumper::dump($client->getInternalRequest()) .
                    VarDumper::dump($client->getInternalResponse())
                );
            }

            $client->followRedirect();
        }

        self::assertRequestWasSuccessful($client);
    }
}
