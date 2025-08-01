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

namespace Tuleap\Artidoc\Document\Field\Numeric;

use Tracker_Artifact_ChangesetValue_Numeric;
use Tuleap\Tracker\FormElement\Field\ArtifactId\ArtifactIdField;
use Tuleap\Tracker\FormElement\Field\PerTrackerArtifactId\PerTrackerArtifactIdField;
use Tuleap\Artidoc\Document\Field\ConfiguredField;
use Tuleap\Artidoc\Domain\Document\Section\Field\FieldWithValue\NumericFieldWithValue;
use Tuleap\Option\Option;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Artifact\Dao\SearchArtifactGlobalRank;
use Tuleap\Tracker\FormElement\Field\Priority\PriorityField;

final readonly class NumericFieldWithValueBuilder
{
    public function __construct(
        private SearchArtifactGlobalRank $search_artifact_global_rank,
    ) {
    }

    public function buildNumericFieldWithValue(
        ConfiguredField $configured_field,
        Artifact $artifact,
        ?Tracker_Artifact_ChangesetValue_Numeric $changeset_value,
    ): NumericFieldWithValue {
        return new NumericFieldWithValue(
            $configured_field->field->getLabel(),
            $configured_field->display_type,
            $this->buildValue($configured_field, $artifact, $changeset_value),
        );
    }

    /**
     * @return Option<int> | Option<float>
     */
    private function buildValue(
        ConfiguredField $configured_field,
        Artifact $artifact,
        ?Tracker_Artifact_ChangesetValue_Numeric $changeset_value,
    ): Option {
        if ($configured_field->field instanceof PerTrackerArtifactIdField) {
            return Option::fromValue($artifact->getPerTrackerArtifactId());
        }
        if ($configured_field->field instanceof ArtifactIdField) {
            return Option::fromValue($artifact->getId());
        }

        if ($configured_field->field instanceof PriorityField) {
            return Option::fromNullable($this->search_artifact_global_rank->getGlobalRank($artifact->getId()));
        }

        return Option::fromNullable($changeset_value?->getNumeric());
    }
}
