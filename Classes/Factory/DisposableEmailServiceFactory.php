<?php

namespace Belsignum\DisposableEmail\Factory;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Belsignum\DisposableEmail\Service\DisposableEmailService;

class DisposableEmailServiceFactory
{
    protected ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    public function get(): DisposableEmailService
    {
        return GeneralUtility::makeInstance(DisposableEmailService::class, $this->connectionPool);
    }
}
