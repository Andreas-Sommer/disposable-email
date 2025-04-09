<?php

namespace Belsignum\DisposableEmail\Utility;

class ExtensionConfigurationUtility
{
    public static function isOverloadEmailValidationActive(): bool
    {
        $poermailLoaded = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('powermail');
        $overloadEmailValidation = (bool)(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('disposable_email')['overloadEmailValidation']);

        return (
            $poermailLoaded === true                // powermail loaded
            && $overloadEmailValidation === true    // but overloadEmailValidation === true
        );
    }
}
