<!--
  - Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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
    <empty-state
        v-if="is_table_empty"
        v-bind:writing_cross_tracker_report="writing_cross_tracker_report"
    />
    <div class="export-button-box" v-if="should_show_export_button">
        <export-button />
    </div>
    <div class="cross-tracker-loader" v-if="is_loading" data-test="loading"></div>
    <div class="overflow-wrapper" v-if="total > 0">
        <div class="selectable-table" v-if="!is_loading">
            <span
                class="headers-cell"
                v-for="column_name of columns"
                v-bind:key="column_name"
                data-test="column-header"
                >{{ getColumnName(column_name) }}</span
            >
            <template v-for="(row_map, index) of rows">
                <selectable-cell
                    v-for="column_name of columns"
                    v-bind:key="column_name + index"
                    v-bind:cell="row_map.get(column_name)"
                    v-bind:even="isEven(index)"
                />
            </template>
        </div>
    </div>
    <selectable-pagination
        v-bind:limit="limit"
        v-bind:offset="offset"
        v-bind:total_number="total"
        v-on:new-page="handleNewPage"
    />
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { strictInject } from "@tuleap/vue-strict-inject";
import {
    IS_CSV_EXPORT_ALLOWED,
    NOTIFY_FAULT,
    REPORT_STATE,
    RETRIEVE_ARTIFACTS_TABLE,
} from "../../injection-symbols";
import type WritingCrossTrackerReport from "../../writing-mode/writing-cross-tracker-report";
import type { ArtifactsTable } from "../../domain/ArtifactsTable";
import type { ResultAsync } from "neverthrow";
import type { Fault } from "@tuleap/fault";
import { useGettext } from "vue3-gettext";
import type { ArtifactsTableWithTotal } from "../../domain/RetrieveArtifactsTable";
import SelectablePagination from "./SelectablePagination.vue";
import EmptyState from "./EmptyState.vue";
import { ArtifactsRetrievalFault } from "../../domain/ArtifactsRetrievalFault";
import ExportButton from "../ExportCSVButton.vue";
import SelectableCell from "./SelectableCell.vue";
import type { ColumnName } from "../../domain/ColumnName";
import { TRACKER_COLUMN_NAME, PROJECT_COLUMN_NAME } from "../../domain/ColumnName";

const { $gettext } = useGettext();

const artifacts_retriever = strictInject(RETRIEVE_ARTIFACTS_TABLE);
const report_state = strictInject(REPORT_STATE);
const notifyFault = strictInject(NOTIFY_FAULT);
const is_csv_export_allowed = strictInject(IS_CSV_EXPORT_ALLOWED);

const props = defineProps<{
    writing_cross_tracker_report: WritingCrossTrackerReport;
}>();

const is_loading = ref(false);
const columns = ref<ArtifactsTable["columns"]>(new Set());
const rows = ref<ArtifactsTable["rows"]>([]);
const total = ref(0);
let offset = 0;
const limit = 30;

const is_table_empty = computed<boolean>(() => !is_loading.value && total.value === 0);

const should_show_export_button = computed<boolean>(
    () => is_csv_export_allowed.value && !is_table_empty.value,
);

watch(report_state, () => {
    if (report_state.value === "report-saved" || report_state.value === "result-preview") {
        refreshArtifactList();
    }
});

function handleNewPage(new_offset: number): void {
    offset = new_offset;
    refreshArtifactList();
}

function refreshArtifactList(): void {
    rows.value = [];
    columns.value = new Set<string>();
    is_loading.value = true;
    loadArtifacts();
}

onMounted(() => {
    is_loading.value = true;
    loadArtifacts();
});

function loadArtifacts(): void {
    getArtifactsFromReportOrUnsavedQuery()
        .match(
            (report_with_total) => {
                columns.value = report_with_total.table.columns;
                rows.value = report_with_total.table.rows;
                total.value = report_with_total.total;
            },
            (fault) => {
                notifyFault(ArtifactsRetrievalFault(fault));
            },
        )
        .then(() => {
            is_loading.value = false;
        });
}

function getArtifactsFromReportOrUnsavedQuery(): ResultAsync<ArtifactsTableWithTotal, Fault> {
    if (report_state.value === "report-saved") {
        return artifacts_retriever.getSelectableReportContent(limit, offset);
    }

    return artifacts_retriever.getSelectableQueryResult(
        props.writing_cross_tracker_report.getTrackerIds(),
        props.writing_cross_tracker_report.expert_query,
        limit,
        offset,
    );
}

const getColumnName = (name: ColumnName): string => {
    if (name === PROJECT_COLUMN_NAME) {
        return $gettext("Project");
    }
    if (name === TRACKER_COLUMN_NAME) {
        return $gettext("Tracker");
    }
    return name;
};

const isEven = (index: number): boolean => index % 2 === 0;
</script>

<style scoped lang="scss">
@use "../../../themes/cell";

.export-button-box {
    margin: var(--tlp-medium-spacing) 0 0;
}

.overflow-wrapper {
    margin: var(--tlp-medium-spacing) var(--tlp-medium-spacing) 0;
    overflow-y: auto;
}

.selectable-table {
    display: grid;
    grid-template-columns: auto;
    grid-template-rows:
        [headers] var(--tlp-x-large-spacing)
        auto;
}

.headers-cell {
    @include cell.cell-template;

    grid-row: headers;
    border-bottom: 2px solid var(--tlp-main-color);
    color: var(--tlp-main-color);
}
</style>
