<?php
/**
 * Copyright (c) Enalean, 2025-present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
 *
 *  Tuleap is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  Tuleap is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Artidoc\Upload\Section\File;

use Tuleap\Artidoc\Domain\Document\Artidoc;

final readonly class InsertFileToUpload
{
    private function __construct(
        public string $name,
        public int $size,
        public int $user_id,
        public ?int $expiration_date,
        public int $artidoc_id,
    ) {
    }

    public static function fromComponents(
        Artidoc $artidoc,
        string $name,
        int $size,
        \PFUser $user,
        ?\DateTimeImmutable $expiration_date,
    ): self {
        return new self(
            $name,
            $size,
            (int) $user->getId(),
            $expiration_date?->getTimestamp(),
            $artidoc->getId(),
        );
    }
}
