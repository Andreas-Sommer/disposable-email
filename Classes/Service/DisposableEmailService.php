<?php

namespace Belsignum\DisposableEmail\Service;

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

    public function checkEmail(string $email): bool
    {
        $domain = trim(substr($email, strpos($email, '@', 0) + 1));
        return $this->checkDomain($domain);
    }

    public function checkDomain(string $domain): bool
    {
        $result = $this->connectionPool
            ->getConnectionForTable(self::TABLE_NAME)
            ->select(
                ['uid'],
                self::TABLE_NAME,
                ['domain' => $domain],
            );

        return $result->fetchOne() !== false;
    }
}
