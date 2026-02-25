<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Tests\Unit\Utility;

use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use PHPUnit\Framework\TestCase;

final class ListTypeConfigurationTest extends TestCase
{
    public function testNormalizeListTypeFallsBackToBothForUnknownValue(): void
    {
        self::assertSame(
            ListTypeConfiguration::LIST_TYPE_BOTH,
            ListTypeConfiguration::normalizeListType('unknown')
        );
    }

    public function testResolveListTypeUsesGlobalOverrideWhenNoFormOverrideExists(): void
    {
        $powermailSettings = [
            'tx_disposableemail' => [
                'overrideExtensionSettings' => [
                    'type' => ListTypeConfiguration::LIST_TYPE_FREEMAIL,
                ],
            ],
        ];

        self::assertSame(
            ListTypeConfiguration::LIST_TYPE_FREEMAIL,
            ListTypeConfiguration::resolveListType(ListTypeConfiguration::LIST_TYPE_DISPOSABLE, $powermailSettings, null)
        );
    }

    public function testResolveListTypePrefersTypeByFormOverride(): void
    {
        $powermailSettings = [
            'tx_disposableemail' => [
                'overrideExtensionSettings' => [
                    'type' => ListTypeConfiguration::LIST_TYPE_FREEMAIL,
                    'typeByForm' => [
                        123 => ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY,
                    ],
                ],
            ],
        ];

        self::assertSame(
            ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY,
            ListTypeConfiguration::resolveListType(ListTypeConfiguration::LIST_TYPE_DISPOSABLE, $powermailSettings, 123)
        );
    }

    public function testResolveListTypeWithOptionalOverrideUsesDefaultIfEmpty(): void
    {
        self::assertSame(
            ListTypeConfiguration::LIST_TYPE_DISPOSABLE,
            ListTypeConfiguration::resolveListTypeWithOptionalOverride(ListTypeConfiguration::LIST_TYPE_DISPOSABLE, ' ')
        );
    }

    public function testResolveListTypeWithOptionalOverrideAcceptsDisable(): void
    {
        self::assertSame(
            ListTypeConfiguration::LIST_TYPE_DISABLE,
            ListTypeConfiguration::resolveListTypeWithOptionalOverride(ListTypeConfiguration::LIST_TYPE_BOTH, 'disable')
        );
    }

    public function testIsValidationDisabled(): void
    {
        self::assertTrue(ListTypeConfiguration::isValidationDisabled(ListTypeConfiguration::LIST_TYPE_DISABLE));
        self::assertFalse(ListTypeConfiguration::isValidationDisabled(ListTypeConfiguration::LIST_TYPE_BOTH));
    }

    public function testGetProviderTypesByListType(): void
    {
        self::assertSame(
            [ListTypeConfiguration::PROVIDER_TYPE_CUSTOM],
            ListTypeConfiguration::getProviderTypesByListType(ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY)
        );

        self::assertSame(
            [ListTypeConfiguration::PROVIDER_TYPE_DISPOSABLE, ListTypeConfiguration::PROVIDER_TYPE_CUSTOM],
            ListTypeConfiguration::getProviderTypesByListType(ListTypeConfiguration::LIST_TYPE_DISPOSABLE)
        );
    }

    public function testGetFormUidFromPowermailArguments(): void
    {
        self::assertSame(
            42,
            ListTypeConfiguration::getFormUidFromPowermailArguments(['mail' => ['form' => '42']])
        );
        self::assertNull(ListTypeConfiguration::getFormUidFromPowermailArguments([]));
    }
}
