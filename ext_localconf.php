<?php
if (!defined('TYPO3'))
{
    die ('Access denied.');
}

if (\Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility::isOverloadEmailValidationActive() === true)
{
    // overload email validation rule
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][In2code\Powermail\Domain\Validator\InputValidator::class] = [
        'className' => \Belsignum\DisposableEmail\Xclass\Powermail\Validator\InputValidator::class,
    ];
}
