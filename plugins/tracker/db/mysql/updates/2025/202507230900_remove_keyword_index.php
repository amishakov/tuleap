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

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
final class b202507230900_remove_keyword_index extends \Tuleap\ForgeUpgrade\Bucket
{
    public function description(): string
    {
        return 'Remove keyword index on tracker_changeset_value_artifactlink table';
    }

    public function up(): void
    {
        // keyword and group_id become nullable instead of being dropped so that we can bisect
        $this->api->dbh->exec(
            <<<EOS
            ALTER TABLE tracker_changeset_value_artifactlink
                MODIFY COLUMN keyword VARCHAR(32) NULL,
                MODIFY COLUMN group_id INT(11) NULL,
                DROP INDEX idx_group_id_keyword
            EOS
        );
    }
}
