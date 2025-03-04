<?php
/**
 * Copyright (c) Enalean, 2013-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use Tuleap\AgileDashboard\Milestone\Backlog\IBacklogItem;

/**
 * First class collection of Backlog Items
 */
interface AgileDashboard_Milestone_Backlog_IBacklogItemCollection extends Iterator, Countable
{
    public function getParentItemName(): string;

    public function setParentItemName(string $name): void;

    public function push(IBacklogItem $item): void;

    public function containsId(int $id): bool;

    public function getTotalAvaialableSize(): int;

    public function setTotalAvaialableSize(int $size): void;

    /**
     * @return list<int>
     */
    public function getItemIds(): array;
}
