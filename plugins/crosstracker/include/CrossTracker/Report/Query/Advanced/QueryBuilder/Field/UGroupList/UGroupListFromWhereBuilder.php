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

namespace Tuleap\CrossTracker\Report\Query\Advanced\QueryBuilder\Field\UGroupList;

use LogicException;
use ParagonIE\EasyDB\EasyStatement;
use Tracker_FormElement_Field_List;
use Tuleap\CrossTracker\Report\Query\Advanced\DuckTypedField\DuckTypedField;
use Tuleap\CrossTracker\Report\Query\Advanced\QueryBuilder\Field\FieldValueWrapperParameters;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\BetweenValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\Comparison;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\ComparisonType;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\CurrentDateTimeValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\CurrentUserValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\InValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\SimpleValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\StatusOpenValueWrapper;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\ValueWrapperVisitor;
use Tuleap\Tracker\Report\Query\Advanced\UgroupLabelConverter;
use Tuleap\Tracker\Report\Query\IProvideParametrizedFromAndWhereSQLFragments;
use Tuleap\Tracker\Report\Query\ParametrizedFromWhere;

/**
 * @template-implements ValueWrapperVisitor<FieldValueWrapperParameters, ParametrizedFromWhere>
 */
final readonly class UGroupListFromWhereBuilder implements ValueWrapperVisitor
{
    public function __construct(
        private UgroupLabelConverter $label_converter,
    ) {
    }

    public function getFromWhere(
        DuckTypedField $duck_typed_field,
        Comparison $comparison,
    ): IProvideParametrizedFromAndWhereSQLFragments {
        $suffix              = spl_object_hash($comparison);
        $tracker_field_alias = "TF_$suffix";
        $filter_alias        = $this->getAliasForFilter($comparison);

        $fields_id_statement        = EasyStatement::open()->in(
            "$tracker_field_alias.id IN(?*)",
            $duck_typed_field->field_ids
        );
        $filter_field_ids_statement = EasyStatement::open()->in(
            'tcv.field_id IN(?*)',
            $duck_typed_field->field_ids
        );

        $from_where = $comparison->getValueWrapper()->accept(
            $this,
            new FieldValueWrapperParameters($comparison)
        );
        $from       = <<<EOSQL
        INNER JOIN tracker_field AS $tracker_field_alias
            ON (tracker.id = $tracker_field_alias.tracker_id AND $fields_id_statement)
        LEFT JOIN (
            SELECT c.artifact_id AS artifact_id
            FROM tracker_artifact AS artifact
            INNER JOIN tracker_changeset AS c ON (artifact.last_changeset_id = c.id)
            INNER JOIN ({$from_where->getFrom()})
                ON (tcv.changeset_id = c.id AND $filter_field_ids_statement)
        ) AS $filter_alias ON (tracker_artifact.id = $filter_alias.artifact_id)
        EOSQL;
        return new ParametrizedFromWhere(
            $from,
            $from_where->getWhere(),
            array_merge(
                $fields_id_statement->values(),
                $from_where->getFromParameters(),
                $filter_field_ids_statement->values()
            ),
            $from_where->getWhereParameters()
        );
    }

    private function getAliasForFilter(Comparison $comparison): string
    {
        $suffix = spl_object_hash($comparison);
        return "FA_$suffix";
    }

    public function visitSimpleValueWrapper(SimpleValueWrapper $value_wrapper, $parameters)
    {
        $comparison   = $parameters->comparison;
        $filter_alias = $this->getAliasForFilter($comparison);

        return match ($comparison->getType()) {
            ComparisonType::Equal    => $this->getWhereForEqual($filter_alias, $value_wrapper),
            ComparisonType::NotEqual => throw new LogicException('Not implemented yet'),
            ComparisonType::In,
            ComparisonType::NotIn    => throw new LogicException('In comparison expected a InValueWrapper, not a SimpleValueWrapper'),
            default                  => throw new LogicException('Other comparison types are invalid for UGroup List field')
        };
    }

    private function getWhereForEqual(
        string $filter_alias,
        SimpleValueWrapper $wrapper,
    ): ParametrizedFromWhere {
        $value = $wrapper->getValue();

        if ($value === '') {
            $from = <<<EOSQL
            tracker_changeset_value AS tcv
            INNER JOIN tracker_changeset_value_list AS tcvl ON (
                tcvl.changeset_value_id = tcv.id AND tcvl.bindvalue_id = ?
            )
            EOSQL;

            return new ParametrizedFromWhere(
                $from,
                "$filter_alias.artifact_id IS NOT NULL",
                [Tracker_FormElement_Field_List::NONE_VALUE],
                []
            );
        }

        if ($this->label_converter->isASupportedDynamicUgroup($value)) {
            $value = $this->label_converter->convertLabelToTranslationKey($value);
        }

        $from = <<<EOSQL
        tracker_changeset_value AS tcv
        INNER JOIN tracker_changeset_value_list AS tcvl ON (
            tcvl.changeset_value_id = tcv.id
        )
        INNER JOIN tracker_field_list_bind_ugroups_value AS tflbuv ON (
            tflbuv.id = tcvl.bindvalue_id
        )
        INNER JOIN ugroup ON (
            tflbuv.ugroup_id = ugroup.ugroup_id AND ugroup.name = ?
        )
        EOSQL;


        return new ParametrizedFromWhere(
            $from,
            "$filter_alias.artifact_id IS NOT NULL",
            [$value],
            []
        );
    }

    public function visitInValueWrapper(InValueWrapper $collection_of_value_wrappers, $parameters)
    {
        throw new LogicException('Not implemented yet');
    }

    public function visitStatusOpenValueWrapper(StatusOpenValueWrapper $value_wrapper, $parameters)
    {
        throw new LogicException('Comparison to status open should have been flagged as invalid for UGroup List fields');
    }

    public function visitCurrentDateTimeValueWrapper(CurrentDateTimeValueWrapper $value_wrapper, $parameters)
    {
        throw new LogicException('Comparison to current date time should have been flagged as invalid for UGroup List fields');
    }

    public function visitBetweenValueWrapper(BetweenValueWrapper $value_wrapper, $parameters)
    {
        throw new LogicException('Comparison with Between() should have been flagged as invalid for UGroup List fields');
    }

    public function visitCurrentUserValueWrapper(CurrentUserValueWrapper $value_wrapper, $parameters)
    {
        throw new LogicException('Comparison to current user should have been flagged as invalid for UGroup List fields');
    }
}