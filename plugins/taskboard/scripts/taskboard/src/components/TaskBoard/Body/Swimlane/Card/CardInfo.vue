<!--
  - Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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
    <div class="taskboard-card-info">
        <slot name="initial_effort" v-if="!card.is_in_edit_mode" />
        <div class="taskboard-card-assignees" v-if="!card.is_in_edit_mode || !can_edit_assignees">
            <user-avatar
                v-for="assignee in card.assignees"
                class="taskboard-card-assignees-avatars"
                v-bind:user="assignee"
                v-bind:key="assignee.id"
            />
        </div>
        <card-assignees
            v-if="card.is_in_edit_mode && can_edit_assignees"
            v-bind:card="card"
            v-bind:tracker="tracker"
            v-on:input="onAssigneesEdit"
        />
    </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import type { Card, Tracker, User } from "../../../../../type";
import CardAssignees from "./CardAssignees.vue";
import UserAvatar from "./UserAvatar.vue";

const props = defineProps<{
    card: Card;
    tracker: Tracker;
}>();

const emit = defineEmits<{
    (e: "input", value: User[]): void;
}>();

const can_edit_assignees = computed((): boolean => props.tracker.assigned_to_field !== null);

function onAssigneesEdit(new_assignees: User[]): void {
    emit("input", new_assignees);
}
</script>
