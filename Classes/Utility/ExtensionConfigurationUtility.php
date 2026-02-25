<?php

namespace Belsignum\DisposableEmail\Utility;

class ExtensionConfigurationUtility
{
    public static function isOverloadEmailValidationActive(): bool
    {
        $powermailLoaded = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('powermail');
        $overloadEmailValidation = (bool)(self::getExtensionConfiguration()['overloadEmailValidation'] ?? false);

        return (
            $powermailLoaded === true                // powermail loaded
            && $overloadEmailValidation === true    // but overloadEmailValidation === true
        );
    }

    public static function getExtensionConfiguration(): array
    {
        return (array)\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('disposable_email');
    }

    public static function getListTypeFromExtensionConfiguration(): string
    {
        return ListTypeConfiguration::normalizeListType((string)(self::getExtensionConfiguration()['type'] ?? ''));
    }
}
