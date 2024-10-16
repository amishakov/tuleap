/*
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

import { describe, it, expect, vi } from "vitest";
import * as tlp_popovers from "@tuleap/tlp-popovers";
import type { Popover } from "@tuleap/tlp-popovers";
import { createLocalDocument } from "../../../helpers/helper-for-test";
import type { PopoverHost } from "./connect-popover";
import { connectPopover } from "./connect-popover";

vi.mock("@tuleap/tlp-popovers");

describe("connect-popover", () => {
    const getHost = (): PopoverHost => {
        const doc = createLocalDocument();

        return {
            button_element: doc.createElement("button"),
            popover_element: doc.createElement("div"),
        } as unknown as PopoverHost;
    };

    it("When the component is connected, then it should create a popover instance", () => {
        const host = getHost();
        const createPopover = vi.spyOn(tlp_popovers, "createPopover");

        connectPopover(host);

        expect(createPopover).toHaveBeenCalledOnce();
        expect(createPopover).toHaveBeenCalledWith(host.button_element, host.popover_element, {
            placement: "bottom-start",
            trigger: "click",
        });
    });

    it("When the component is disconnected, then it should destroy the popover instance", () => {
        const host = getHost();
        const popover_instance = {
            destroy: vi.fn(),
        } as unknown as Popover;

        vi.spyOn(tlp_popovers, "createPopover").mockReturnValue(popover_instance);

        const disconnect = connectPopover(host);

        disconnect();

        expect(popover_instance.destroy).toHaveBeenCalledOnce();
    });
});
