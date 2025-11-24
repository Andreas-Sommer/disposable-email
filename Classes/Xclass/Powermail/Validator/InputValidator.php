<?php

namespace Belsignum\DisposableEmail\Xclass\Powermail\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Belsignum\DisposableEmail\Factory\DisposableEmailServiceFactory;
use Belsignum\DisposableEmail\Service\DisposableEmailService;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Validator\InputValidator as CoreInputValidator;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class InputValidator extends CoreInputValidator
{
    private DisposableEmailService $disposableEmailService;

    public function isValid($mail): void
    {
        // load once DisposableEmailService via factory due to lack of DI
        $this->disposableEmailService = GeneralUtility::makeInstance(DisposableEmailServiceFactory::class)->get();
        parent::isValid($mail);
    }

    protected function isValidFieldInStringValidation(Field $field, $value): void
    {
        // do powermail core validation
        parent::isValidFieldInStringValidation($field, $value);

        // do in addition custom validation
        if (!empty($value) && in_array($field->getType(), $this->stringValidationFieldTypes))
        {
            // only for valid email field
            if (
                $field->getValidation() === 1 // validation type email
                && $this->validateEmail($value) // check only when valid email
                && $this->disposableEmailService->checkEmail($value) // is disposable mail
            )
            {
                // show disposable email error
                $this->setErrorAndMessage(
                    $field,
                    LocalizationUtility::translate('validation.disposable_email.error', 'disposable_email')
                );
            }
        }
    }
}
