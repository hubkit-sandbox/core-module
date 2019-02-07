<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Application\Service\Shared\Query\Result;

/**
 * The PaginableResult is implemented by a provider to limit
 * the amount of records returned and providing information
 * about the total count of items.
 *
 * For performance reasons this should only be used for results when
 * offset paginating doesn't have a negative impact or uses an index.
 */
interface PaginableResult
{
    public function totalCountOfItems(): int;

    /**
     * Returns a portion of the total result.
     */
    public function slice(int $offset, int $limit): iterable;
}
