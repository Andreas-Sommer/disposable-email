<?php
if (!defined('TYPO3'))
{
    die ('Access denied.');
}

if (
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('powermail')
    && \Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility::isOverloadEmailValidationActive() === true
)
{
    // overload email validation rule for powermail email fields
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][In2code\Powermail\Domain\Validator\InputValidator::class] = [
        'className' => \Belsignum\DisposableEmail\Xclass\Powermail\Validator\InputValidator::class,
    ];
}
