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

import type { EditorState, Transaction } from "prosemirror-state";
import type { RemoveLinkCallback } from "../element/items/RemoveLinkButtonElement";
import { removePopover } from "./create-link-popover";
import { removeSelectedLinks } from "./remove-selected-links";

export type BuildRemoveLinkCallback = {
    build(doc: Document, editor_id: string): RemoveLinkCallback;
};

export const RemoveLinkCallbackBuilder = (
    state: EditorState,
    dispatch: (tr: Transaction) => void,
): BuildRemoveLinkCallback => ({
    build:
        (doc: Document, editor_id: string): RemoveLinkCallback =>
        (): void => {
            removeSelectedLinks(state, dispatch);
            removePopover(doc, editor_id);
        },
});
