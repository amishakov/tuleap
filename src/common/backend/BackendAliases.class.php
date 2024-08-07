<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright (c) Enalean, 2011 - Present. All rights reserved
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


class BackendAliases extends Backend
{
    public const ADMIN_ALIAS        = 'codendi-admin';
    public const ALIAS_ENTRY_FORMAT = '%-50s%-10s';

    protected $need_update = false;

    /**
     * Set if we need to update mail aliases
     *
     * @return void
     */
    public function setNeedUpdateMailAliases()
    {
        $this->need_update = true;
    }

    /**
     * Do we need to update mail aliases?
     *
     * @return bool
     */
    public function aliasesNeedUpdate()
    {
        return $this->need_update;
    }

    /**
     * Write System email aliases:
     * - generic aliases like codendi-admin
     * - user aliases for addresses like user@codendi.server.name
     *
     * @return bool
     */
    public function update()
    {
        $alias_file     = ForgeConfig::get('alias_file');
        $alias_file_new = $alias_file . '.new';
        $alias_file_old = $alias_file . '.old';

        if (! $fp = fopen($alias_file_new, 'w')) {
            $this->log("Can't open file for writing: $alias_file_new", Backend::LOG_ERROR);
            return false;
        }

        if (
            (! $this->writeGenericAliases($fp))
            || (! $this->writeOtherAliases($fp))
        ) {
            $this->log("Can't write aliases to $alias_file_new", Backend::LOG_ERROR);
            return false;
        }
        fclose($fp);

        // Replace current file by new one
        if (! $this->installNewFileVersion($alias_file_new, $alias_file, $alias_file_old, true)) {
            return false;
        }

        // Run newaliases
        return ($this->system('/usr/bin/newaliases > /dev/null') !== false);
    }

    /**
     * Generic part: should be written first
     *
     * @param resource $fp A file system pointer resource that is typically created using fopen().
     *
     * @return bool
     */
    protected function writeGenericAliases($fp)
    {
        fwrite($fp, "# This file is autogenerated - Do not edit\n\n");
        fwrite($fp, "# The Tuleap wide aliases (specific to Tuleap) resides in this file\n");
        fwrite($fp, "# All system wide aliases remains in /etc/aliases\n\n");
        fwrite($fp, "# Tuleap wide aliases\n\n");
        fwrite($fp, 'codendi-contact:         ' . self::ADMIN_ALIAS . "\n\n");
        fwrite($fp, 'codex-contact:           ' . self::ADMIN_ALIAS . "\n");// deprecated user name
        fwrite($fp, 'codex-admin:             ' . self::ADMIN_ALIAS . "\n");// deprecated user name
        fwrite($fp, 'sourceforge:             ' . self::ADMIN_ALIAS . "\n");// deprecated user name
        fwrite($fp, $this->getHTTPUser() . ':               ' . self::ADMIN_ALIAS . "\n");
        fwrite($fp, "noreply:                 \"|/bin/true\"\n");
        fwrite($fp, "undisclosed-recipients:  \"|/bin/true\"\n"); // for phpWiki notifications...
        fwrite($fp, 'webmaster:               ' . self::ADMIN_ALIAS . "\n");
        return fwrite($fp, "\n\n");
    }

    private function writeOtherAliases($fp)
    {
        $aliases = [];
        EventManager::instance()->processEvent(
            Event::BACKEND_ALIAS_GET_ALIASES,
            [
                'aliases' => &$aliases,
            ]
        );

        foreach ($aliases as $alias) {
            $this->writeAlias($fp, $alias);
        }

        return fwrite($fp, "\n\n");
    }

    private function writeAlias($fp, System_Alias $alias)
    {
        $name  = str_replace(['"', "\n"], '', $alias->getName());
        $value = str_replace("\n", '', $alias->getValue());
        fwrite($fp, sprintf(self::ALIAS_ENTRY_FORMAT, '"' . $name . '":', $value . "\n"));
    }
}
