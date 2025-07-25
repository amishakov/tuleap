<?php
/**
 * Copyright (c) Enalean, 2025-Present. All Rights Reserved.
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

namespace Tuleap\Tracker\FormElement\Field\ArtifactLink;

use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Type\TypePresenter;
use Tuleap\Tracker\Tracker;
use function Psl\Json\encode;

/**
 * @psalm-immutable
 */
final readonly class EditorWithReverseLinksPresenter
{
    public int $link_field_id;
    public string $link_field_label;
    public ?int $current_artifact_id;
    public int $current_tracker_id;
    public string $current_tracker_color;
    public string $current_tracker_short_name;
    public int $current_project_id;
    public ?int $parent_tracker_id;
    public int $user_id;
    public string $allowed_link_types;
    public string $initial_value;

    /**
     * @param list<TypePresenter> $allowed_link_types
     */
    public function __construct(
        ArtifactLinkField $link_field,
        ?Artifact $current_artifact,
        Tracker $current_tracker,
        ?Tracker $parent_tracker,
        array $allowed_link_types,
        string $initial_value,
    ) {
        $this->link_field_id              = $link_field->getId();
        $this->link_field_label           = $link_field->getLabel();
        $this->current_artifact_id        = $current_artifact?->getId();
        $this->current_tracker_id         = $current_tracker->getId();
        $this->current_tracker_color      = $current_tracker->getColor()->value;
        $this->current_tracker_short_name = $current_tracker->getItemName();
        $this->current_project_id         = (int) $current_tracker->getGroupId();
        $this->parent_tracker_id          = $parent_tracker?->getId();
        $this->allowed_link_types         = encode($allowed_link_types);
        $this->initial_value              = $initial_value;
    }
}
