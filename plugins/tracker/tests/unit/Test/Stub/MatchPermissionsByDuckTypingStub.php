<?php
/**
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
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

namespace Tuleap\Tracker\Test\Stub;

use Tracker_FormElement_Field_PermissionsOnArtifact;
use Tuleap\Tracker\FormElement\Field\PermissionsOnArtifact\MatchPermissionsByDuckTyping;

final class MatchPermissionsByDuckTypingStub implements MatchPermissionsByDuckTyping
{
    /**
     * @param string[] $user_groups_names
     */
    private function __construct(private readonly array $user_groups_names)
    {
    }

    /**
     * @param string[] $user_groups_names
     */
    public static function withUserGroupsInDestinationField(array $user_groups_names): self
    {
        return new self($user_groups_names);
    }

    #[\Override]
    public function doesUserGroupExistsInDestinationField(Tracker_FormElement_Field_PermissionsOnArtifact $destination_field, string $user_group_name): bool
    {
        return in_array($user_group_name, $this->user_groups_names, true);
    }
}
