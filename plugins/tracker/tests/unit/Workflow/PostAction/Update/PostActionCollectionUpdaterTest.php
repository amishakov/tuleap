<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Workflow\PostAction\Update;

use PHPUnit\Framework\MockObject\MockObject;
use Transition;
use Tuleap\Tracker\Workflow\PostAction\Update\Internal\PostActionUpdater;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class PostActionCollectionUpdaterTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private PostActionCollectionUpdater $collection_updater;

    private PostActionUpdater&MockObject $post_action_updater1;
    private PostActionUpdater&MockObject $post_action_updater2;

    #[\PHPUnit\Framework\Attributes\Before]
    public function createUpdater()
    {
        $this->post_action_updater1 = $this->createMock(PostActionUpdater::class);
        $this->post_action_updater2 = $this->createMock(PostActionUpdater::class);

        $this->collection_updater = new PostActionCollectionUpdater(
            $this->post_action_updater1,
            $this->post_action_updater2
        );
    }

    public function testUpdateByTransitionDelegatesUpdateToUpdaters()
    {
        $transition = $this->createMock(Transition::class);
        $transition->method('getId')
            ->willReturn(1);

        $action            = new CIBuildValue('http://example.test');
        $action_collection = new PostActionCollection($action);

        $this->post_action_updater1
            ->expects($this->atLeast(1))
            ->method('updateByTransition')
            ->with($action_collection, $transition);
        $this->post_action_updater2
            ->expects($this->atLeast(1))
            ->method('updateByTransition')
            ->with($action_collection, $transition);

        $this->collection_updater->updateByTransition($transition, $action_collection);
    }
}
