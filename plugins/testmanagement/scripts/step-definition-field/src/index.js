/*
 * Copyright (c) Enalean, 2018-Present. All Rights Reserved.
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

import VueDOMPurifyHTML from "vue-dompurify-html";
import { createStore } from "./store/index.js";
import StepDefinitionField from "./StepDefinitionField.vue";
import { setProjectId } from "./helpers/shared-properties.js";
import { createApp } from "vue";
import { getPOFileFromLocale, initVueGettext } from "@tuleap/vue3-gettext-init";
import { createGettext } from "vue3-gettext";

document.addEventListener("DOMContentLoaded", async () => {
    for (const mount_point of document.querySelectorAll(".ttm-definition-step-mount-point")) {
        const store = createStore();
        const initial_steps = JSON.parse(mount_point.dataset.steps);

        createApp(StepDefinitionField, {
            initial_steps,
            artifact_field_id: JSON.parse(mount_point.dataset.fieldId),
            empty_step: JSON.parse(mount_point.dataset.emptyStep),
            upload_url: mount_point.dataset.uploadUrl,
            upload_field_name: mount_point.dataset.uploadFieldName,
            upload_max_size: mount_point.dataset.uploadMaxSize,
        })
            .use(VueDOMPurifyHTML)
            .use(
                await initVueGettext(
                    createGettext,
                    (locale) => import(`../po/${getPOFileFromLocale(locale)}`),
                ),
            )
            .use(store)
            .mount(mount_point);
        setProjectId(mount_point.dataset.projectId);
    }
});
