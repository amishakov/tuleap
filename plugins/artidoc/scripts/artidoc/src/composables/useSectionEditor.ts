/*
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

import { isPendingSection } from "@/helpers/artidoc-section.type";
import { strictInject } from "@tuleap/vue-strict-inject";
import useSaveSection from "@/composables/useSaveSection";
import { useRefreshSection } from "@/composables/useRefreshSection";
import { UPLOAD_FILE_STORE } from "@/stores/upload-file-store-injection-key";
import type { RefreshSection } from "@/composables/useRefreshSection";
import type { ManageSectionAttachmentFiles } from "@/sections/SectionAttachmentFilesManager";
import type { Fault } from "@tuleap/fault";
import type { ReplacePendingSections } from "@/sections/PendingSectionsReplacer";
import type { UpdateSections } from "@/sections/SectionsUpdater";
import type { RemoveSections } from "@/sections/SectionsRemover";
import type { RetrieveSectionsPositionForSave } from "@/sections/SectionsPositionsForSaveRetriever";
import type { SectionState } from "@/sections/SectionStateBuilder";
import type { ReactiveStoredArtidocSection } from "@/sections/SectionsCollection";
import type { ManageErrorState } from "@/sections/SectionErrorManager";
import type { ManageSectionEditorState } from "@/sections/SectionEditorStateManager";

export type SectionEditorActions = {
    saveEditor: () => void;
    forceSaveEditor: () => void;
    cancelEditor: () => void;
    refreshSection: RefreshSection["refreshSection"];
    deleteSection: () => void;
};

export type SectionEditor = {
    editor_actions: SectionEditorActions;
};

export function useSectionEditor(
    document_id: number,
    section: ReactiveStoredArtidocSection,
    section_state: SectionState,
    manage_error_state: ManageErrorState,
    manage_section_editor_state: ManageSectionEditorState,
    manage_section_attachments: ManageSectionAttachmentFiles,
    replace_pending_sections: ReplacePendingSections,
    update_sections: UpdateSections,
    remove_sections: RemoveSections,
    retrieve_positions: RetrieveSectionsPositionForSave,
    raise_delete_section_error_callback: (error_message: string) => void,
): SectionEditor {
    const { refreshSection } = useRefreshSection(
        section,
        section_state,
        manage_error_state,
        update_sections,
        closeEditor,
    );

    const { save, forceSave } = useSaveSection(
        document_id,
        section,
        section_state,
        manage_error_state,
        replace_pending_sections,
        update_sections,
        retrieve_positions,
        manage_section_attachments,
        closeEditor,
    );

    function closeEditor(): void {
        manage_section_editor_state.resetContent();
        manage_error_state.resetErrorStates();
        manage_section_attachments.setWaitingListAttachments([]);
    }

    const { cancelSectionUploads } = strictInject(UPLOAD_FILE_STORE);
    function cancelEditor(): void {
        closeEditor();
        cancelSectionUploads(section.value.id);

        if (isPendingSection(section.value)) {
            remove_sections.removeSection(section.value);
        }
    }

    function deleteSection(): void {
        remove_sections.removeSection(section.value).match(
            () => {
                if (section_state.is_section_in_edit_mode.value) {
                    closeEditor();
                }
            },
            (fault: Fault) => {
                if (section_state.is_section_in_edit_mode.value) {
                    manage_error_state.handleError(fault);
                } else {
                    raise_delete_section_error_callback(String(fault));
                }
            },
        );
    }

    const forceSaveEditor = (): void => {
        if (!section_state.is_save_allowed.value) {
            return;
        }
        forceSave();
    };

    const saveEditor = (): void => {
        if (!section_state.is_save_allowed.value) {
            return;
        }

        save();
    };

    const editor_actions: SectionEditorActions = {
        saveEditor,
        forceSaveEditor,
        cancelEditor,
        refreshSection,
        deleteSection,
    };

    return {
        editor_actions,
    };
}
