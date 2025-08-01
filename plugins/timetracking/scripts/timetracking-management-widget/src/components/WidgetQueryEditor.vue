<!--
  - Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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
    <div class="timetracking-management-query-editor">
        <div class="tlp-form-element">
            <label for="timetracking-management-query-editor-start-date" class="tlp-label">
                {{ $gettext("From") }}
                <i class="fa-solid fa-asterisk" aria-hidden="true"></i>
            </label>
            <div class="tlp-form-element tlp-form-element-prepend">
                <span class="tlp-prepend">
                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                </span>
                <input
                    type="text"
                    class="tlp-input tlp-input-date"
                    id="timetracking-management-query-editor-start-date"
                    ref="start_date_input"
                    size="11"
                    v-on:change="resetSelectedOption"
                    data-test="start-date-input"
                />
            </div>
        </div>
        <div class="tlp-form-element">
            <label for="timetracking-management-query-editor-end-date" class="tlp-label">
                {{ $gettext("To") }}
                <i class="fa-solid fa-asterisk" aria-hidden="true"></i>
            </label>
            <div class="tlp-form-element tlp-form-element-prepend">
                <span class="tlp-prepend">
                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                </span>
                <input
                    type="text"
                    class="tlp-input tlp-input-date"
                    id="timetracking-management-query-editor-end-date"
                    ref="end_date_input"
                    size="11"
                    v-on:change="resetSelectedOption"
                    data-test="end-date-input"
                />
            </div>
        </div>
        <tuleap-predefined-time-period-select
            ref="predefined_time_period_select"
            v-bind:onselection="setDatePickersValues"
            v-bind:selected_time_period="selected_predefined_time_period"
            data-test="predefined-time-period-select"
        />
    </div>
    <div class="tlp-form-element">
        <label class="tlp-label" for="update-users-select">{{ $gettext("Users") }}</label>
        <tuleap-lazybox id="update-users-select" ref="users_input" />
    </div>
    <div class="timetracking-management-query-editor-actions">
        <button
            class="tlp-button-primary tlp-button-outline"
            type="button"
            data-test="cancel-button"
            v-on:click="$emit('closeEditMode')"
        >
            {{ $gettext("Cancel") }}
        </button>
        <button
            class="tlp-button-primary"
            data-test="search-button"
            type="button"
            v-on:click="setDatesAndCloseEditMode"
        >
            {{ $gettext("Search") }}
        </button>
    </div>
</template>

<script setup lang="ts">
import "@tuleap/lazybox";
import type { Lazybox } from "@tuleap/lazybox";
import { datePicker, type DatePickerInstance } from "tlp";
import { useGettext } from "vue3-gettext";
import type { Ref } from "vue";
import { onBeforeUnmount, onMounted, ref } from "vue";
import "@tuleap/plugin-timetracking-predefined-time-periods";
import type {
    PredefinedTimePeriod,
    PredefinedTimePeriodSelect,
    PeriodOption,
} from "@tuleap/plugin-timetracking-predefined-time-periods";
import { formatDatetimeToYearMonthDay } from "@tuleap/plugin-timetracking-time-formatters";
import { strictInject } from "@tuleap/vue-strict-inject";
import { RETRIEVE_QUERY, USER_LOCALE_KEY } from "../injection-symbols";
import type { User } from "@tuleap/core-rest-api-types";
import { initUsersAutocompleter } from "@tuleap/lazybox-users-autocomplete";
import type { ResultAsync } from "neverthrow";
import type { Fault } from "@tuleap/fault";
import { uri, getJSON } from "@tuleap/fetch-result";

const { $gettext } = useGettext();

const user_locale = strictInject(USER_LOCALE_KEY);

const query = strictInject(RETRIEVE_QUERY);

const start_date_input: Ref<HTMLInputElement | undefined> = ref();
const end_date_input: Ref<HTMLInputElement | undefined> = ref();
const predefined_time_period_select: Ref<PredefinedTimePeriodSelect | undefined> = ref();

let start_date_picker: DatePickerInstance;
let end_date_picker: DatePickerInstance;

let selected_predefined_time_period = ref<PredefinedTimePeriod | "">(
    query.getQuery().predefined_time_period,
);

const users_input = ref<Lazybox | undefined>();
const currently_selected_users = ref<Array<User>>([]);

const emit = defineEmits<{
    (e: "closeEditMode"): void;
}>();

const isHTMLInputElement = (element: HTMLElement | undefined): element is HTMLInputElement => {
    return element instanceof HTMLInputElement;
};

onMounted((): void => {
    if (
        !isHTMLInputElement(start_date_input.value) ||
        !isHTMLInputElement(end_date_input.value) ||
        !users_input.value
    ) {
        return;
    }
    start_date_picker = datePicker(start_date_input.value);
    start_date_picker.setDate(query.getQuery().start_date);

    end_date_picker = datePicker(end_date_input.value);
    end_date_picker.setDate(query.getQuery().end_date);

    initUsersAutocompleter(
        users_input.value,
        query.getQuery().users_list.value,
        (selected_users: ReadonlyArray<User>): void => {
            currently_selected_users.value = [...selected_users];
        },
        user_locale,
        (query: string): ResultAsync<User[], Fault> => {
            return getJSON(uri`/api/v1/timetracking_management_users`, {
                params: {
                    query,
                },
            });
        },
    );
});

onBeforeUnmount((): void => {
    start_date_picker.destroy();
    end_date_picker.destroy();
});

const setDatesAndCloseEditMode = (): void => {
    if (start_date_input.value?.value && end_date_input.value?.value) {
        query.setQuery(
            start_date_input.value?.value,
            end_date_input.value?.value,
            selected_predefined_time_period.value,
            currently_selected_users.value,
        );
    }
    query.has_the_query_been_modified.value = true;
    emit("closeEditMode");
};

const resetSelectedOption = (): void => {
    predefined_time_period_select.value?.resetSelection();
};

const setDatePickersValues = (
    selected_time_period: PredefinedTimePeriod,
    period: PeriodOption,
): void => {
    selected_predefined_time_period.value = selected_time_period;
    period.apply((period) => {
        start_date_picker.setDate(formatDatetimeToYearMonthDay(period.start));
        end_date_picker.setDate(formatDatetimeToYearMonthDay(period.end));
    });
};
</script>

<style lang="scss">
@use "pkg:@tuleap/lazybox";
</style>

<style scoped lang="scss">
.timetracking-management-query-editor {
    display: flex;
    gap: var(--tlp-medium-spacing);
}

.timetracking-management-query-editor-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--tlp-medium-spacing);
    gap: var(--tlp-medium-spacing);
}
</style>
