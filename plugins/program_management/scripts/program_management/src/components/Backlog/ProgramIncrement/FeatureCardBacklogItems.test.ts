/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

import type { VueWrapper } from "@vue/test-utils";
import { shallowMount } from "@vue/test-utils";
import FeatureCardBacklogItems from "./FeatureCardBacklogItems.vue";
import { getGlobalTestOptions } from "../../../helpers/global-options-for-tests";
import type { Feature, TrackerMinimalRepresentation } from "../../../type";
import BacklogElementSkeleton from "../BacklogElementSkeleton.vue";
import type { ProgramIncrement } from "../../../helpers/ProgramIncrement/program-increment-retriever";
import type { UserStory } from "../../../helpers/UserStories/user-stories-retriever";
import ErrorDisplayer from "../ErrorDisplayer.vue";
import UserStoryDisplayer from "../UserStoryDisplayer.vue";

jest.useFakeTimers();

describe("FeatureCardBacklogItems", () => {
    let feature: Feature;
    beforeEach(() => {
        feature = { id: 100 } as Feature;
    });

    function getWrapper(
        user_stories: UserStory[],
    ): VueWrapper<InstanceType<typeof FeatureCardBacklogItems>> {
        return shallowMount(FeatureCardBacklogItems, {
            global: {
                ...getGlobalTestOptions({
                    actions: {
                        linkUserStoriesToFeature: () => Promise.resolve(user_stories),
                    },
                }),
            },
            props: {
                feature,
                program_increment: { id: 11 } as ProgramIncrement,
            },
        });
    }

    it("Displays a skeleton during get user stories", async () => {
        const wrapper = getWrapper([]);

        wrapper.get("[data-test=backlog-items-open-close-button]").trigger("click");
        await wrapper.vm.$nextTick();

        expect(wrapper.findComponent(BacklogElementSkeleton).exists()).toBe(true);
    });

    it("Displays error message if api rest error exists", async () => {
        const wrapper = getWrapper([]);
        wrapper.vm.message_error_rest = "404 Not Found";

        wrapper.get("[data-test=backlog-items-open-close-button]").trigger("click");
        await jest.runOnlyPendingTimersAsync();

        expect(wrapper.findComponent(BacklogElementSkeleton).exists()).toBe(false);
        expect(wrapper.findComponent(ErrorDisplayer).exists()).toBe(true);
        expect(wrapper.findComponent(UserStoryDisplayer).exists()).toBe(false);
    });

    it("When user stories are loaded, Then UserStoryDisplayer is rendered", async () => {
        const wrapper = getWrapper([
            {
                id: 14,
                title: "My US",
                xref: "us #14",
                background_color: "lake-placid-blue",
                tracker: { color_name: "fiesta-red" } as TrackerMinimalRepresentation,
                is_open: true,
                uri: "tracker?aid=14",
                project: { label: "project" },
            } as UserStory,
        ]);

        wrapper.get("[data-test=backlog-items-open-close-button]").trigger("click");
        await jest.runOnlyPendingTimersAsync();

        expect(wrapper.findComponent(BacklogElementSkeleton).exists()).toBe(false);
        expect(wrapper.findComponent(ErrorDisplayer).exists()).toBe(false);
        expect(wrapper.findComponent(UserStoryDisplayer).exists()).toBe(true);
    });

    it("No rest call when user stories are already loaded in feature", async () => {
        feature = {
            id: 100,
            user_stories: [
                {
                    id: 14,
                    title: "My US",
                    xref: "us #14",
                    background_color: "lake-placid-blue",
                    tracker: { color_name: "fiesta-red" } as TrackerMinimalRepresentation,
                    is_open: true,
                    uri: "tracker?aid=14",
                    project: { label: "project" },
                } as UserStory,
            ],
        } as Feature;

        const wrapper = getWrapper([]);

        await wrapper.get("[data-test=backlog-items-open-close-button]").trigger("click");

        expect(wrapper.findAllComponents(UserStoryDisplayer)).toHaveLength(1);
        expect(wrapper.findComponent(BacklogElementSkeleton).exists()).toBe(false);
        expect(wrapper.findComponent(ErrorDisplayer).exists()).toBe(false);
        expect(wrapper.findComponent(UserStoryDisplayer).exists()).toBe(true);
    });

    it("When user stories are loaded, Then user can hide stories", async () => {
        feature = {
            id: 100,
            user_stories: [
                {
                    id: 14,
                    title: "My US",
                    xref: "us #14",
                    background_color: "lake-placid-blue",
                    tracker: { color_name: "fiesta-red" } as TrackerMinimalRepresentation,
                    is_open: true,
                    uri: "tracker?aid=14",
                    project: { label: "project" },
                } as UserStory,
            ],
        } as Feature;

        const wrapper = getWrapper([]);

        await wrapper.get("[data-test=backlog-items-open-close-button]").trigger("click");

        expect(wrapper.findAllComponents(UserStoryDisplayer)).toHaveLength(1);
        expect(wrapper.findComponent(BacklogElementSkeleton).exists()).toBe(false);
        expect(wrapper.findComponent(ErrorDisplayer).exists()).toBe(false);
        expect(wrapper.findComponent(UserStoryDisplayer).exists()).toBe(true);

        await wrapper.get("[data-test=backlog-items-open-close-button]").trigger("click");

        expect(wrapper.findComponent(BacklogElementSkeleton).exists()).toBe(false);
        expect(wrapper.findComponent(ErrorDisplayer).exists()).toBe(false);
        expect(wrapper.findComponent(UserStoryDisplayer).exists()).toBe(false);
    });
});
