<!--
  - Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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
    <div
        class="tlp-form-element"
        v-bind:class="{
            'tracker-name-above-slugified-shortname': can_display_slugify_mode,
            'tlp-form-element-error': is_name_already_used,
        }"
    >
        <label class="tlp-label" for="tracker-name">
            {{ $gettext("Name") }}
            <i class="fa fa-asterisk"></i>
        </label>
        <input
            type="text"
            class="tlp-input tlp-input-large"
            id="tracker-name"
            name="tracker-name"
            data-test="tracker-name-input"
            v-bind:value="tracker_to_be_created.name"
            v-on:keyup="setTrackerName($event)"
            required
        />
        <p class="tlp-text-danger" data-test="name-error" v-if="is_name_already_used">
            <i class="fa fa-fw fa-exclamation-circle"></i>
            {{
                $gettext(
                    "The chosen name already exist in this project, please choose another one.",
                )
            }}
        </p>
    </div>
</template>
<script setup lang="ts">
import { useState, useStore, useGetters } from "vuex-composition-helpers";
import type { State } from "../../../../store/type";

const { tracker_to_be_created } = useState<Pick<State, "tracker_to_be_created">>([
    "tracker_to_be_created",
]);
const { can_display_slugify_mode, is_name_already_used } = useGetters([
    "can_display_slugify_mode",
    "is_name_already_used",
]);

const $store = useStore();

function setTrackerName(event: Event): void {
    if (!(event.target instanceof HTMLInputElement)) {
        return;
    }
    $store.commit("setTrackerName", event.target.value);
}
</script>
