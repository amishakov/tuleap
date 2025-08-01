<?php
/**
 * Copyright (c) Enalean, 2015 - present. All Rights Reserved.
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

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use Tuleap\Tracker\Artifact\MailGateway\MailGatewayConfig;
use Tuleap\Tracker\FormElement\Field\String\StringField;
use Tuleap\Tracker\FormElement\Field\Text\TextField;
use Tuleap\Tracker\Test\Builders\Fields\StringFieldBuilder;
use Tuleap\Tracker\Test\Builders\Fields\TextFieldBuilder;
use Tuleap\Tracker\Test\Stub\Semantic\Description\RetrieveSemanticDescriptionFieldStub;
use Tuleap\Tracker\Tracker;

#[\PHPUnit\Framework\Attributes\DisableReturnValueGenerationForTestDoubles]
final class Tracker_ArtifactByEmailStatusTest extends \Tuleap\Test\PHPUnit\TestCase //phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
{
    private const int TRACKER_ID = 66;
    private Tracker&MockObject $tracker;
    private MailGatewayConfig&MockObject $tracker_plugin_conf;
    private RetrieveSemanticDescriptionFieldStub $retrieve_description_field;

    protected function setUp(): void
    {
        $this->tracker                    = $this->createMock(Tracker::class);
        $this->tracker_plugin_conf        = $this->createMock(MailGatewayConfig::class);
        $this->retrieve_description_field = RetrieveSemanticDescriptionFieldStub::build();
    }

    private function getByEmail(): Tracker_ArtifactByEmailStatus
    {
        return new Tracker_ArtifactByEmailStatus(
            $this->tracker_plugin_conf,
            $this->retrieve_description_field
        );
    }

    public function testItDoesNotAcceptArtifactByInsecureEmailWhenSemanticTitleIsNotDefined(): void
    {
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getTitleField')->willReturn(null);
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);

        self::assertFalse($this->getByEmail()->canCreateArtifact($this->tracker));
    }

    public function testItDoesNotAcceptArtifactByInsecureEmailWhenSemanticDescriptionIsNotDefined(): void
    {
        $field_title = $this->createMock(StringField::class);

        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getTitleField')->willReturn($field_title);
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);

        self::assertFalse($this->getByEmail()->canCreateArtifact($this->tracker));
    }

    public function testItAcceptsArtifactByInsecureEmailWhenSemanticIsDefined(): void
    {
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);
        $field_title       = StringFieldBuilder::aStringField(515)->inTracker($this->tracker)->build();
        $field_description = TextFieldBuilder::aTextField(82)->inTracker($this->tracker)->build();

        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getTitleField')->willReturn($field_title);
        $this->tracker->method('getFormElementFields')->willReturn([$field_title, $field_description]);
        $this->retrieve_description_field->withDescriptionField($field_description);

        self::assertTrue($this->getByEmail()->canCreateArtifact($this->tracker));
    }

    public function testItAcceptsArtifactByInsecureEmailWhenRequiredFieldsAreValid(): void
    {
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);
        $field_title       = StringFieldBuilder::aStringField(515)->inTracker($this->tracker)->build();
        $field_description = TextFieldBuilder::aTextField(82)->inTracker($this->tracker)->build();

        $this->tracker->method('getTitleField')->willReturn($field_title);
        $this->tracker->method('getFormElementFields')->willReturn([$field_title, $field_description]);
        $this->retrieve_description_field->withDescriptionField($field_description);

        self::assertTrue($this->getByEmail()->canCreateArtifact($this->tracker));
    }

    public function testItDoesNotAcceptArtifactByInsecureEmailWhenRequiredFieldsAreInvalid(): void
    {
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);
        $field_title       = StringFieldBuilder::aStringField(1)->inTracker($this->tracker)->build();
        $field_description = TextFieldBuilder::aTextField(2)->inTracker($this->tracker)->build();

        $this->tracker->method('getTitleField')->willReturn($field_title);

        $another_field = TextFieldBuilder::aTextField(3)
            ->inTracker($this->tracker)
            ->thatIsRequired()
            ->build();

        $this->tracker->method('getFormElementFields')->willReturn([$field_title, $another_field, $field_description]);
        $this->retrieve_description_field->withDescriptionField($field_description);

        self::assertFalse($this->getByEmail()->canCreateArtifact($this->tracker));
    }

    public function testItDoesNotCreateArtifactInTokenMode(): void
    {
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        self::assertFalse($this->getByEmail()->canCreateArtifact($this->tracker));
    }

    public function testItUpdatesArtifactInTokenMode(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(true);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(false);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        self::assertTrue($this->getByEmail()->canUpdateArtifactInTokenMode($this->tracker));
    }

    public function testItDoesNotUpdateArtifactInTokenModeWhenMailGatewayIsDisabled(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(false);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        self::assertFalse($this->getByEmail()->canUpdateArtifactInTokenMode($this->tracker));
    }

    public function testItUpdatesArtifactInTokenModeWhenMailGatewayIsInsecure(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        self::assertTrue($this->getByEmail()->canUpdateArtifactInTokenMode($this->tracker));
    }

    public function testItDoesNotUpdateArtifactInTokenModeWhenMailGatewayIsInsecureAndTrackerDisallowEmailGateway(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(false);

        self::assertFalse($this->getByEmail()->canUpdateArtifactInTokenMode($this->tracker));
    }

    public function testItUpdatesArtifactInInsecureMode(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);

        self::assertTrue($this->getByEmail()->canUpdateArtifactInInsecureMode($this->tracker));
    }

    public function testItDoesNotUpdateArtifactInInsecureModeWhenTrackerEmailGatewayIsDisabled(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(false);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(false);

        self::assertFalse($this->getByEmail()->canUpdateArtifactInInsecureMode($this->tracker));
    }

    public function testItDoesNotUpdateArtifactInInsecureModeWhenTokenModeIsEnabled(): void
    {
        $this->tracker_plugin_conf->method('isTokenBasedEmailgatewayEnabled')->willReturn(true);
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(false);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(false);

        self::assertFalse($this->getByEmail()->canUpdateArtifactInInsecureMode($this->tracker));
    }

    #[TestWith([false, false, false, true])]
    #[TestWith([true, true, false, true])]
    #[TestWith([true, true, true, false])]
    public function testItChecksFieldValidity(
        bool $is_title_required,
        bool $is_description_required,
        bool $is_another_field_required,
        bool $expected,
    ): void {
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);

        $field_title = $this->createMock(StringField::class);
        $field_title->method('getId')->willReturn(1);
        $field_title->method('isRequired')->willReturn($is_title_required);

        $field_description = $this->createMock(TextField::class);
        $field_description->method('getId')->willReturn(2);
        $field_description->method('isRequired')->willReturn($is_description_required);
        $field_description->method('getTrackerId')->willReturn(self::TRACKER_ID);

        $another_field = $this->createMock(TextField::class);
        $another_field->method('getId')->willReturn(3);
        $another_field->method('isRequired')->willReturn($is_another_field_required);

        $this->tracker->method('getTitleField')->willReturn($field_title);
        $this->tracker->method('getFormElementFields')->willReturn([$field_title, $another_field, $field_description]);

        $this->retrieve_description_field->withDescriptionField($field_description);
        self::assertSame($expected, $this->getByEmail()->isRequiredFieldsConfigured($this->tracker));
    }

    public function testIsSemanticConfiguredReturnsFalseIfNoTitle(): void
    {
        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getTitleField')->willReturn(null);
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);

        self::assertFalse($this->getByEmail()->isSemanticConfigured($this->tracker));
    }

    public function testIsSemanticConfiguredReturnsFalseIfNoDescription(): void
    {
        $field_title = $this->createMock(StringField::class);

        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getTitleField')->willReturn($field_title);
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);

        self::assertFalse($this->getByEmail()->isSemanticConfigured($this->tracker));
    }

    public function testIsSemanticConfiguredReturnsTrue(): void
    {
        $this->tracker->method('getId')->willReturn(self::TRACKER_ID);
        $field_title       = StringFieldBuilder::aStringField(242)->inTracker($this->tracker)->build();
        $field_description = TextFieldBuilder::aTextField(224)->inTracker($this->tracker)->build();

        $this->tracker_plugin_conf->method('isInsecureEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('isEmailgatewayEnabled')->willReturn(true);
        $this->tracker->method('getTitleField')->willReturn($field_title);
        $this->retrieve_description_field->withDescriptionField($field_description);

        self::assertTrue($this->getByEmail()->isSemanticConfigured($this->tracker));
    }
}
