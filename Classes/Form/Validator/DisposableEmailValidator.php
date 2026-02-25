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
use Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Belsignum\DisposableEmail\Utility\PowermailValidationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class DisposableEmailValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'listType' => ['', 'Optional list type override (disable|disposable|freemail|both|customListsOnly)', 'string'],
    ];

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

        $defaultListType = ExtensionConfigurationUtility::getListTypeFromExtensionConfiguration();
        $listType = ListTypeConfiguration::resolveListTypeWithOptionalOverride(
            $defaultListType,
            (string)($this->options['listType'] ?? '')
        );

        if (ListTypeConfiguration::isValidationDisabled($listType)) {
            return;
        }

        $disposableEmailService = GeneralUtility::makeInstance(DisposableEmailServiceFactory::class)->get();
        if ($disposableEmailService->checkEmail($value, $listType) === true) {
            $this->addError(
                $this->translateErrorMessage(
                    PowermailValidationUtility::getCmsFormValidationErrorMessageKey($listType),
                    'disposable_email'
                ) ?: 'For collaboration with business partners, we kindly ask you to use a business email address. Addresses from disposable or free providers cannot be considered.',
                170000791
            );
        }
    }
}
