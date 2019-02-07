<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Infrastructure\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use function preg_match;

/**
 * Compares a pre-defined set of checks against a Request instance.
 *
 * In addition to the RequestMatcher base class this also allows
 * to match against HTTP cookies.
 */
final class CookiesRequestMatcher extends RequestMatcher
{
    /** @var array */
    private $cookies = [];

    /**
     * Adds a check for the HTTP Cookies.
     *
     * @param array $cookies An array of names and a _regexp pattern_ to match there value against
     */
    public function matchCookies(array $cookies): void
    {
        $this->cookies = $cookies;
    }

    public function matches(Request $request): bool
    {
        if (! parent::matches($request)) {
            return false;
        }

        foreach ($this->cookies as $key => $pattern) {
            if (! preg_match('{' . $pattern . '}', (string) $request->cookies->get($key, ''))) {
                return false;
            }
        }

        return true;
    }
}
