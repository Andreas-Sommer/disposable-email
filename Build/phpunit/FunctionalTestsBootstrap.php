<?php

use TYPO3\TestingFramework\Core\Testbase;

(static function () {
    if (!getenv('typo3DatabaseDriver')) {
        putenv('typo3DatabaseDriver=pdo_sqlite');
    }

    if (!getenv('typo3DatabaseName')) {
        putenv('typo3DatabaseName=disposable_email_functional');
    }

    $testbase = new Testbase();
    $testbase->defineOriginalRootPath();
    $testbase->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/tests');
    $testbase->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/transient');
})();
