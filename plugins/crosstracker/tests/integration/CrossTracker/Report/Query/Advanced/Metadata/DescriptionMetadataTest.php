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

namespace Tuleap\CrossTracker\Report\Query\Advanced\Metadata;

use PFUser;
use ProjectUGroup;
use Tracker;
use Tuleap\CrossTracker\CrossTrackerDefaultReport;
use Tuleap\CrossTracker\Report\Query\Advanced\CrossTrackerFieldTestCase;
use Tuleap\CrossTracker\Tests\Report\ArtifactReportFactoryInstantiator;
use Tuleap\DB\DBFactory;
use Tuleap\Test\Builders\CoreDatabaseBuilder;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Report\Query\Advanced\SearchablesAreInvalidException;
use Tuleap\Tracker\Report\Query\Advanced\SearchablesDoNotExistException;
use Tuleap\Tracker\REST\v1\ArtifactMatchingReportCollection;
use Tuleap\Tracker\Test\Builders\TrackerDatabaseBuilder;

final class DescriptionMetadataTest extends CrossTrackerFieldTestCase
{
    private PFUser $project_member;
    private PFUser $project_admin;
    private Tracker $release_tracker;
    private Tracker $sprint_tracker;
    private Tracker $task_tracker;
    private int $release_artifact_empty_id;
    private int $release_artifact_with_description_id;
    private int $release_artifact_with_description_2_id;
    private int $sprint_artifact_empty_id;
    private int $sprint_artifact_with_description_id;
    private int $task_artifact_with_description_id;

    protected function setUp(): void
    {
        $db              = DBFactory::getMainTuleapDBConnection()->getDB();
        $tracker_builder = new TrackerDatabaseBuilder($db);
        $core_builder    = new CoreDatabaseBuilder($db);

        $project              = $core_builder->buildProject('project_name');
        $project_id           = (int) $project->getID();
        $this->project_member = $core_builder->buildUser('project_member', 'Project Member', 'project_member@example.com');
        $this->project_admin  = $core_builder->buildUser('project_admin', 'Project Admin', 'project_admin@example.com');
        $core_builder->addUserToProjectMembers((int) $this->project_member->getId(), $project_id);
        $core_builder->addUserToProjectMembers((int) $this->project_admin->getId(), $project_id);
        $core_builder->addUserToProjectAdmins((int) $this->project_admin->getId(), $project_id);

        $this->release_tracker = $tracker_builder->buildTracker($project_id, 'Release');
        $this->sprint_tracker  = $tracker_builder->buildTracker($project_id, 'Sprint');
        $this->task_tracker    = $tracker_builder->buildTracker($project_id, 'Task');
        $tracker_builder->setViewPermissionOnTracker($this->release_tracker->getId(), Tracker::PERMISSION_FULL, ProjectUGroup::PROJECT_MEMBERS);
        $tracker_builder->setViewPermissionOnTracker($this->sprint_tracker->getId(), Tracker::PERMISSION_FULL, ProjectUGroup::PROJECT_MEMBERS);
        $tracker_builder->setViewPermissionOnTracker($this->task_tracker->getId(), Tracker::PERMISSION_FULL, ProjectUGroup::PROJECT_MEMBERS);

        $release_description_field_id = $tracker_builder->buildTextField(
            $this->release_tracker->getId(),
            'release_description'
        );
        $sprint_description_field_id  = $tracker_builder->buildTextField(
            $this->sprint_tracker->getId(),
            'sprint_description',
        );
        $task_description_field_id    = $tracker_builder->buildTextField(
            $this->task_tracker->getId(),
            'task_description',
        );

        $tracker_builder->buildDescriptionSemantic($this->release_tracker->getId(), $release_description_field_id);
        $tracker_builder->buildDescriptionSemantic($this->sprint_tracker->getId(), $sprint_description_field_id);
        $tracker_builder->buildDescriptionSemantic($this->task_tracker->getId(), $task_description_field_id);

        $tracker_builder->grantReadPermissionOnField(
            $release_description_field_id,
            ProjectUGroup::PROJECT_MEMBERS
        );
        $tracker_builder->grantReadPermissionOnField(
            $sprint_description_field_id,
            ProjectUGroup::PROJECT_MEMBERS
        );
        $tracker_builder->grantReadPermissionOnField(
            $task_description_field_id,
            ProjectUGroup::PROJECT_ADMIN
        );

        $this->release_artifact_empty_id              = $tracker_builder->buildArtifact($this->release_tracker->getId());
        $this->release_artifact_with_description_id   = $tracker_builder->buildArtifact($this->release_tracker->getId());
        $this->release_artifact_with_description_2_id = $tracker_builder->buildArtifact($this->release_tracker->getId());
        $this->sprint_artifact_empty_id               = $tracker_builder->buildArtifact($this->sprint_tracker->getId());
        $this->sprint_artifact_with_description_id    = $tracker_builder->buildArtifact($this->sprint_tracker->getId());
        $this->task_artifact_with_description_id      = $tracker_builder->buildArtifact($this->task_tracker->getId());

        $release_empty_changeset              = $tracker_builder->buildLastChangeset($this->release_artifact_empty_id);
        $release_with_description_changeset   = $tracker_builder->buildLastChangeset($this->release_artifact_with_description_id);
        $release_with_description_2_changeset = $tracker_builder->buildLastChangeset($this->release_artifact_with_description_2_id);
        $sprint_empty_changeset               = $tracker_builder->buildLastChangeset($this->sprint_artifact_empty_id);
        $sprint_with_description_changeset    = $tracker_builder->buildLastChangeset($this->sprint_artifact_with_description_id);
        $task_with_description_changeset      = $tracker_builder->buildLastChangeset($this->task_artifact_with_description_id);

        $tracker_builder->buildTextValue($release_empty_changeset, $release_description_field_id, '', 'text');
        $tracker_builder->buildTextValue($release_with_description_changeset, $release_description_field_id, 'description', 'text');
        $tracker_builder->buildTextValue($release_with_description_2_changeset, $release_description_field_id, 'a very long long text', 'text');
        $tracker_builder->buildTextValue($sprint_empty_changeset, $sprint_description_field_id, '', 'text');
        $tracker_builder->buildTextValue($sprint_with_description_changeset, $sprint_description_field_id, 'description', 'text');
        $tracker_builder->buildTextValue($task_with_description_changeset, $task_description_field_id, 'description', 'text');
    }

