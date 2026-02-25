<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Tests\Functional;

use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class SchemaIntegrityTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'belsignum/disposable-email',
    ];

    public function testUniqueIndexIsScopedByDomainAndProviderType(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_disposableemail_list');

        $connection->insert('tx_disposableemail_list', [
            'domain' => 'example.com',
            'provider_type' => ListTypeConfiguration::PROVIDER_TYPE_DISPOSABLE,
        ]);
        $connection->insert('tx_disposableemail_list', [
            'domain' => 'example.com',
            'provider_type' => ListTypeConfiguration::PROVIDER_TYPE_FREEMAIL,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);
        $connection->insert('tx_disposableemail_list', [
            'domain' => 'example.com',
            'provider_type' => ListTypeConfiguration::PROVIDER_TYPE_FREEMAIL,
        ]);
    }
}
