<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Report\Query\Advanced;

use Exception;

final class SearchablesDoNotExistException extends Exception
{
    private string $i18n_message;

    /**
     * @param string[] $non_existing_searchables
     */
    public function __construct(array $non_existing_searchables)
    {
        $string_list = implode("', '", $non_existing_searchables);
        parent::__construct(sprintf("We cannot search on '%s', we don't know what they refer to. Please refer to the documentation for the allowed comparisons.", $string_list));
        $this->i18n_message = sprintf(
            dngettext(
                'tuleap-tracker',
                "We cannot search on '%s', we don't know what it refers to. Please refer to the documentation for the allowed comparisons.",
                "We cannot search on '%s', we don't know what they refer to. Please refer to the documentation for the allowed comparisons.",
                count($non_existing_searchables)
            ),
            $string_list
        );
    }

    public function getI18NExceptionMessage(): string
    {
        return $this->i18n_message;
    }
}
