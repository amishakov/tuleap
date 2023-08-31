<?php
/**
 * Copyright (c) Enalean, 2023 - Present. All Rights Reserved.
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

namespace Tuleap\AgileDashboard;

use Tuleap\Kanban\SplitKanbanConfigurationChecker;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

final class AgileDashboardServiceHomepageUrlBuilderTest extends TestCase
{
    public function testGetTopBacklogUrl(): void
    {
        self::assertSame(
            '/plugins/agiledashboard/?group_id=116&action=show-top&pane=topplanning-v2',
            AgileDashboardServiceHomepageUrlBuilder::getTopBacklogUrl(
                ProjectTestBuilder::aProject()->withId(116)->build(),
            )
        );
    }

    public function testGetUrlReturnLegacyUrlWhenProjectIsNotUsingSplitKanban(): void
    {
        $checker = new class implements SplitKanbanConfigurationChecker {
            public function isProjectAllowedToUseSplitKanban(\Project $project): bool
            {
                return false;
            }
        };

        self::assertSame(
            '/plugins/agiledashboard/?group_id=116',
            AgileDashboardServiceHomepageUrlBuilder::buildWithSplitKanbanConfigurationChecker($checker)
                ->getUrl(ProjectTestBuilder::aProject()->withId(116)->build())
        );
    }

    public function testGetUrlTopBacklogUrlWhenProjectIsUsingSplitKanban(): void
    {
        $checker = new class implements SplitKanbanConfigurationChecker {
            public function isProjectAllowedToUseSplitKanban(\Project $project): bool
            {
                return true;
            }
        };

        self::assertSame(
            '/plugins/agiledashboard/?group_id=116&action=show-top&pane=topplanning-v2',
            AgileDashboardServiceHomepageUrlBuilder::buildWithSplitKanbanConfigurationChecker($checker)
                ->getUrl(ProjectTestBuilder::aProject()->withId(116)->build())
        );
    }
}