/*
 * Copyright (c) Enalean 2022 -  Present. All Rights Reserved.
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
 *
 */

import { describe, expect, it, vi } from "vitest";
import type { VueWrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import StatusProperty from "./StatusProperty.vue";
import emitter from "../../../../helpers/emitter";
import { getGlobalTestOptions } from "../../../../helpers/global-options-for-test";

vi.mock("../../../../helpers/emitter");

describe("StatusProperty", () => {
    function createWrapper(props = {}): VueWrapper<InstanceType<typeof StatusProperty>> {
        return shallowMount(StatusProperty, {
            props: { ...props },
            global: { ...getGlobalTestOptions({}) },
        });
    }

    it(`send a custom event on change`, () => {
        const wrapper = createWrapper({ value: "draft" });

        wrapper.find("[data-test=value-approved]").setSelected();

        expect(emitter.emit).toHaveBeenCalledWith("update-status-property", "approved");
    });
});
