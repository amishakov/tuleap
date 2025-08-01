<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Artidoc\Stubs\Document;

use Tuleap\Artidoc\Document\SaveConfiguration;
use Tuleap\Artidoc\Domain\Document\Section\Field\ArtifactSectionField;

final class SaveConfigurationStub implements SaveConfiguration
{
    /** @psalm-var callable(int, int, ArtifactSectionField[]): void */
    private $callback;

    private function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @psalm-param callable(int, int, ArtifactSectionField[]): void $callback
     */
    public static function withCallback(callable $callback): self
    {
        return new self($callback);
    }

    public static function noop(): self
    {
        return new self(self::doNothing(...));
    }

    private static function doNothing(): void
    {
        //Do nothing
    }

    #[\Override]
    public function saveConfiguration(int $item_id, int $tracker_id, array $fields): void
    {
        ($this->callback)($item_id, $tracker_id, $fields);
    }
}
