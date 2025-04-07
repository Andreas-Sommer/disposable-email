<?php

if (!defined('TYPO3_MODE'))
{
    die ('Access denied.');
}

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('powermail'))
{
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:disposable_email/Configuration/TsConfig/powermail.tsconfig">'
    );
}
