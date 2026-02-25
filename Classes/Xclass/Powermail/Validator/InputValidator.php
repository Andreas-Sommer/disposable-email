<?php

namespace Belsignum\DisposableEmail\Xclass\Powermail\Validator;

use Belsignum\DisposableEmail\Factory\DisposableEmailServiceFactory;
use Belsignum\DisposableEmail\Service\DisposableEmailService;
use Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Belsignum\DisposableEmail\Utility\PowermailValidationUtility;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Validator\InputValidator as CoreInputValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class InputValidator extends CoreInputValidator
{
    private DisposableEmailService $disposableEmailService;
    private string $listType = ListTypeConfiguration::LIST_TYPE_BOTH;

    public function isValid($mail): void
    {
        // load once DisposableEmailService via factory due to lack of DI
        $this->disposableEmailService = GeneralUtility::makeInstance(DisposableEmailServiceFactory::class)->get();

        $defaultListType = ExtensionConfigurationUtility::getListTypeFromExtensionConfiguration();
        $formUid = $mail->getForm() !== null ? (int)$mail->getForm()->getUid() : null;
        // Resolve localized form uid to default-language uid for stable typeByForm mapping.
        $formUid = PowermailValidationUtility::getDefaultLanguageFormUid($formUid);
        $this->listType = ListTypeConfiguration::resolveListType(
            $defaultListType,
            (array)$this->settings,
            $formUid
        );

        parent::isValid($mail);
    }

    protected function isValidFieldInStringValidation(Field $field, $value): void
    {
        // do powermail core validation
        parent::isValidFieldInStringValidation($field, $value);

        // do in addition custom validation
        if (!empty($value) && in_array($field->getType(), $this->stringValidationFieldTypes)) {
            if (ListTypeConfiguration::isValidationDisabled($this->listType)) {
                return;
            }

            // only for valid email field
            if (
                $field->getValidation() === 1 // validation type email
                && $this->validateEmail($value) // check only when valid email
                && $this->disposableEmailService->checkEmail($value, $this->listType) // is disposable mail
            ) {
                // show disposable email error
                $this->setErrorAndMessage(
                    $field,
                    PowermailValidationUtility::getPowermailValidationErrorMessage(
                        $this->listType,
                        $this->resolveLanguageCode()
                    )
                );
            }
        }
    }

    protected function resolveLanguageCode(): ?string
    {
        $typoscriptFrontendController = $this->typoscriptFrontendController();
        if ($typoscriptFrontendController === null || $typoscriptFrontendController->getLanguage() === null) {
            return null;
        }

        return $typoscriptFrontendController->getLanguage()->getLocale()->getLanguageCode();
    }

    protected function typoscriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
