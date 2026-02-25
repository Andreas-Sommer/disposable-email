<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Tests\Functional;

use Belsignum\DisposableEmail\Service\DisposableEmailService;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class DisposableEmailServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'belsignum/disposable-email',
    ];

    public function testCheckDomainRespectsProviderTypesAndNormalizesInput(): void
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
        $connection->insert('tx_disposableemail_list', [
            'domain' => 'custom.test',
            'provider_type' => ListTypeConfiguration::PROVIDER_TYPE_CUSTOM,
        ]);

        $service = new DisposableEmailService($this->getConnectionPool());

        self::assertTrue($service->checkDomain(' ExAmPle.com. ', ListTypeConfiguration::LIST_TYPE_DISPOSABLE));
        self::assertTrue($service->checkDomain('example.com', ListTypeConfiguration::LIST_TYPE_FREEMAIL));
        self::assertFalse($service->checkDomain('example.com', ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY));
        self::assertTrue($service->checkDomain('custom.test', ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY));
    }
}
