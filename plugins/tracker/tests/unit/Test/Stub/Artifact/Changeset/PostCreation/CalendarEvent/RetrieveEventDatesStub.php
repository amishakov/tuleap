<?php
/**
 * Copyright (c) Enalean, 2023 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Test\Stub\Artifact\Changeset\PostCreation\CalendarEvent;

use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Ok;
use Tuleap\NeverThrow\Result;
use Tuleap\Tracker\Artifact\Changeset\PostCreation\CalendarEvent\CalendarEventData;
use Tuleap\Tracker\Artifact\Changeset\PostCreation\CalendarEvent\RetrieveEventDates;

final class RetrieveEventDatesStub implements RetrieveEventDates
{
    private bool $time_displayed = false;

    /**
     * @param non-falsy-string|null $error
     */
    private function __construct(private readonly ?int $start, private readonly int $end, private readonly ?string $error)
    {
    }

    public static function shouldNotBeCalled(): self
    {
        return new self(null, 0, null);
    }

    public static function withDates(int $start, int $end): self
    {
        return new self($start, $end, null);
    }

    public function withIsTimeDisplayed(bool $time_displayed): self
    {
        $this->time_displayed = $time_displayed;

        return $this;
    }

    /**
     * @param non-falsy-string $message
     */
    public static function withError(string $message): self
    {
        return new self(0, 0, $message);
    }

    /**
     * @return Ok<CalendarEventData>|Err<non-falsy-string>
     */
    #[\Override]
    public function retrieveEventDates(
        CalendarEventData $calendar_event_data,
        \Tracker_Artifact_Changeset $changeset,
        \PFUser $recipient,
        \Psr\Log\LoggerInterface $logger,
        bool $should_check_permissions,
    ): Ok|Err {
        if ($this->start === null) {
            throw new \Exception('Should not have been called');
        }

        if ($this->error !== null) {
            return Result::err($this->error);
        }

        return Result::ok($calendar_event_data->withDates($this->start, $this->end)->withTimeDisplayed($this->time_displayed));
    }
}
