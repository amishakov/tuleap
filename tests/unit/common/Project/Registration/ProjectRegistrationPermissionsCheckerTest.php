<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

namespace Tuleap\Project\Registration;

use Psr\Log\NullLogger;
use Tuleap\Project\DefaultProjectVisibilityRetriever;
use Tuleap\Project\ProjectCreationData;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class ProjectRegistrationPermissionsCheckerTest extends TestCase
{
    private ProjectRegistrationPermissionsChecker $checker;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&ProjectRegistrationUserPermissionChecker
     */
    private $permission_checker;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->permission_checker = $this->createMock(ProjectRegistrationUserPermissionChecker::class);

        $this->checker = new ProjectRegistrationPermissionsChecker(
            $this->permission_checker
        );
    }

    public function testItCollectsFirstFoundPermissionError(): void
    {
        $user = UserTestBuilder::aUser()->build();

        $this->permission_checker
            ->expects($this->once())
            ->method('checkUserCreateAProject')
            ->with($user)
            ->willThrowException(
                new class extends RegistrationForbiddenException
                {
                    #[\Override]
                    public function getI18NMessage(): string
                    {
                        return '';
                    }
                }
            );

        $data = new ProjectCreationData(
            new DefaultProjectVisibilityRetriever(),
            new NullLogger()
        );

        $errors_collection = $this->checker->collectAllErrorsForProjectRegistration(
            $user,
            $data
        );

        self::assertNotEmpty($errors_collection->getErrors());
        self::assertCount(1, $errors_collection->getErrors());
        self::assertInstanceOf(RegistrationForbiddenException::class, $errors_collection->getErrors()[0]);
    }

    public function testItReturnsEmptyCollectionIfThereIsNoPermissionErrors(): void
    {
        $user = UserTestBuilder::aUser()->build();

        $this->permission_checker
            ->expects($this->once())
            ->method('checkUserCreateAProject')
            ->with($user);

        $data = new ProjectCreationData(
            new DefaultProjectVisibilityRetriever(),
            new NullLogger()
        );

        $errors_collection = $this->checker->collectAllErrorsForProjectRegistration(
            $user,
            $data
        );

        self::assertEmpty($errors_collection->getErrors());
    }
}
