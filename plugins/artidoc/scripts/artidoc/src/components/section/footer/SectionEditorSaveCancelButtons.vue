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
    <div v-if="is_section_in_edit_mode" class="document-section-cancel-save-buttons">
        <button
            v-on:click="onCancel"
            type="button"
            class="tlp-button-primary tlp-button-outline tlp-button-large"
        >
            <i class="fa-solid fa-xmark tlp-button-icon" aria-hidden="true"></i>
            <span>{{ $gettext("Cancel") }}</span>
        </button>
        <button
            v-on:click="saveEditor"
            v-bind:disabled="!is_save_allowed"
            type="button"
            class="tlp-button-primary tlp-button-large"
        >
            <i class="fa-solid fa-floppy-disk tlp-button-icon" aria-hidden="true"></i>
            <span>{{ $gettext("Save") }}</span>
        </button>
    </div>
</template>

<script setup lang="ts">
import type { SectionEditor } from "@/composables/useSectionEditor";
import { useGettext } from "vue3-gettext";
import type { SectionState } from "@/sections/SectionStateBuilder";

const props = defineProps<{
    editor: SectionEditor;
    section_state: SectionState;
}>();

const { $gettext } = useGettext();
const { cancelEditor, saveEditor } = props.editor.editor_actions;
const { is_section_in_edit_mode, is_save_allowed } = props.section_state;

function onCancel(): void {
    cancelEditor();
}
</script>

<style lang="scss" scoped>
div {
    display: flex;
    gap: var(--tlp-medium-spacing);
    justify-content: center;
}
</style>
