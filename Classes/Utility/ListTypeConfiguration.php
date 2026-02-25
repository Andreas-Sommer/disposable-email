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

        if ($formUid !== null && $formUid > 0)
        {
            if (isset($typeByForm[$formUid]))
            {
                return self::normalizeOverrideType((string)$typeByForm[$formUid]);
            }
        }

        if (!empty($overrides['type']))
        {
            return self::normalizeOverrideType((string)$overrides['type']);
        }

        return $listType;
    }

    public static function isValidationDisabled(string $listType): bool
    {
        return self::normalizeOverrideType($listType) === self::LIST_TYPE_DISABLE;
    }

    public static function getProviderTypesByListType(string $listType): array
    {
        switch (self::normalizeOverrideType($listType))
        {
            case self::LIST_TYPE_DISABLE:
                return [];

            case self::LIST_TYPE_DISPOSABLE:
                return [self::PROVIDER_TYPE_DISPOSABLE, self::PROVIDER_TYPE_CUSTOM];

            case self::LIST_TYPE_FREEMAIL:
                return [self::PROVIDER_TYPE_FREEMAIL, self::PROVIDER_TYPE_CUSTOM];

            case self::LIST_TYPE_CUSTOM_LISTS_ONLY:
                return [self::PROVIDER_TYPE_CUSTOM];

            case self::LIST_TYPE_BOTH:
            default:
                return [self::PROVIDER_TYPE_DISPOSABLE, self::PROVIDER_TYPE_FREEMAIL, self::PROVIDER_TYPE_CUSTOM];
        }
    }

    public static function getFormUidFromPowermailArguments(array $powermailArguments): ?int
    {
        $formUid = (int)($powermailArguments['mail']['form'] ?? 0);
        return $formUid > 0 ? $formUid : null;
    }

    public static function normalizeListType(string $listType): string
    {
        switch ($listType)
        {
            case self::LIST_TYPE_DISABLE:
            case self::LIST_TYPE_DISPOSABLE:
            case self::LIST_TYPE_FREEMAIL:
            case self::LIST_TYPE_BOTH:
            case self::LIST_TYPE_CUSTOM_LISTS_ONLY:
                return $listType;

            default:
                return self::LIST_TYPE_BOTH;
        }
    }

    public static function normalizeOverrideType(string $listType): string
    {
        return self::normalizeListType($listType);
    }
}
