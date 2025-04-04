<?php
/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

namespace Tuleap\Tracker\Creation\JiraImporter\Import\Reports;

use PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles;
use Psr\Log\NullLogger;
use SimpleXMLElement;
use Tracker_FormElement_Field_List_Bind_Static;
use Tracker_FormElementFactory;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\FieldMapping;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\ListFieldMapping;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\ScalarFieldMapping;
use Tuleap\Tracker\Creation\JiraImporter\Import\Values\StatusValuesCollection;
use Tuleap\Tracker\Test\Stub\Creation\JiraImporter\JiraClientStub;
use XML_SimpleXMLCDATAFactory;

#[DisableReturnValueGenerationForTestDoubles]
final class XmlReportCreatedRecentlyExporterTest extends TestCase
{
    private XmlReportCreatedRecentlyExporter $report_export;
    private SimpleXMLElement $reports_node;
    private FieldMapping $summary_field_mapping;
    private FieldMapping $description_field_mapping;
    private FieldMapping $status_field_mapping;
    private FieldMapping $priority_field_mapping;
    private FieldMapping $jira_issue_url_field_mapping;
    private FieldMapping $created_field_mapping;

    protected function setUp(): void
    {
        $this->summary_field_mapping = new ScalarFieldMapping(
            'summary',
            'Summary',
            null,
            'Fsummary',
            'summary',
            Tracker_FormElementFactory::FIELD_STRING_TYPE,
        );

        $this->description_field_mapping = new ScalarFieldMapping(
            'description',
            'Description',
            null,
            'Fdescription',
            'description',
            Tracker_FormElementFactory::FIELD_TEXT_TYPE,
        );

        $this->status_field_mapping = new ListFieldMapping(
            'status',
            'Status',
            null,
            'Fstatus',
            'status',
            Tracker_FormElementFactory::FIELD_SELECT_BOX_TYPE,
            Tracker_FormElement_Field_List_Bind_Static::TYPE,
            [],
        );

        $this->priority_field_mapping = new ListFieldMapping(
            'priority',
            'Priority',
            null,
            'Fpriority',
            'priority',
            Tracker_FormElementFactory::FIELD_SELECT_BOX_TYPE,
            Tracker_FormElement_Field_List_Bind_Static::TYPE,
            [],
        );

        $this->jira_issue_url_field_mapping = new ScalarFieldMapping(
            'jira_issue_url',
            'Link to original issue',
            null,
            'Fjira_issue_url',
            'jira_issue_url',
            Tracker_FormElementFactory::FIELD_STRING_TYPE,
        );

        $this->created_field_mapping = new ScalarFieldMapping(
            'created',
            'Created',
            null,
            'Fcreated',
            'created',
            Tracker_FormElementFactory::FIELD_DATE_TYPE,
        );

        $tracker_node        = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><trackers />');
        $this->reports_node  = $tracker_node->addChild('reports');
        $this->report_export = new XmlReportCreatedRecentlyExporter(
            new XmlTQLReportExporter(
                new XmlReportDefaultCriteriaExporter(),
                new XML_SimpleXMLCDATAFactory(),
                new XmlReportTableExporter()
            )
        );
    }

    public function testItDoesNotExportWhenNoCreatedField(): void
    {
        $this->report_export->exportJiraLikeReport(
            $this->reports_node,
            new StatusValuesCollection(JiraClientStub::aJiraClient(), new NullLogger()),
            $this->summary_field_mapping,
            $this->description_field_mapping,
            $this->status_field_mapping,
            $this->priority_field_mapping,
            $this->jira_issue_url_field_mapping,
            null,
            null,
            null,
        );

        $reports_node = $this->reports_node;
        self::assertEquals(0, $reports_node->count());
    }

    public function testItExportsAReportShowingIssuesCreatedBetweenNowAndLastWeek(): void
    {
        $this->report_export->exportJiraLikeReport(
            $this->reports_node,
            new StatusValuesCollection(JiraClientStub::aJiraClient(), new NullLogger()),
            $this->summary_field_mapping,
            $this->description_field_mapping,
            $this->status_field_mapping,
            $this->priority_field_mapping,
            $this->jira_issue_url_field_mapping,
            $this->created_field_mapping,
            null,
            null,
        );

        $reports_node = $this->reports_node;
        self::assertNotNull($reports_node);

        $report_node = $reports_node->report;
        self::assertNotNull($report_node);

        $report_node_name = $report_node->name;
        self::assertEquals('Created recently', $report_node_name);

        $reports_node_description = $report_node->description;
        self::assertEquals('All issues created recently in this tracker', $reports_node_description);
        self::assertEquals('0', $report_node['is_default']);
        self::assertEquals('1', $report_node['is_in_expert_mode']);
        self::assertEquals('created BETWEEN(NOW() - 1w, NOW())', (string) $report_node['expert_query']);

        $criterias = $report_node->criterias;
        self::assertNotNull($criterias);
        self::assertCount(4, $criterias->children());

        $criterion_01 = $criterias->criteria[0];
        self::assertSame('Fsummary', (string) $criterion_01->field['REF']);

        $criterion_02 = $criterias->criteria[1];
        self::assertSame('Fdescription', (string) $criterion_02->field['REF']);

        $criterion_03 = $criterias->criteria[2];
        self::assertSame('Fpriority', (string) $criterion_03->field['REF']);

        $criterion_04 = $criterias->criteria[3];
        self::assertSame('Fcreated', (string) $criterion_04->field['REF']);

        $renderers_node = $report_node->renderers;
        self::assertNotNull($renderers_node);

        $renderer_node = $renderers_node->renderer;
        self::assertNotNull($renderer_node);

        self::assertEquals('table', $renderer_node['type']);
        self::assertEquals('0', $renderer_node['rank']);
        self::assertEquals('15', $renderer_node['chunksz']);

        $renderer_name = $renderer_node->name;
        self::assertNotNull($renderer_name);

        $columns_node = $renderer_node->columns;
        self::assertNotNull($columns_node);
        self::assertCount(5, $columns_node->children());

        $field_01 = $columns_node->field[0];
        self::assertEquals('Fsummary', (string) $field_01['REF']);

        $field_02 = $columns_node->field[1];
        self::assertEquals('Fstatus', (string) $field_02['REF']);

        $field_03 = $columns_node->field[2];
        self::assertEquals('Fjira_issue_url', (string) $field_03['REF']);

        $field_04 = $columns_node->field[3];
        self::assertEquals('Fpriority', (string) $field_04['REF']);

        $field_05 = $columns_node->field[4];
        self::assertEquals('Fcreated', (string) $field_05['REF']);

        self::assertEquals('Results', (string) $renderer_name);
    }
}
