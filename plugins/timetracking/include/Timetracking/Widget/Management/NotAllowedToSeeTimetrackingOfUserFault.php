<?php
/**
 * Copyright (c) Enalean, 2025 - Present. All Rights Reserved.
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

namespace Tuleap\Timetracking\Widget\Management;

use Tuleap\NeverThrow\Fault;

/**
 * @psalm-immutable
 */
final readonly class NotAllowedToSeeTimetrackingOfUserFault extends Fault
{
    protected function __construct(string $message, public \PFUser $user)
    {
        parent::__construct($message);
    }

    public static function build(\PFUser $manager, \PFUser $user): Fault
    {
        return new self(
            sprintf(
                'User #%d (%s) is not allowed to see times for user #%d (%s).',
                (int) $manager->getId(),
                $manager->getUserName(),
                (int) $user->getId(),
                $user->getUsername(),
            ),
            $user,
        );
    }
}
