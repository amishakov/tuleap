<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\CrossTracker\Report;

use ForgeConfig;
use PFUser;
use Tracker;
use Tuleap\Config\ConfigKey;
use Tuleap\Config\ConfigKeyInt;
use Tuleap\CrossTracker\CrossTrackerArtifactReportDao;
use Tuleap\CrossTracker\CrossTrackerInstrumentation;
use Tuleap\CrossTracker\CrossTrackerReport;
use Tuleap\CrossTracker\Report\Query\Advanced\ExpertQueryIsEmptyException;
use Tuleap\CrossTracker\Report\Query\Advanced\InvalidOrderByBuilder;
use Tuleap\CrossTracker\Report\Query\Advanced\InvalidSearchablesCollectionBuilder;
use Tuleap\CrossTracker\Report\Query\Advanced\InvalidSelectablesCollectionBuilder;
use Tuleap\CrossTracker\Report\Query\Advanced\InvalidSelectablesCollectorVisitor;
use Tuleap\CrossTracker\Report\Query\Advanced\InvalidTermCollectorVisitor;
use Tuleap\CrossTracker\Report\Query\Advanced\OrderByBuilderVisitor;
use Tuleap\CrossTracker\Report\Query\Advanced\QueryBuilder\CrossTrackerExpertQueryReportDao;
use Tuleap\CrossTracker\Report\Query\Advanced\QueryBuilderVisitor;
use Tuleap\CrossTracker\Report\Query\Advanced\QueryValidation\DuckTypedField\DuckTypedFieldChecker;
use Tuleap\CrossTracker\Report\Query\Advanced\QueryValidation\Metadata\MetadataChecker;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\SelectedValueRepresentation;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilder\SelectedValuesCollection;
use Tuleap\CrossTracker\Report\Query\Advanced\ResultBuilderVisitor;
use Tuleap\CrossTracker\Report\Query\Advanced\SelectBuilderVisitor;
use Tuleap\CrossTracker\REST\v1\Representation\CrossTrackerReportContentRepresentation;
use Tuleap\CrossTracker\REST\v1\Representation\CrossTrackerSelectedRepresentation;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Artifact\RetrieveArtifact;
use Tuleap\Tracker\Permission\ArtifactPermissionType;
use Tuleap\Tracker\Permission\RetrieveUserPermissionOnArtifacts;
use Tuleap\Tracker\Report\Query\Advanced\ExpertQueryValidator;
use Tuleap\Tracker\Report\Query\Advanced\FromIsInvalidException;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\Metadata;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\Query;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\SyntaxError;
use Tuleap\Tracker\Report\Query\Advanced\LimitSizeIsExceededException;
use Tuleap\Tracker\Report\Query\Advanced\MissingFromException;
use Tuleap\Tracker\Report\Query\Advanced\OrderByIsInvalidException;
use Tuleap\Tracker\Report\Query\Advanced\ParserCacheProxy;
use Tuleap\Tracker\Report\Query\Advanced\SearchablesAreInvalidException;
use Tuleap\Tracker\Report\Query\Advanced\SearchablesDoNotExistException;
use Tuleap\Tracker\Report\Query\Advanced\SelectablesAreInvalidException;
use Tuleap\Tracker\Report\Query\Advanced\SelectablesDoNotExistException;
use Tuleap\Tracker\Report\Query\Advanced\SelectablesMustBeUniqueException;
use Tuleap\Tracker\Report\Query\Advanced\SelectLimitExceededException;
use Tuleap\Tracker\REST\v1\ArtifactMatchingReportCollection;

