<?php

namespace Belsignum\DisposableEmail\Service;

use Belsignum\DisposableEmail\Utility\DomainNormalizationUtility;
use Belsignum\DisposableEmail\Utility\ExtensionConfigurationUtility;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

class DisposableEmailService
{
    private const TABLE_NAME = 'tx_disposableemail_list';
    protected ConnectionPool $connectionPool;

    public function __construct(
        ConnectionPool $connectionPool
    )
    {
        $this->connectionPool = $connectionPool;
    }

    public function checkEmail(string $email, ?string $listType = null): bool
    {
        $atPosition = strpos($email, '@', 0);
        if ($atPosition === false)
        {
            return false;
        }

        $domain = trim(substr($email, $atPosition + 1));
        if ($domain === '')
        {
            return false;
        }

        return $this->checkDomain($domain, $listType);
    }

    public function checkDomain(string $domain, ?string $listType = null): bool
    {
        $normalizedDomain = DomainNormalizationUtility::normalizeDomain($domain);
        if ($normalizedDomain === '')
        {
            return false;
        }

        $effectiveListType = $listType ?? ExtensionConfigurationUtility::getListTypeFromExtensionConfiguration();
        $providerTypes = ListTypeConfiguration::getProviderTypesByListType($effectiveListType);
        if ($providerTypes === [])
        {
            return false;
        }
        $queryBuilder = $this->connectionPool
            ->getConnectionForTable(self::TABLE_NAME)
            ->createQueryBuilder();
        $queryBuilder
            ->select('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'domain',
                    $queryBuilder->createNamedParameter($normalizedDomain)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->in(
                    'provider_type',
                    $queryBuilder->createNamedParameter($providerTypes, Connection::PARAM_STR_ARRAY)
                )
            )
            ->setMaxResults(1);

        return $queryBuilder->execute()->fetchOne() !== false;
    }
}
