<?php
/**
 * Copyright (c) Enalean, 2016 - present. All Rights Reserved.
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

namespace Tuleap\Tracker\Artifact\XMLImport;

use SimpleXMLElement;
use Tuleap\Tracker\FormElement\Field\Computed\ComputedField;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Artifact\Changeset\PostCreation\PostCreationContext;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;
use Tuleap\Tracker\Test\Builders\Fields\ComputedFieldBuilder;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class XMLImportFieldStrategyComputedTest extends TestCase
{
    public function testItShouldWorkWithAManualValue(): void
    {
        $field             = ComputedFieldBuilder::aComputedField(45)->build();
        $user              = UserTestBuilder::buildWithDefaults();
        $xml_change        = new SimpleXMLElement('<?xml version="1.0"?>
                  <field_change field_name="capacity" type="computed">
                    <manual_value>0</manual_value>
                  </field_change>');
        $strategy_computed = new XMLImportFieldStrategyComputed();

        $change_computed = $strategy_computed->getFieldData($field, $xml_change, $user, ArtifactTestBuilder::anArtifact(89)->build(), PostCreationContext::withNoConfig(false));
        $expected_result = [ComputedField::FIELD_VALUE_MANUAL => '0'];

        self::assertSame($expected_result, $change_computed);
    }

    public function testItShouldWorkWhenIsAutocomputed(): void
    {
        $field             = ComputedFieldBuilder::aComputedField(45)->build();
        $user              = UserTestBuilder::buildWithDefaults();
        $xml_change        = new SimpleXMLElement('<?xml version="1.0"?>
                  <field_change field_name="capacity" type="computed">
                    <is_autocomputed>1</is_autocomputed>
                  </field_change>');
        $strategy_computed = new XMLImportFieldStrategyComputed();

        $change_computed = $strategy_computed->getFieldData($field, $xml_change, $user, ArtifactTestBuilder::anArtifact(89)->build(), PostCreationContext::withNoConfig(false));
        $expected_result = [ComputedField::FIELD_VALUE_IS_AUTOCOMPUTED => '1'];

        self::assertSame($expected_result, $change_computed);
    }

    public function testItShouldWorkWithAManualValueAndIsAutocomputed(): void
    {
        $field             = ComputedFieldBuilder::aComputedField(45)->build();
        $user              = UserTestBuilder::buildWithDefaults();
        $xml_change        = new SimpleXMLElement('<?xml version="1.0"?>
                  <field_change field_name="capacity" type="computed">
                    <manual_value></manual_value>
                    <is_autocomputed>1</is_autocomputed>
                  </field_change>');
        $strategy_computed = new XMLImportFieldStrategyComputed();

        $change_computed = $strategy_computed->getFieldData($field, $xml_change, $user, ArtifactTestBuilder::anArtifact(89)->build(), PostCreationContext::withNoConfig(false));
        $expected_result = [
            ComputedField::FIELD_VALUE_MANUAL          => '',
            ComputedField::FIELD_VALUE_IS_AUTOCOMPUTED => '1',
        ];

        self::assertSame($expected_result, $change_computed);
    }
}
