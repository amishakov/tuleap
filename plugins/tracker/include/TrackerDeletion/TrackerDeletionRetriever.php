<?php
/*
 * Copyright (c) Enalean, 2025 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\TrackerDeletion;

use Tuleap\Tracker\RetrieveTracker;
use Tuleap\Tracker\Tracker;

final class TrackerDeletionRetriever
{
    public function __construct(private RetrieveDeletedTracker $dao, private RetrieveTracker $tracker_factory)
    {
    }

    /**
     * @return Tracker[]
     */
    public function getDeletedTrackers(): array
    {
        $pending_trackers = $this->dao->retrieveTrackersMarkAsDeleted();
        $deleted_trackers = [];

        if (! $pending_trackers) {
            return [];
        }

        foreach ($pending_trackers as $pending_tracker) {
            $tracker = $this->tracker_factory->getTrackerById($pending_tracker['id']);
            if ($tracker) {
                $deleted_trackers[] = $tracker;
            }
        }

        return $deleted_trackers;
    }
}
