/**
 * Copyright (c) Enalean, 2021 - present. All Rights Reserved.
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

import type { Wrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import { createStoreMock } from "@tuleap/vuex-store-wrapper-jest";
import { createRoadmapLocalVue } from "../../helpers/local-vue-for-test";
import { NaturesLabels } from "../../type";
import type { TasksState } from "../../store/tasks/type";
import type { RootState } from "../../store/type";
import DependencyNatureControl from "./DependencyNatureControl.vue";

function isSelected(wrapper: Wrapper<Vue>, nature: string): boolean {
    const option = wrapper.find(`[data-test=option-${nature}]`).element;
    if (!(option instanceof HTMLOptionElement)) {
        throw Error("Enable to find option for nature " + nature);
    }

    return option.selected;
}

describe("DependencyNatureControl", () => {
    let value: string | null, available_natures: NaturesLabels, has_at_least_one_row_shown: boolean;

    beforeEach(() => {
        value = null;
        available_natures = new NaturesLabels([
            ["", "Linked to"],
            ["depends_on", "Depends on"],
        ]);
        has_at_least_one_row_shown = true;
    });

    async function getWrapper(): Promise<Wrapper<Vue>> {
        return shallowMount(DependencyNatureControl, {
            localVue: await createRoadmapLocalVue(),
            propsData: {
                value,
                available_natures,
            },
            mocks: {
                $store: createStoreMock({
                    state: {
                        tasks: {} as TasksState,
                    } as RootState,
                    getters: {
                        "tasks/has_at_least_one_row_shown": has_at_least_one_row_shown,
                    },
                }),
            },
        });
    }

    it("should display a selectbox with available natures", async () => {
        value = "depends_on";

        const wrapper = await getWrapper();

        expect(isSelected(wrapper, "none")).toBe(false);
        expect(isSelected(wrapper, "")).toBe(false);
        expect(isSelected(wrapper, "depends_on")).toBe(true);
    });

    it("should emit input event when the value is changed", async () => {
        const wrapper = await getWrapper();

        wrapper.find(`[data-test=option-depends_on]`).setSelected();
        wrapper.find(`[data-test=option-]`).setSelected();
        wrapper.find(`[data-test=option-none]`).setSelected();

        const input_event = wrapper.emitted("input");
        if (!input_event) {
            throw new Error("Failed to catch input event");
        }

        expect(input_event[0][0]).toBe("depends_on");
        expect(input_event[1][0]).toBe("");
        expect(input_event[2][0]).toBeNull();
    });

    it("should mark the selectbox as disabled when there is no link", async () => {
        available_natures = new NaturesLabels([]);

        const wrapper = await getWrapper();

        const select = wrapper.find("[data-test=select-links]").element;
        if (!(select instanceof HTMLSelectElement)) {
            throw new Error("Unable to find the select");
        }
        expect(select.disabled).toBe(true);
    });

    it("should mark the selectbox as disabled when there is no rows", async () => {
        has_at_least_one_row_shown = false;

        const wrapper = await getWrapper();

        const select = wrapper.find("[data-test=select-links]").element;
        if (!(select instanceof HTMLSelectElement)) {
            throw new Error("Unable to find the select");
        }
        expect(select.disabled).toBe(true);
    });
});
