<!--
  - Copyright (c) Enalean, 2020 - present. All Rights Reserved.
  -
  -  This file is a part of Tuleap.
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
        <div class="tlp-form-element">
            <label class="tlp-label" for="jira-url" name="jira-url">
                {{ $gettext("Your Jira server url") }}
            </label>
            <input
                type="url"
                id="jira-url"
                name="jira-url"
                placeholder="https://jira.example.com"
                v-bind:value="credentials.server_url"
                v-on:input="updateServerUrl"
                pattern="https?://.+"
                class="tlp-input"
            />
        </div>
        <div class="tlp-form-element">
            <label class="tlp-label" for="jira-email" name="jira-email">
                {{ $gettext("User email (or jira login for Jira Server)") }}
            </label>
            <div class="tlp-form-element tlp-form-element-prepend">
                <span class="tlp-prepend">@</span>
                <input
                    type="text"
                    id="jira-email"
                    name="jira-email"
                    placeholder="Bob@example.com"
                    v-bind:value="credentials.user_email"
                    v-on:input="updateUserEmail"
                    class="tlp-input"
                />
            </div>
        </div>
        <div class="tlp-form-element">
            <label class="tlp-label" for="jira-token" name="jira-token">
                {{ $gettext("API Token (or password for Jira Server)") }}
            </label>
            <div class="tlp-form-element tlp-form-element-prepend">
                <span class="tlp-prepend"><i class="fa fa-key"></i></span>
                <input
                    type="password"
                    id="jira-token"
                    name="jira-token"
                    placeholder="ToKeNGLsLC1wY4j6qiuk61kFX"
                    v-bind:value="credentials.token"
                    v-on:input="updateToken"
                    class="tlp-input"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Credentials } from "../../../../../store/type";
import { useGettext } from "@tuleap/vue2-gettext-composition-helper";

const { $gettext } = useGettext();

const props = defineProps<{
    credentials: Credentials;
}>();

const emit = defineEmits<{
    (e: "input", value: Credentials): void;
}>();

function updateServerUrl(event: Event): void {
    if (!(event.target instanceof HTMLInputElement)) {
        return;
    }

    emit("input", {
        ...props.credentials,
        server_url: event.target.value,
    });
}

function updateUserEmail(event: Event): void {
    if (!(event.target instanceof HTMLInputElement)) {
        return;
    }

    emit("input", {
        ...props.credentials,
        user_email: event.target.value,
    });
}

function updateToken(event: Event): void {
    if (!(event.target instanceof HTMLInputElement)) {
        return;
    }

    emit("input", {
        ...props.credentials,
        token: event.target.value,
    });
}
</script>
