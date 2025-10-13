<?php

/**
 * Validator to check if a given email address is disposable.
 *
 * This class extends the AbstractValidator class and utilizes the DisposableEmailServiceFactory
 * to determine if an email address belongs to a disposable email provider.
 * The validator ensures the email is syntactically valid and blocks disposable email addresses.
 */

namespace Belsignum\DisposableEmail\Form\Validator;

use Belsignum\DisposableEmail\Factory\DisposableEmailServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class DisposableEmailValidator extends AbstractValidator
{
    /**
     * Validate the given value.
     *
     * @param mixed $value
     */
    public function isValid(mixed $value): void
    {
        // Syntactic email validation first
        if (!is_string($value) || !GeneralUtility::validEmail($value)) {
            // do not display error while this is done by email validator, but stop executing!
            return;
        }

        $disposableEmailService = GeneralUtility::makeInstance(DisposableEmailServiceFactory::class)->get();
        if ($disposableEmailService->checkEmail($value) === true) {
            $type = (bool)(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
            )->get('disposable_email')['overloadEmailValidation']);


            $this->addError(
                $this->translateErrorMessage('validator.disposableEmail.error', 'disposable_email')
                ?: 'For collaboration with business partners, we kindly ask you to use a business email address. Addresses from disposable or free providers cannot be considered.',
                170000791
            );
        }
    }
}
