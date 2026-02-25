<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class PowermailValidationUtility
{
    public static function getDefaultLanguageFormUid(?int $formUid): ?int
    {
        if ($formUid === null || $formUid <= 0)
        {
            return null;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_powermail_domain_model_form');
        $row = $queryBuilder
            ->select('l10n_parent')
            ->from('tx_powermail_domain_model_form')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($formUid, \PDO::PARAM_INT)
                )
            )
            ->setMaxResults(1)
            ->execute()
            ->fetchAssociative();

        $parentUid = (int)($row['l10n_parent'] ?? 0);
        return $parentUid > 0 ? $parentUid : $formUid;
    }

    public static function getValidationErrorMessage(string $listType): string
    {
        $translationKey = 'validation.disposable_email.error.both';

        switch ($listType)
        {
            case ListTypeConfiguration::LIST_TYPE_DISABLE:
                $translationKey = 'validation.disposable_email.error.both';
                break;
            case ListTypeConfiguration::LIST_TYPE_DISPOSABLE:
                $translationKey = 'validation.disposable_email.error.disposable';
                break;
            case ListTypeConfiguration::LIST_TYPE_FREEMAIL:
                $translationKey = 'validation.disposable_email.error.freemail';
                break;
            case ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY:
                $translationKey = 'validation.disposable_email.error.custom';
                break;
        }

        return
            LocalizationUtility::translate($translationKey, 'disposable_email')
            ?? LocalizationUtility::translate('validation.disposable_email.error', 'disposable_email')
            ?? 'Disposable email validation failed.';
    }
}
