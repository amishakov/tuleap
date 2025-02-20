/*
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
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

import { dispatch } from "hybrids";
import { loadTooltips } from "@tuleap/tooltip";
import type { HostElement } from "./WritingZone";
import { WritingZonePresenter } from "./WritingZonePresenter";
import { fetchCommonMarkPreview } from "./WritingZoneCommonMarkPreviewFetcher";

export type ControlWritingZone = {
    onTextareaInput(host: HostElement): void;
    switchToWritingMode(host: HostElement): void;
    switchToPreviewMode(host: HostElement): void;
    focusWritingZone(host: HostElement): void;
    blurWritingZone(host: HostElement): void;
    resetWritingZone(host: HostElement): void;
    initWritingZone(): WritingZonePresenter;
    shouldFocusWritingZoneWhenConnected(): boolean;
};

export type WritingZoneConfig = {
    document: Document;
    project_id: number;
    focus_writing_zone_when_connected?: boolean;
};

export const PARENT_ELEMENT_ACTIVE_CLASS = "pull-request-comment-with-writing-zone-active";

export const WritingZoneController = (config: WritingZoneConfig): ControlWritingZone => {
    const focusWritingZone = (host: HostElement): void => {
        host.presenter = WritingZonePresenter.buildFocused(host.presenter);

        if (host.presenter.is_in_writing_mode && config.document.activeElement !== host.textarea) {
            host.textarea.focus();
            host.textarea.setSelectionRange(host.textarea.value.length, host.textarea.value.length);
        }

        if (host.parentElement) {
            host.parentElement.classList.add(PARENT_ELEMENT_ACTIVE_CLASS);
        }
    };

    const blurWritingZone = (host: HostElement): void => {
        host.presenter = WritingZonePresenter.buildBlurred(host.presenter);

        if (host.parentElement) {
            host.parentElement.classList.remove(PARENT_ELEMENT_ACTIVE_CLASS);
        }
    };

    return {
        initWritingZone: () => WritingZonePresenter.buildInitial(config.project_id),

        onTextareaInput: (host: HostElement): void => {
            const content = host.textarea.value;
            host.comment_content = content;
            dispatch(host, "writing-zone-input", { detail: { content } });
        },

        switchToWritingMode: (host: HostElement): void => {
            host.presenter = WritingZonePresenter.buildWritingMode(host.presenter);

            setTimeout(() => {
                focusWritingZone(host);
            });
        },

        switchToPreviewMode: (host: HostElement): void => {
            fetchCommonMarkPreview(host.presenter.project_id, host.comment_content)
                .match(
                    (previewed_content: string) => {
                        host.presenter = WritingZonePresenter.buildPreviewMode(
                            host.presenter,
                            previewed_content,
                        );

                        setTimeout(() => {
                            loadTooltips(host);
                        });
                    },
                    () => {
                        host.presenter = WritingZonePresenter.buildPreviewWithError(host.presenter);
                    },
                )
                .finally(() => {
                    setTimeout(() => {
                        focusWritingZone(host);
                    });
                });
        },

        focusWritingZone,
        blurWritingZone,

        resetWritingZone: (host: HostElement): void => {
            host.textarea.value = "";
            host.comment_content = "";
            host.presenter = WritingZonePresenter.buildBlurred(
                WritingZonePresenter.buildWritingMode(host.presenter),
            );
            blurWritingZone(host);
        },

        shouldFocusWritingZoneWhenConnected: () => config.focus_writing_zone_when_connected ?? true,
    };
};
