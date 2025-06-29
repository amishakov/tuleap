<!--
  - Copyright (c) Enalean, 2025 - present. All Rights Reserved.
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
  -->

<template>
    <div class="tlp-modal-body">
        <tracker-selection-introductory-text v-bind:selected_tracker="new_selected_tracker" />
        <tracker-selection
            v-bind:allowed_trackers="allowed_trackers"
            v-bind:selected_tracker="new_selected_tracker"
            v-bind:is_tracker_selection_disabled="is_success"
            v-on:select-tracker="onSelectTracker"
        />
    </div>
    <configuration-modal-footer
        v-bind:current_tab="TRACKER_SELECTION_TAB"
        v-bind:is_submit_button_disabled="is_submit_button_disabled"
        v-bind:on_save_callback="onSubmit"
        v-bind:is_saving="is_saving"
        v-bind:is_error="is_error"
        v-bind:is_success="is_success"
        v-bind:error_message="error_message"
    />
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import type { Option } from "@tuleap/option";
import { strictInject } from "@tuleap/vue-strict-inject";
import { TRACKER_SELECTION_TAB } from "@/components/configuration/configuration-modal";
import TrackerSelectionIntroductoryText from "@/components/configuration/TrackerSelectionIntroductoryText.vue";
import TrackerSelection from "@/components/configuration/TrackerSelection.vue";
import ConfigurationModalFooter from "@/components/configuration/ConfigurationModalFooter.vue";
import type { Tracker } from "@/configuration/AllowedTrackersCollection";
import { ALLOWED_TRACKERS } from "@/configuration/AllowedTrackersCollection";
import { SELECTED_TRACKER } from "@/configuration/SelectedTracker";
import { buildTrackerConfigurationSaver } from "@/configuration/TrackerConfigurationSaver";
import { DOCUMENT_ID } from "@/document-id-injection-key";
import { SELECTED_FIELDS } from "@/configuration/SelectedFieldsCollection";
import { AVAILABLE_FIELDS } from "@/configuration/AvailableFieldsCollection";

const saved_tracker = strictInject(SELECTED_TRACKER);
const allowed_trackers = strictInject(ALLOWED_TRACKERS);
const document_id = strictInject(DOCUMENT_ID);
const selected_fields = strictInject(SELECTED_FIELDS);
const available_fields = strictInject(AVAILABLE_FIELDS);

const new_selected_tracker = ref<Option<Tracker>>(saved_tracker.value);
const is_saving = ref<boolean>(false);
const is_error = ref<boolean>(false);
const is_success = ref<boolean>(false);
const error_message = ref<string>("");

const is_submit_button_disabled = computed(
    () =>
        allowed_trackers.isEmpty() ||
        is_saving.value ||
        new_selected_tracker.value.mapOr(
            (tracker) => tracker.id === saved_tracker.value.mapOr((saved) => saved.id, Number.NaN),
            false,
        ),
);

const configuration_saver = buildTrackerConfigurationSaver(
    document_id,
    saved_tracker,
    selected_fields,
    available_fields,
);

function onSubmit(): void {
    new_selected_tracker.value.apply((tracker) => {
        is_saving.value = true;
        is_error.value = false;
        is_success.value = false;
        configuration_saver.saveTrackerConfiguration(tracker).match(
            () => {
                is_saving.value = false;
                is_success.value = true;
            },
            (fault) => {
                is_saving.value = false;
                is_error.value = true;
                error_message.value = String(fault);
            },
        );
    });
}

function onSelectTracker(tracker: Option<Tracker>): void {
    new_selected_tracker.value = tracker;
}

defineExpose({ is_success });
</script>
