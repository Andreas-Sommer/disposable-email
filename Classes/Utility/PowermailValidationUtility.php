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
        if ($formUid === null || $formUid <= 0) {
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
            ->executeQuery()
            ->fetchAssociative();

        $parentUid = (int)($row['l10n_parent'] ?? 0);
        return $parentUid > 0 ? $parentUid : $formUid;
    }

    public static function getPowermailValidationErrorMessage(string $listType, ?string $languageCode = null): string
    {
        $translationKey = 'validation.disposable_email.error.' . self::getMessageTypeSuffix($listType);

        return LocalizationUtility::translate($translationKey, 'disposable_email', null, $languageCode)
            ?? LocalizationUtility::translate('validation.disposable_email.error', 'disposable_email', null, $languageCode)
            ?? 'Disposable email validation failed.';
    }

    public static function getCmsFormValidationErrorMessageKey(string $listType): string
    {
        return 'validator.disposableEmail.error.' . self::getMessageTypeSuffix($listType);
    }

    private static function getMessageTypeSuffix(string $listType): string
    {
        return match (ListTypeConfiguration::normalizeOverrideType($listType)) {
            ListTypeConfiguration::LIST_TYPE_DISPOSABLE => 'disposable',
            ListTypeConfiguration::LIST_TYPE_FREEMAIL => 'freemail',
            ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY => 'custom',
            default => 'both',
        };
    }
}
