/*
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

import type { LocaleString } from "@tuleap/core-constants";

const isLocale = (locale: string | undefined): locale is LocaleString => {
    return locale !== undefined;
};

export const getLocaleOrThrow = (doc: Document): LocaleString => {
    const locale = doc.body.dataset.userLocale;
    if (!isLocale(locale)) {
        throw Error("Could not read user locale from document body");
    }
    return locale;
};

export const getTimezoneOrThrow = (doc: Document): string => {
    const timezone = doc.body.dataset.userTimezone;
    if (!timezone) {
        throw Error("Could not read user timezone from document body");
    }
    return timezone;
};
