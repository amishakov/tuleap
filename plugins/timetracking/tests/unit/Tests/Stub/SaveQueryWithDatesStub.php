<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Timetracking\Tests\Stub;

use DateTimeImmutable;
use Error;
use Tuleap\Timetracking\REST\v1\TimetrackingManagement\SaveQueryWithDates;
use Tuleap\Timetracking\REST\v1\TimetrackingManagement\UserList;

final class SaveQueryWithDatesStub implements SaveQueryWithDates
{
    private bool $has_been_called = false;

    private function __construct(private bool $should_not_be_called)
    {
    }

    public static function build(): self
    {
        return new self(false);
    }

    public static function shouldNotBeCalled(): self
    {
        return new self(true);
    }

    public function hasBeenCalled(): bool
    {
        return $this->has_been_called;
    }

    #[\Override]
    public function saveQueryWithDates(int $query_id, DateTimeImmutable $start_date, DateTimeImmutable $end_date, UserList $users,): void
    {
        if ($this->should_not_be_called) {
            throw new Error("Shouldn't have been called");
        }

        $this->has_been_called = true;
    }
}
