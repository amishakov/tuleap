<?php
/**
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

namespace Tuleap\CrossTracker\Report\Query\Advanced\DuckTypedField;

use Tracker_FormElement;
use Tracker_FormElement_Field_Date;
use Tuleap\Tracker\FormElement\RetrieveFieldType;

final readonly class FieldTypeRetrieverWrapper implements RetrieveFieldType
{
    public const FIELD_DATETIME_TYPE = 'datetime';

    public function __construct(private RetrieveFieldType $wrapper)
    {
    }

    public function getType(Tracker_FormElement $form_element): string
    {
        if ($form_element instanceof Tracker_FormElement_Field_Date && $form_element->isTimeDisplayed()) {
            return self::FIELD_DATETIME_TYPE;
        }

        return $this->wrapper->getType($form_element);
    }
}