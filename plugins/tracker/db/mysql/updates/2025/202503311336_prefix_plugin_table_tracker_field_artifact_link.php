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

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
final class b202503311336_prefix_plugin_table_tracker_field_artifact_link extends \Tuleap\ForgeUpgrade\Bucket
{
    public function description(): string
    {
        return 'Rename table tracker_field_artifact_link to plugin_tracker_field_artifact_link';
    }

    public function up(): void
    {
        if (! $this->api->tableNameExists('tracker_field_artifact_link')) {
            $this->log->warning('Migration has nothing to do');
            return;
        }

        $this->api->dbh->exec('ALTER TABLE tracker_field_artifact_link RENAME plugin_tracker_field_artifact_link');
    }
}
