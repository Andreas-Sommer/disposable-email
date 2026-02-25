<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Utility;

final class ListTypeConfiguration
{
    public const LIST_TYPE_DISABLE = 'disable';
    public const LIST_TYPE_DISPOSABLE = 'disposable';
    public const LIST_TYPE_FREEMAIL = 'freemail';
    public const LIST_TYPE_BOTH = 'both';
    public const LIST_TYPE_CUSTOM_LISTS_ONLY = 'customListsOnly';

    public const PROVIDER_TYPE_DISPOSABLE = 'disposable';
    public const PROVIDER_TYPE_FREEMAIL = 'freemail';
    public const PROVIDER_TYPE_CUSTOM = 'custom';

    public static function resolveListType(string $defaultListType, array $powermailSettings = [], ?int $formUid = null): string
    {
        $listType = self::normalizeListType($defaultListType);
        $overrides = (array)($powermailSettings['tx_disposableemail']['overrideExtensionSettings'] ?? []);
        $typeByForm = (array)($overrides['typeByForm'] ?? []);

        if ($formUid !== null && $formUid > 0 && isset($typeByForm[$formUid])) {
            return self::normalizeOverrideType((string)$typeByForm[$formUid]);
        }

        if (!empty($overrides['type'])) {
            return self::normalizeOverrideType((string)$overrides['type']);
        }

        return $listType;
    }

    public static function resolveListTypeWithOptionalOverride(string $defaultListType, ?string $overrideListType): string
    {
        $defaultListType = self::normalizeListType($defaultListType);
        $overrideListType = trim((string)$overrideListType);
        if ($overrideListType === '') {
            return $defaultListType;
        }

        return self::normalizeOverrideType($overrideListType);
    }

    public static function isValidationDisabled(string $listType): bool
    {
        return self::normalizeOverrideType($listType) === self::LIST_TYPE_DISABLE;
    }

    public static function getProviderTypesByListType(string $listType): array
    {
        return match (self::normalizeOverrideType($listType)) {
            self::LIST_TYPE_DISABLE => [],
            self::LIST_TYPE_DISPOSABLE => [self::PROVIDER_TYPE_DISPOSABLE, self::PROVIDER_TYPE_CUSTOM],
            self::LIST_TYPE_FREEMAIL => [self::PROVIDER_TYPE_FREEMAIL, self::PROVIDER_TYPE_CUSTOM],
            self::LIST_TYPE_CUSTOM_LISTS_ONLY => [self::PROVIDER_TYPE_CUSTOM],
            default => [self::PROVIDER_TYPE_DISPOSABLE, self::PROVIDER_TYPE_FREEMAIL, self::PROVIDER_TYPE_CUSTOM],
        };
    }

    public static function getFormUidFromPowermailArguments(array $powermailArguments): ?int
    {
        $formUid = (int)($powermailArguments['mail']['form'] ?? 0);
        return $formUid > 0 ? $formUid : null;
    }

    public static function normalizeListType(string $listType): string
    {
        return match ($listType) {
            self::LIST_TYPE_DISABLE,
            self::LIST_TYPE_DISPOSABLE,
            self::LIST_TYPE_FREEMAIL,
            self::LIST_TYPE_BOTH,
            self::LIST_TYPE_CUSTOM_LISTS_ONLY => $listType,
            default => self::LIST_TYPE_BOTH,
        };
    }

    public static function normalizeOverrideType(string $listType): string
    {
        return self::normalizeListType($listType);
    }
}