final readonly class CrossTrackerArtifactReportFactory
{
    #[ConfigKey('Configure the maximum quantity of tracker a cross tracker search expert query can use (default to 0 for no limit)')]
    #[ConfigKeyInt(0)]
    public const MAX_TRACKER_FROM = 'crosstracker_maximum_tracker_get_from';

    #[ConfigKey('Configure the maximum quantity of column a cross tracker search expert query can select (default to 0 for no limit)')]
    #[ConfigKeyInt(0)]
    public const MAX_SELECT = 'crosstracker_maximum_selected_columns';

    public function __construct(
        private CrossTrackerArtifactReportDao $artifact_report_dao,
        private RetrieveArtifact $artifact_factory,
        private ExpertQueryValidator $expert_query_validator,
        private QueryBuilderVisitor $query_builder,
        private SelectBuilderVisitor $select_builder,
        private OrderByBuilderVisitor $order_builder,
        private ResultBuilderVisitor $result_builder,
        private ParserCacheProxy $parser,
        private CrossTrackerExpertQueryReportDao $expert_query_dao,
        private InvalidTermCollectorVisitor $term_collector,
        private InvalidSelectablesCollectorVisitor $selectables_collector,
        private DuckTypedFieldChecker $field_checker,
        private MetadataChecker $metadata_checker,
        private RetrieveReportTrackers $report_trackers_retriever,
        private CrossTrackerInstrumentation $instrumentation,
        private RetrieveUserPermissionOnArtifacts $permission_on_artifacts_retriever,
        private RetrieveArtifact $artifact_retriever,
    ) {
    }

    /**
     * @throws ExpertQueryIsEmptyException
     * @throws FromIsInvalidException
     * @throws LimitSizeIsExceededException
     * @throws MissingFromException
     * @throws OrderByIsInvalidException
     * @throws SearchablesAreInvalidException
     * @throws SearchablesDoNotExistException
     * @throws SelectLimitExceededException
     * @throws SelectablesAreInvalidException
     * @throws SelectablesDoNotExistException
     * @throws SelectablesMustBeUniqueException
     * @throws SyntaxError
     */
    public function getArtifactsMatchingReport(
        CrossTrackerReport $report,
        PFUser $current_user,
        int $limit,
        int $offset,
    ): ArtifactMatchingReportCollection|CrossTrackerReportContentRepresentation {
        if ($report->getExpertQuery() === '') {
            if ($report->isExpert()) {
                throw new ExpertQueryIsEmptyException();
            }

            return $this->getArtifactsFromGivenTrackers(
                $this->getOnlyActiveTrackersInActiveProjects($report->getTrackers()),
                $limit,
                $offset,
                $current_user,
            );
        }

        if ($report->isExpert()) {
            return $this->getArtifactsMatchingExpertQuery(
                $report,
                $current_user,
                $limit,
                $offset,
            );
        }
        return $this->getArtifactsMatchingDefaultQuery(
            $report,
            $current_user,
            $limit,
            $offset
        );
    }

    /**
     * @param Tracker[] $trackers
     */
    private function getArtifactsFromGivenTrackers(array $trackers, int $limit, int $offset, PFUser $current_user): ArtifactMatchingReportCollection
    {
        if (count($trackers) === 0) {
            return new ArtifactMatchingReportCollection([], 0);
        }
        $this->instrumentation->updateTrackerCount(count($trackers));

        $trackers_id = $this->getTrackersId($trackers);

        $result     = $this->artifact_report_dao->searchArtifactsFromTracker($trackers_id, $limit, $offset);
        $total_size = $this->artifact_report_dao->foundRows();
        return $this->buildCollectionOfArtifacts($result, $total_size, $current_user);
    }

    /**
     * @throws SearchablesAreInvalidException
     * @throws SearchablesDoNotExistException
     * @throws SyntaxError
     * @throws SelectablesDoNotExistException
     * @throws SelectablesAreInvalidException
     * @throws OrderByIsInvalidException
     */
    private function getArtifactsMatchingDefaultQuery(
        CrossTrackerReport $report,
        PFUser $current_user,
        int $limit,
        int $offset,
    ): ArtifactMatchingReportCollection {
        $trackers = $this->getOnlyActiveTrackersInActiveProjects($report->getTrackers());
        $this->instrumentation->updateTrackerCount(count($trackers));
        $query                 = $this->getQueryFromReport($report, $current_user, $trackers);
        $condition             = $query->getCondition();
        $additional_from_where = $this->query_builder->buildFromWhere($condition, $trackers, $current_user);
        $results               = $this->expert_query_dao->searchArtifactsMatchingQuery(
            $additional_from_where,
            $this->getTrackersId($trackers),
            $limit,
            $offset
        );
        $total_size            = $this->expert_query_dao->foundRows();
        return $this->buildCollectionOfArtifacts($results, $total_size, $current_user);
    }

    /**
     * @throws FromIsInvalidException
     * @throws LimitSizeIsExceededException
     * @throws MissingFromException
     * @throws OrderByIsInvalidException
     * @throws SearchablesAreInvalidException
     * @throws SearchablesDoNotExistException
     * @throws SelectLimitExceededException
     * @throws SelectablesAreInvalidException
     * @throws SelectablesDoNotExistException
     * @throws SelectablesMustBeUniqueException
     * @throws SyntaxError
     */
    private function getArtifactsMatchingExpertQuery(
        CrossTrackerReport $report,
        PFUser $current_user,
        int $limit,
        int $offset,
    ): CrossTrackerReportContentRepresentation {
        $trackers = $this->report_trackers_retriever->getReportTrackers($report, $current_user, ForgeConfig::getInt(self::MAX_TRACKER_FROM));
        if ($trackers === []) {
            throw new FromIsInvalidException([dgettext('tuleap-crosstracker', 'No tracker found')]);
        }
        $this->instrumentation->updateTrackerCount(count($trackers));

        $query = $this->getQueryFromReport($report, $current_user, $trackers);
        if ($query->getOrderBy() !== null) {
            $this->instrumentation->updateOrderByUsage();
        }

        $additional_from_where = $this->query_builder->buildFromWhere($query->getCondition(), $trackers, $current_user);
        $additional_from_order = $this->order_builder->buildFromOrder($query->getOrderBy(), $trackers, $current_user);
        $artifact_ids          = $this->expert_query_dao->searchArtifactsIdsMatchingQuery(
            $additional_from_where,
            $additional_from_order,
            $this->getTrackersId($trackers),
            $limit,
            $offset
        );
        $artifact_ids          = array_map(
            static fn(Artifact $artifact) => $artifact->getId(),
            $this->permission_on_artifacts_retriever->retrieveUserPermissionOnArtifacts(
                $current_user,
                $this->getArtifacts($artifact_ids),
                ArtifactPermissionType::PERMISSION_VIEW,
            )->allowed,
        );

        if ($artifact_ids === []) {
            return new CrossTrackerReportContentRepresentation([], [], 0);
        }

        $total_size = $this->expert_query_dao->countArtifactsMatchingQuery(
            $additional_from_where,
            $this->getTrackersId($trackers),
        );

        $this->instrumentation->updateSelectCount(count($query->getSelect()));
        $additional_select_from = $this->select_builder->buildSelectFrom($query->getSelect(), $trackers, $current_user);
        $select_results         = $this->expert_query_dao->searchArtifactsColumnsMatchingIds(
            $additional_select_from,
            $additional_from_order,
            array_values($artifact_ids),
        );

        $results = $this->result_builder->buildResult([new Metadata('artifact'), ...$query->getSelect()], $trackers, $current_user, $select_results);
        return $this->buildReportContentRepresentation($results, $total_size);
    }

    /**
     * @param Tracker[] $trackers
     * @throws LimitSizeIsExceededException
     * @throws OrderByIsInvalidException
     * @throws SearchablesAreInvalidException
     * @throws SearchablesDoNotExistException
     * @throws SelectLimitExceededException
     * @throws SelectablesAreInvalidException
     * @throws SelectablesDoNotExistException
     * @throws SelectablesMustBeUniqueException
     * @throws SyntaxError
     */
    private function getQueryFromReport(
        CrossTrackerReport $report,
        PFUser $current_user,
        array $trackers,
    ): Query {
        $expert_query = $report->getExpertQuery();
        $this->expert_query_validator->validateExpertQuery(
            $expert_query,
            $report->isExpert(),
            new InvalidSearchablesCollectionBuilder($this->term_collector, $trackers, $current_user),
            new InvalidSelectablesCollectionBuilder($this->selectables_collector, $trackers, $current_user),
            new InvalidOrderByBuilder($this->field_checker, $this->metadata_checker, $trackers, $current_user),
        );
        return $this->parser->parse($expert_query);
    }

    /**
     * @param Tracker[] $trackers
     * @return Tracker[]
     */
    private function getOnlyActiveTrackersInActiveProjects(array $trackers): array
    {
        return array_filter($trackers, static fn(Tracker $tracker) => $tracker->isActive() && $tracker->getProject()->isActive());
    }

    /**
     * @param Tracker[] $trackers
     * @return int[]
     */
    private function getTrackersId(array $trackers): array
    {
        $id = [];

        foreach ($trackers as $tracker) {
            $id[] = $tracker->getId();
        }

        return $id;
    }

    /**
     * @param list<array{id: int}> $artifact_ids
     * @return list<Artifact>
     */
    private function getArtifacts(array $artifact_ids): array
    {
        $artifacts = [];
        foreach ($artifact_ids as $row) {
            $artifact = $this->artifact_retriever->getArtifactById($row['id']);
            if ($artifact !== null) {
                $artifacts[] = $artifact;
            }
        }
        return $artifacts;
    }

    private function buildCollectionOfArtifacts(array $results, int $total_size, PFUser $current_user): ArtifactMatchingReportCollection
    {
        $artifacts = [];
        foreach ($results as $artifact) {
            $artifact = $this->artifact_factory->getArtifactById($artifact['id']);
            if ($artifact && ! in_array($artifact, $artifacts, true)) {
                $artifacts[] = $artifact;
            }
        }

        return new ArtifactMatchingReportCollection(
            $this->permission_on_artifacts_retriever->retrieveUserPermissionOnArtifacts(
                $current_user,
                $artifacts,
                ArtifactPermissionType::PERMISSION_VIEW,
            )->allowed,
            $total_size,
        );
    }

    /**
     * @param SelectedValuesCollection[] $results
     */
    private function buildReportContentRepresentation(array $results, int $total_size): CrossTrackerReportContentRepresentation
    {
        /** @var CrossTrackerSelectedRepresentation[] $selected */
        $selected = [];
        /** @var array<int, array<string, SelectedValueRepresentation>> $artifacts */
        $artifacts = [];

        foreach ($results as $result) {
            if ($result->selected === null) {
                continue;
            }
            $selected[] = $result->selected;
            foreach ($result->values as $id => $value) {
                if (! isset($artifacts[$id])) {
                    $artifacts[$id] = [];
                }
                $artifacts[$id][$value->name] = $value->value;
            }
        }

        return new CrossTrackerReportContentRepresentation(array_values($artifacts), $selected, $total_size);
    }
}
