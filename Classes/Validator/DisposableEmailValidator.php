<?php

namespace Belsignum\DisposableEmail\Validator;

use Belsignum\DisposableEmail\Factory\DisposableEmailServiceFactory;
use Belsignum\DisposableEmail\Service\DisposableEmailService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DisposableEmailValidator {

    public function validate791($value, $validationConfiguration): bool
    {
        if(GeneralUtility::validEmail($value) === false)
        {
            return false;
        }

        /** @var DisposableEmailService $disposableEmailService */
        $disposableEmailService = GeneralUtility::makeInstance(DisposableEmailServiceFactory::class)->get();
        return $disposableEmailService->checkEmail($value) === false;
    }
}
