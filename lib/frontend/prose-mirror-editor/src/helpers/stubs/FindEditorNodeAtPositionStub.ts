/*
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

import type { EditorNode } from "../../types/internal-types";
import type { FindEditorNodeAtPosition } from "../EditorNodeAtPositionFinder";

export const FindEditorNodeAtPositionStub = {
    withNoEditorNode: (): FindEditorNodeAtPosition => ({
        findNodeAtPosition: () => null,
    }),
    withNode: (editor_node: EditorNode): FindEditorNodeAtPosition => ({
        findNodeAtPosition: () => editor_node,
    }),
    withNodes: (editor_nodes: Map<number, EditorNode>): FindEditorNodeAtPosition => ({
        findNodeAtPosition: (position) => editor_nodes.get(position) ?? null,
    }),
};
