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
 * A KeysetPageResult is returned by KeysetPaginable::getPage()
 * to provide information about the current keyset position (page).
 */
interface KeysetPageResult
{
    /**
     * Returns the ElementIdentifier for this current page.
     *
     * This value should be provided when paginating,
     * helping the Finder to determine the higher items.
     *
     * @return mixed either a string or integer
     */
    public function lastElementIdentifier();

    /**
     * Returns whether there are other items from beyond the give keyset.
     */
    public function hasNextPage(): bool;

    /**
     * Returns the items for this current page.
     *
     * @return iterable
     */
    public function getItems(): iterable;
}
