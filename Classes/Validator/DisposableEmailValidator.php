<?php

namespace Belsignum\DisposableEmail\Validator;

use Belsignum\DisposableEmail\Factory\DisposableEmailServiceFactory;
use Belsignum\DisposableEmail\Service\DisposableEmailService;
use Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Belsignum\DisposableEmail\Utility\PowermailValidationUtility;
use In2code\Powermail\Domain\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DisposableEmailValidator
{
    public function validate791($value, $validationConfiguration): bool
    {
        if (GeneralUtility::validEmail($value) === false) {
            return false;
        }

        /** @var DisposableEmailService $disposableEmailService */
        $disposableEmailService = GeneralUtility::makeInstance(DisposableEmailServiceFactory::class)->get();

        $defaultListType = ExtensionConfigurationUtility::getListTypeFromExtensionConfiguration();
        $powermailSettings = [];

        try {
            $powermailSettings = GeneralUtility::makeInstance(ConfigurationService::class)->getTypoScriptSettings();
        } catch (\Throwable $exception) {
            // keep default list type from extension configuration
        }

        $powermailArguments = (array)GeneralUtility::_GPmerged('tx_powermail_pi1');
        $formUid = ListTypeConfiguration::getFormUidFromPowermailArguments($powermailArguments);
        // Resolve localized form uid to default-language uid for stable typeByForm mapping.
        $formUid = PowermailValidationUtility::getDefaultLanguageFormUid($formUid);
        $listType = ListTypeConfiguration::resolveListType($defaultListType, $powermailSettings, $formUid);

        if (ListTypeConfiguration::isValidationDisabled($listType)) {
            return true;
        }

        return $disposableEmailService->checkEmail($value, $listType) === false;
    }
}