    /**
     * @return list<int>
     * @throws SearchablesDoNotExistException
     * @throws SearchablesAreInvalidException
     */
    private function getMatchingArtifactIds(CrossTrackerDefaultReport $report, PFUser $user): array
    {
        $result = (new ArtifactReportFactoryInstantiator())
            ->getFactory()
            ->getArtifactsMatchingReport($report, $user, 10, 0);
        assert($result instanceof ArtifactMatchingReportCollection);
        return array_values(array_map(static fn(Artifact $artifact) => $artifact->getId(), $result->getArtifacts()));
    }

    public function testEqualEmpty(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description = ''",
                [$this->release_tracker, $this->sprint_tracker],
            ),
            $this->project_member
        );

        self::assertCount(2, $artifacts);
        self::assertEqualsCanonicalizing([$this->release_artifact_empty_id, $this->sprint_artifact_empty_id], $artifacts);
    }

    public function testEqualValue(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description = 'description'",
                [$this->release_tracker, $this->sprint_tracker, $this->task_tracker],
            ),
            $this->project_member
        );

        self::assertCount(2, $artifacts);
        self::assertEqualsCanonicalizing([$this->release_artifact_with_description_id, $this->sprint_artifact_with_description_id], $artifacts);
    }

    public function testPermissionsEqual(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description = 'description'",
                [$this->release_tracker, $this->sprint_tracker, $this->task_tracker],
            ),
            $this->project_admin
        );

        self::assertCount(3, $artifacts);
        self::assertEqualsCanonicalizing([$this->release_artifact_with_description_id, $this->sprint_artifact_with_description_id, $this->task_artifact_with_description_id], $artifacts);
    }

    public function testMultipleEqual(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description = 'description' OR @description = 'long'",
                [$this->release_tracker, $this->sprint_tracker],
            ),
            $this->project_member
        );

        self::assertCount(3, $artifacts);
        self::assertEqualsCanonicalizing([$this->release_artifact_with_description_id, $this->release_artifact_with_description_2_id, $this->sprint_artifact_with_description_id], $artifacts);
    }

    public function testNotEqualEmpty(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description != ''",
                [$this->release_tracker, $this->sprint_tracker],
            ),
            $this->project_member
        );

        self::assertCount(3, $artifacts);
        self::assertEqualsCanonicalizing([$this->release_artifact_with_description_id, $this->release_artifact_with_description_2_id, $this->sprint_artifact_with_description_id], $artifacts);
    }

    public function testNotEqualValue(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description != 'long'",
                [$this->release_tracker, $this->sprint_tracker, $this->task_tracker],
            ),
            $this->project_member
        );

        self::assertCount(4, $artifacts);
        self::assertEqualsCanonicalizing([
            $this->release_artifact_empty_id, $this->release_artifact_with_description_id,
            $this->sprint_artifact_empty_id, $this->sprint_artifact_with_description_id,
        ], $artifacts);
    }

    public function testPermissionsNotEqual(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description != 'long'",
                [$this->release_tracker, $this->sprint_tracker, $this->task_tracker],
            ),
            $this->project_admin
        );

        self::assertCount(5, $artifacts);
        self::assertEqualsCanonicalizing([
            $this->release_artifact_empty_id, $this->release_artifact_with_description_id,
            $this->sprint_artifact_empty_id, $this->sprint_artifact_with_description_id,
            $this->task_artifact_with_description_id,
        ], $artifacts);
    }

    public function testMultipleNotEqual(): void
    {
        $artifacts = $this->getMatchingArtifactIds(
            new CrossTrackerDefaultReport(
                1,
                "@description != 'long' AND @description != ''",
                [$this->release_tracker, $this->sprint_tracker],
            ),
            $this->project_member
        );

        self::assertCount(2, $artifacts);
        self::assertEqualsCanonicalizing([$this->release_artifact_with_description_id, $this->sprint_artifact_with_description_id], $artifacts);
    }
}
