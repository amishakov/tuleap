<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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

namespace Tuleap\User\OAuth2\AccessToken;

use LogicException;
use Tuleap\Authentication\Scope\AuthenticationScope;
use Tuleap\Authentication\Scope\AuthenticationScopeDefinition;
use Tuleap\Authentication\Scope\AuthenticationScopeIdentifier;
use Tuleap\User\OAuth2\Scope\OAuth2ScopeIdentifier;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class OAuth2AccessTokenDoesNotHaveRequiredScopeExceptionTest extends \Tuleap\Test\PHPUnit\TestCase
{
    public function testRequiredScopeIsKept(): void
    {
        $scope = new class /** @psalm-immutable */ implements AuthenticationScope
        {
            #[\Override]
            public static function fromItself(): AuthenticationScope
            {
                throw new LogicException('Not Supposed to be called in the test');
            }

            #[\Override]
            public static function fromIdentifier(AuthenticationScopeIdentifier $identifier): ?AuthenticationScope
            {
                throw new LogicException('Not Supposed to be called in the test');
            }

            #[\Override]
            public function getIdentifier(): AuthenticationScopeIdentifier
            {
                return OAuth2ScopeIdentifier::fromIdentifierKey('foo');
            }

            #[\Override]
            public function getDefinition(): AuthenticationScopeDefinition
            {
                return new class /** @psalm-immutable */ implements AuthenticationScopeDefinition
                {
                    #[\Override]
                    public function getName(): string
                    {
                        return 'Test';
                    }

                    #[\Override]
                    public function getDescription(): string
                    {
                        return 'For test';
                    }
                };
            }

            #[\Override]
            public function covers(AuthenticationScope $scope): bool
            {
                throw new LogicException('Not Supposed to be called in the test');
            }
        };

        $exception = new OAuth2AccessTokenDoesNotHaveRequiredScopeException($scope);
        self::assertSame($scope, $exception->getNeededScope());
    }
}
