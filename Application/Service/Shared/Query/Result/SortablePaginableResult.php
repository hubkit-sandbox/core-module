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

namespace ParkManager\Module\CoreModule\Application\Service\Shared\Query\Result;

/**
 * The SortablePaginableResult is implemented by a provider to limit
 * the amount of records returned, providing information about the
 * total count of items, and which sorting is accepted.
 *
 * For performance reasons this should only be used for results when
 * offset paginating doesn't have a negative impact or uses an index.
 */
interface SortablePaginableResult extends PaginableResult
{
    public const SORT_ASCENDING  = 'asc';
    public const SORT_DESCENDING = 'desc';

    /**
     * @return array a hash that associates a field (any string) to a sort
     *               direction. The order of fields inside the array matters
     */
    public function sortSpecification(): array;

    /**
     * Returns a portion of the total result.
     *
     * @param array $sorting a hash of fields and there sorting eg. [id => asc]
     *
     * @return iterable
     */
    public function slice(int $offset, int $limit, ?array $sorting = null): iterable;
}
