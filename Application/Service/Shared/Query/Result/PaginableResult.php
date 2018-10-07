<?php

declare(strict_types=1);

/*
 * This file is part of the Park-Manager project.
 *
 * Copyright (c) the Contributors as noted in the AUTHORS file.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
