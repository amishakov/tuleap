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
    <div>
        <button
            type="button"
            class="tlp-button-primary"
            v-bind:title="title"
            v-on:click="onClick"
            data-test="add-new-section"
        >
            <i class="fa-solid fa-plus" role="img"></i>
        </button>
    </div>
</template>

<script setup lang="ts">
import { useGettext } from "vue3-gettext";
import { strictInject } from "@tuleap/vue-strict-inject";
import { OPEN_CONFIGURATION_MODAL_BUS } from "@/composables/useOpenConfigurationModalBus";
import { isTrackerWithSubmittableSection, CONFIGURATION_STORE } from "@/stores/configuration-store";
import type { PositionDuringEdition, SectionsStore } from "@/stores/useSectionsStore";
import type { PendingArtifactSection } from "@/helpers/artidoc-section.type";
import PendingArtifactSectionFactory from "@/helpers/pending-artifact-section.factory";

const props = defineProps<{
    position: PositionDuringEdition;
    insert_section_callback: SectionsStore["insertSection"];
}>();

const configuration_store = strictInject(CONFIGURATION_STORE);

const { $gettext } = useGettext();

const title = $gettext("Add new section");

const bus = strictInject(OPEN_CONFIGURATION_MODAL_BUS);

function onClick(): void {
    if (!configuration_store.selected_tracker.value) {
        openConfigurationModalBeforeInsertingNewSection();
        return;
    }

    if (!isTrackerWithSubmittableSection(configuration_store.selected_tracker.value)) {
        openConfigurationModalBeforeInsertingNewSection();
        return;
    }

    insertNewSection();
}

function openConfigurationModalBeforeInsertingNewSection(): void {
    bus.openModal(insertNewSection);
}

function insertNewSection(): void {
    if (!configuration_store.selected_tracker.value) {
        return;
    }

    if (!isTrackerWithSubmittableSection(configuration_store.selected_tracker.value)) {
        return;
    }

    const section: PendingArtifactSection = PendingArtifactSectionFactory.overrideFromTracker(
        configuration_store.selected_tracker.value,
    );

    props.insert_section_callback(section, props.position);
}
</script>

<style scoped lang="scss">
@use "@/themes/includes/whitespace";
@use "@/themes/includes/size";

$half: calc(#{size.$add-section-button-container-height} * 0.5);
$half-minus-one-px: calc(#{$half} - 1px);
$half-plus-one-px: calc(#{$half} + 1px);

div {
    --add-new-section-button-background-color: var(--tlp-neutral-light-color);
    --add-new-section-button-text-color: var(--tlp-typo-default-text-color);

    display: flex;
    justify-content: center;
    margin: 0 whitespace.$section-right-padding 0 whitespace.$section-left-padding;
    padding: whitespace.$add-section-button-container-vertical-padding 0;
    transition: background-color ease-in-out 150ms;
    background: linear-gradient(
        0deg,
        var(--tlp-white-color) 0,
        var(--tlp-white-color) $half-minus-one-px,
        var(--add-new-section-button-background-color) $half,
        var(--tlp-white-color) $half-plus-one-px,
        var(--tlp-white-color) 100%
    );

    &:has(button:hover, button:focus-within) {
        --add-new-section-button-background-color: var(--tlp-main-color);
        --add-new-section-button-text-color: var(--tlp-white-color);
    }

    @media print {
        display: none;
    }
}

button {
    width: size.$add-section-button-size;
    height: size.$add-section-button-size;
    padding: 0;
    transition: all ease-in-out 150ms;
    border-radius: 50%;
    border-color: var(--add-new-section-button-background-color);
    background: var(--add-new-section-button-background-color);
    color: var(--add-new-section-button-text-color);

    &:focus-within,
    &:hover {
        border-color: var(--add-new-section-button-background-color);
        background: var(--add-new-section-button-background-color);
        color: var(--add-new-section-button-text-color);
    }
}
</style>
