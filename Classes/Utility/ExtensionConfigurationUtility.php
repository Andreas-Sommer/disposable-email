<?php

namespace Belsignum\DisposableEmail\Utility;

class ExtensionConfigurationUtility
{
    public static function isOverloadEmailValidationActive(): bool
    {
        $powermailLoaded = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('powermail');
        $overloadEmailValidation = (bool)(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('disposable_email')['overloadEmailValidation']);

        return (
            $powermailLoaded === true                // powermail loaded
            && $overloadEmailValidation === true    // but overloadEmailValidation === true
        );
    }
}
