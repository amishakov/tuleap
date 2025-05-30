<?php
/**
 * Copyright (c) Enalean, 2014-Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

declare(strict_types=1);

namespace Tuleap\AgileDashboard;

use Project;
use Psr\EventDispatcher\EventDispatcherInterface;
use Tuleap\AgileDashboard\Milestone\Sidebar\DuplicateMilestonesInSidebarConfig;
use Tuleap\AgileDashboard\Milestone\Sidebar\UpdateMilestonesInSidebarConfig;

final readonly class ConfigurationManager
{
    public function __construct(
        private ConfigurationDao $dao,
        private EventDispatcherInterface $event_dispatcher,
        private DuplicateMilestonesInSidebarConfig $milestones_in_sidebar_config_duplicator,
        private UpdateMilestonesInSidebarConfig $milestones_in_sidebar_config,
    ) {
    }

    public function scrumIsActivatedForProject(Project $project): bool
    {
        $block_scrum_access = new BlockScrumAccess($project);
        $this->event_dispatcher->dispatch($block_scrum_access);
        if (! $block_scrum_access->isScrumAccessEnabled()) {
            return false;
        }
        return $this->dao->isScrumActivated((int) $project->getID());
    }

    public function updateConfiguration(
        int $project_id,
        bool $scrum_is_activated,
        bool $should_sidebar_display_last_milestones,
    ): void {
        $this->dao->updateConfiguration(
            $project_id,
            $scrum_is_activated,
        );

        $should_sidebar_display_last_milestones
            ? $this->milestones_in_sidebar_config->activateMilestonesInSidebar($project_id)
            : $this->milestones_in_sidebar_config->deactivateMilestonesInSidebar($project_id);
    }

    public function duplicate(int $project_id, int $template_id): void
    {
        $this->dao->duplicate($project_id, $template_id);
        $this->milestones_in_sidebar_config_duplicator->duplicate($project_id, $template_id);
    }
}
