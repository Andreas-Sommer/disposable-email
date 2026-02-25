<?php

namespace Belsignum\DisposableEmail\Validator;

use Belsignum\DisposableEmail\Service\DisposableEmailService;
use Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Belsignum\DisposableEmail\Utility\PowermailValidationUtility;
use In2code\Powermail\Domain\Service\ConfigurationService;
use In2code\Powermail\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DisposableEmailValidator
{
    private DisposableEmailService $disposableEmailService;

    public function injectDisposableEmailService(
        DisposableEmailService $disposableEmailService
    ): void
    {
        $this->disposableEmailService = $disposableEmailService;
    }

    public function validate791($value, $validationConfiguration): bool
    {
        if (GeneralUtility::validEmail($value) === false)
        {
            return false;
        }

        $defaultListType = ExtensionConfigurationUtility::getListTypeFromExtensionConfiguration();
        $powermailSettings = [];

        try
        {
            $powermailSettings = ObjectUtility::getObjectManager()
                ->get(ConfigurationService::class)
                ->getTypoScriptSettings();
        }
        catch (\Throwable $exception)
        {
            // keep default list type from extension configuration
        }

        $powermailArguments = (array)GeneralUtility::_GPmerged('tx_powermail_pi1');
        $formUid = ListTypeConfiguration::getFormUidFromPowermailArguments($powermailArguments);
        // Resolve localized form uid to default-language uid for stable typeByForm mapping.
        $formUid = PowermailValidationUtility::getDefaultLanguageFormUid($formUid);
        $listType = ListTypeConfiguration::resolveListType($defaultListType, $powermailSettings, $formUid);
        if (ListTypeConfiguration::isValidationDisabled($listType))
        {
            return true;
        }

        return $this->disposableEmailService->checkEmail($value, $listType) === false;
    }
}
