<?php
/**
 * Copyright (c) Enalean, 2025 - present. All Rights Reserved.
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

namespace Tuleap\Tracker\Test\Stub;

use Tracker_FormElement_Field;
use Tuleap\Tracker\FormElement\Field\RetrieveFieldById;

final class RetrieveFieldByIdStub implements RetrieveFieldById
{
    /** @psalm-var callable(int): ?Tracker_FormElement_Field */
    private $callback;

    private function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public static function withCallback(callable $callback): self
    {
        return new self($callback);
    }

    #[\Override]
    public function getFieldById($field_id): ?Tracker_FormElement_Field
    {
        return ($this->callback)($field_id);
    }
}
