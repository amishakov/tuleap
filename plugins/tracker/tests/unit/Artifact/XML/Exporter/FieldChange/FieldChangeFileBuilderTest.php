<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\Tracker\Artifact\XML\Exporter\FieldChange;

use SimpleXMLElement;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class FieldChangeFileBuilderTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private FieldChangeFileBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new FieldChangeFileBuilder();
    }

    public function testItBuildsTheFieldChangeNode(): void
    {
        $changeset_node = new SimpleXMLElement('<changeset/>');

        $this->builder->build(
            $changeset_node,
            'field_file_01',
            [1456]
        );

        $this->assertTrue(isset($changeset_node->field_change));
        $field_change_node = $changeset_node->field_change;

        self::assertSame('file', (string) $field_change_node['type']);
        self::assertSame('field_file_01', (string) $field_change_node['field_name']);
        self::assertSame('fileinfo_1456', (string) $field_change_node->value['ref']);
    }

    public function testItBuildsTheFieldChangeNodeWithValueAsNull(): void
    {
        $changeset_node = new SimpleXMLElement('<changeset/>');

        $this->builder->build(
            $changeset_node,
            'field_file_01',
            []
        );

        $this->assertTrue(isset($changeset_node->field_change));
        $field_change_node = $changeset_node->field_change;

        self::assertSame('file', (string) $field_change_node['type']);
        self::assertSame('field_file_01', (string) $field_change_node['field_name']);
        self::assertSame('', (string) $field_change_node->value);
    }
}
