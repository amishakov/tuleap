<!--
  - Copyright (c) Enalean, 2024 - present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -
  -->
<template>
    <section v-bind:data-test="is_section_in_edit_mode ? 'section-edition' : undefined">
        <div class="artidoc-dropdown-container">
            <section-dropdown
                v-bind:editor="editor"
                v-bind:section="section"
                v-if="!is_loading_sections"
            />
        </div>

        <article
            class="document-section"
            v-bind:class="{
                'document-section-is-being-saved': isBeingSaved(),
                'document-section-is-just-saved': isJustSaved(),
                'document-section-is-just-refreshed': isJustRefreshed(),
                'document-section-is-in-error': is_in_error,
                'document-section-is-outdated': is_outdated,
            }"
        >
            <section-header
                class="section-header"
                v-if="!is_loading_sections"
                v-bind:title="section.display_title"
            />
            <section-header-skeleton v-if="is_loading_sections" class="section-header" />
            <section-description
                v-bind:editable_description="editable_description"
                v-bind:readonly_description="getReadonlyDescription()"
                v-bind:is_edit_mode="is_section_in_edit_mode"
                v-bind:add_attachment_to_waiting_list="addAttachmentToWaitingList"
                v-bind:post_information="post_information"
                v-bind:is_image_upload_allowed="is_image_upload_allowed"
                v-bind:upload_file="upload_file"
                v-bind:project_id="getProjectId()"
                v-bind:title="section.display_title"
                v-bind:input_section_content="inputSectionContent"
                v-bind:is_there_any_change="is_there_any_change"
                v-bind:section="section"
            />
            <section-footer v-bind:editor="editor" v-bind:section="section" />
        </article>
    </section>
</template>

<script setup lang="ts">
import type { ArtidocSection } from "@/helpers/artidoc-section.type";
import { isPendingArtifactSection, isArtifactSection } from "@/helpers/artidoc-section.type";
import SectionHeader from "./header/SectionHeader.vue";
import SectionDescription from "./description/SectionDescription.vue";
import { useSectionEditor } from "@/composables/useSectionEditor";
import SectionDropdown from "./header/SectionDropdown.vue";
import SectionHeaderSkeleton from "./header/SectionHeaderSkeleton.vue";
import SectionFooter from "./footer/SectionFooter.vue";
import { useAttachmentFile } from "@/composables/useAttachmentFile";
import { strictInject } from "@tuleap/vue-strict-inject";
import { useUploadFile } from "@/composables/useUploadFile";
import { SET_GLOBAL_ERROR_MESSAGE } from "@/global-error-message-injection-key";
import { useGettext } from "vue3-gettext";
import { IS_LOADING_SECTIONS } from "@/is-loading-sections-injection-key";
import { getPendingSectionsReplacer } from "@/sections/PendingSectionsReplacer";
import { SECTIONS_COLLECTION } from "@/sections/sections-collection-injection-key";
import { getSectionsUpdater } from "@/sections/SectionsUpdater";
import { getSectionsRemover } from "@/sections/SectionsRemover";
import { getSectionsPositionsForSaveRetriever } from "@/sections/SectionsPositionsForSaveRetriever";
import { DOCUMENT_ID } from "@/document-id-injection-key";

const props = defineProps<{ section: ArtidocSection }>();
const setGlobalErrorMessage = strictInject(SET_GLOBAL_ERROR_MESSAGE);
const is_loading_sections = strictInject(IS_LOADING_SECTIONS);
const sections_collection = strictInject(SECTIONS_COLLECTION);
const document_id = strictInject(DOCUMENT_ID);

const {
    post_information,
    addAttachmentToWaitingList,
    mergeArtifactAttachments,
    setWaitingListAttachments,
} = useAttachmentFile(props.section, document_id);

const upload_file = useUploadFile(props.section.id, post_information, addAttachmentToWaitingList);

const { $gettext } = useGettext();

const editor = useSectionEditor(
    props.section,
    mergeArtifactAttachments,
    setWaitingListAttachments,
    getPendingSectionsReplacer(sections_collection),
    getSectionsUpdater(sections_collection),
    getSectionsRemover(sections_collection),
    getSectionsPositionsForSaveRetriever(sections_collection),
    upload_file.is_in_progress,
    (error: string) => {
        setGlobalErrorMessage({
            message: $gettext("An error occurred while removing the section."),
            details: error,
        });
    },
);

const {
    is_section_in_edit_mode,
    isJustRefreshed,
    isJustSaved,
    isBeingSaved,
    is_image_upload_allowed,
} = editor.editor_state;
const { is_in_error, is_outdated } = editor.editor_error;

const { inputSectionContent, is_there_any_change, editable_description, getReadonlyDescription } =
    editor.editor_section_content;

function getProjectId(): number {
    if (isArtifactSection(props.section)) {
        return props.section.artifact.tracker.project.id;
    }

    return isPendingArtifactSection(props.section) ? props.section.tracker.project.id : 0;
}
</script>

<style lang="scss" scoped>
@use "@/themes/includes/whitespace";

section {
    display: grid;
    grid-template-columns: auto whitespace.$section-right-padding;
}

.artidoc-dropdown-container {
    display: flex;
    justify-content: center;
    order: 1;

    @media print {
        display: none;
    }
}

.document-section {
    display: flex;
    flex-direction: column;
}

.section-header {
    margin-bottom: var(--tlp-medium-spacing);
    border-bottom: 1px solid var(--tlp-neutral-normal-color);
    background: var(--tuleap-artidoc-section-background);
}
</style>
