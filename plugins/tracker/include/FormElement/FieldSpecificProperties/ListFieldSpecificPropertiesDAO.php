<?php
/*
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

namespace Tuleap\Tracker\FormElement\FieldSpecificProperties;

use Tuleap\DB\DataAccessObject;
use Tuleap\Option\Option;

class ListFieldSpecificPropertiesDAO extends DataAccessObject implements DuplicateSpecificProperties, DeleteSpecificProperties
{
    public function duplicate(int $from_field_id, int $to_field_id): void
    {
        $sql = 'REPLACE INTO tracker_field_list (field_id, bind_type)
                SELECT ?, bind_type
                FROM tracker_field_list
                WHERE field_id = ?';
        $this->getDB()->run($sql, $to_field_id, $from_field_id);
    }

    public function deleteFieldProperties(int $field_id): void
    {
        $this->getDB()->delete('tracker_field_list', ['field_id' => $field_id]);
    }

    /**
     * @return Option<string>
     */
    public function searchBindByFieldId(int $field_id): Option
    {
        $sql = 'SELECT bind_type
                FROM tracker_field_list
                WHERE field_id = ? ';

        $bind_type = $this->getDB()->cell($sql, $field_id);
        return ($bind_type === false) ? Option::nothing(\Psl\Type\string()) : Option::fromValue($bind_type);
    }

    public function saveBindForFieldId(int $field_id, string $bind_type): void
    {
        $sql = 'REPLACE INTO tracker_field_list (field_id, bind_type)
                VALUES (?, ?)';
        $this->getDB()->run($sql, $field_id, $bind_type);
    }
}
