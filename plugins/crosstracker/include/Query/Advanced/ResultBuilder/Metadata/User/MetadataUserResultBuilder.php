<?php
/**
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

namespace Tuleap\CrossTracker\Query\Advanced\ResultBuilder\Metadata\User;

use LogicException;
use Tuleap\CrossTracker\Query\Advanced\ResultBuilder\Representations\UserRepresentation;
use Tuleap\CrossTracker\Query\Advanced\ResultBuilder\SelectedValue;
use Tuleap\CrossTracker\Query\Advanced\ResultBuilder\SelectedValuesCollection;
use Tuleap\CrossTracker\REST\v1\Representation\CrossTrackerSelectedRepresentation;
use Tuleap\CrossTracker\REST\v1\Representation\CrossTrackerSelectedType;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\Metadata;
use Tuleap\User\RetrieveUserById;
use UserHelper;

final readonly class MetadataUserResultBuilder
{
    public function __construct(
        private RetrieveUserById $user_retriever,
        private UserHelper $user_helper,
    ) {
    }

    public function getResult(Metadata $metadata, array $select_results): SelectedValuesCollection
    {
        $values = [];
        $alias  = $metadata->getName();

        foreach ($select_results as $result) {
            $id = $result['id'];
            if (isset($values[$id])) {
                continue;
            }

            $value = $result[$alias];
            if (! is_int($value)) {
                continue;
            }
            $user = $this->user_retriever->getUserById($value);
            if ($user === null) {
                throw new LogicException("User $value not found");
            }
            $values[$id] = new SelectedValue($metadata->getName(), UserRepresentation::fromPFUser($user, $this->user_helper));
        }

        return new SelectedValuesCollection(
            new CrossTrackerSelectedRepresentation($metadata->getName(), CrossTrackerSelectedType::TYPE_USER),
            $values,
        );
    }
}
