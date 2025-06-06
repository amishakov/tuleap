/*
 * Copyright (c) Enalean, 2018-Present. All Rights Reserved.
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

import type { RootState } from "../type";

export const state: RootState = {
    project_ugroups: null,
    is_loading_folder: true,
    folder_content: [],
    current_folder: null,
    current_folder_ascendant_hierarchy: [],
    is_loading_ascendant_hierarchy: false,
    root_title: "",
    folded_items_ids: [],
    folded_by_map: {},
    files_uploads_list: [],
    is_loading_currently_previewed_item: false,
    currently_previewed_item: null,
    show_post_deletion_notification: false,
    toggle_quick_look: false,
};
