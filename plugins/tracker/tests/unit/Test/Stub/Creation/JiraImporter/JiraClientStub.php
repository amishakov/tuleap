<?php
/**
 * Copyright (c) Enalean, 2025-Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Test\Stub\Creation\JiraImporter;

use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\Attachment\Attachment;
use Tuleap\Tracker\Creation\JiraImporter\JiraClient;

final class JiraClientStub implements JiraClient
{
    private function __construct()
    {
    }

    public static function aJiraClient(): self
    {
        return new self();
    }

    #[\Override]
    public function getUrl(string $url): ?array
    {
        return null;
    }

    #[\Override]
    public function isJiraCloud(): bool
    {
        return false;
    }

    #[\Override]
    public function isJiraServer9(): bool
    {
        return false;
    }

    #[\Override]
    public function getAttachmentContents(Attachment $attachment): string
    {
        return '';
    }
}
