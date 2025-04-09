<?php

if (!defined('TYPO3_MODE'))
{
    die ('Access denied.');
}

if (\Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility::isOverloadEmailValidationActive() === false)
{
    // load individual validation rule
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:disposable_email/Configuration/TsConfig/powermail.tsconfig">'
    );
}
