<?php

namespace Belsignum\DisposableEmail\Validator;

use Belsignum\DisposableEmail\Service\DisposableEmailService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DisposableEmailValidator {

    private DisposableEmailService $disposableEmailService;

    public function injectDisposableEmailService(
        DisposableEmailService $disposableEmailService
    ): void
    {
        $this->disposableEmailService = $disposableEmailService;
    }

    public function validate791($value, $validationConfiguration): bool
    {
        if(GeneralUtility::validEmail($value) === false)
        {
            return false;
        }
        return $this->disposableEmailService->checkEmail($value) === false;
    }
}
