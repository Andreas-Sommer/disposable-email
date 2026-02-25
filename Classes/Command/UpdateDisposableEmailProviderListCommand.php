<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Command;

use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UpdateDisposableEmailProviderListCommand extends Command
{
    protected const TABLE_NAME = 'tx_disposableemail_list';
    protected const FIELD_NAME = 'domain';
    protected const FIELD_PROVIDER_TYPE = 'provider_type';

    protected const ENDPOINT_LISTS = [
        'https://raw.githubusercontent.com/disposable/disposable-email-domains/master/domains_strict.txt',
        'https://raw.githubusercontent.com/groundcat/disposable-email-domain-list/master/domains.txt',
        'https://raw.githubusercontent.com/wesbos/burner-email-providers/master/emails.txt'
    ];

    protected const FREE_EMAIL_LISTS = [
        'https://gist.githubusercontent.com/humphreybc/d17e9215530684d6817ebe197c94a76b/raw/409d79d56965c365598d189aeb1ffba642f3770d/free_email_providers.conf',
        'https://gist.githubusercontent.com/okutbay/5b4974b70673dfdcc21c517632c1f984/raw/993a35930a8d24a1faab1b988d19d38d92afbba4/free_email_provider_domains.txt',
        'https://raw.githubusercontent.com/edwin-zvs/email-providers/master/email-providers.csv'
    ];

    protected ExtensionConfiguration $extensionConfiguration;
    protected RequestFactory $requestFactory;
    protected ConnectionPool $connectionPool;

    public function __construct(
        ExtensionConfiguration $extensionConfiguration,
        RequestFactory         $requestFactory,
        ConnectionPool         $connectionPool
    )
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $this->requestFactory = $requestFactory;
        $this->connectionPool = $connectionPool;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Updates the current list with remote endpoints.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extConf = $this->extensionConfiguration->get('disposable_email');
        $listType = ListTypeConfiguration::normalizeListType((string)($extConf['type'] ?? ''));

        $domainsByProviderType = [
            ListTypeConfiguration::PROVIDER_TYPE_DISPOSABLE => [],
            ListTypeConfiguration::PROVIDER_TYPE_FREEMAIL => [],
            ListTypeConfiguration::PROVIDER_TYPE_CUSTOM => [],
        ];

        if (
            in_array(
                $listType,
                [ListTypeConfiguration::LIST_TYPE_DISPOSABLE, ListTypeConfiguration::LIST_TYPE_BOTH],
                true
            )
        )
        {
            $domainsByProviderType[ListTypeConfiguration::PROVIDER_TYPE_DISPOSABLE] = $this->collectDomains(
                self::ENDPOINT_LISTS
            );
        }

        if (
            in_array(
                $listType,
                [ListTypeConfiguration::LIST_TYPE_FREEMAIL, ListTypeConfiguration::LIST_TYPE_BOTH],
                true
            )
        )
        {
            $domainsByProviderType[ListTypeConfiguration::PROVIDER_TYPE_FREEMAIL] = $this->collectDomains(
                self::FREE_EMAIL_LISTS
            );
        }

        if (
            ListTypeConfiguration::isValidationDisabled($listType) === false
            && empty($extConf['customLists'] ?? []) === false
        )
        {
            $customLists = GeneralUtility::trimExplode(',', (string)$extConf['customLists'], true);
            $domainsByProviderType[ListTypeConfiguration::PROVIDER_TYPE_CUSTOM] = $this->collectDomains($customLists);
        }

        $formattedData = [];
        foreach ($domainsByProviderType as $providerType => $domains)
        {
            foreach ($this->deduplicateDomains($domains) as $domain)
            {
                $formattedData[] = [
                    self::FIELD_NAME => $domain,
                    self::FIELD_PROVIDER_TYPE => $providerType,
                ];
            }
        }

        // truncate table
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $connection->truncate(self::TABLE_NAME);

        $chunks = array_chunk($formattedData, 10000);
        foreach ($chunks as $chunk)
        {
            $connection->bulkInsert(
                self::TABLE_NAME,
                $chunk,
                [self::FIELD_NAME, self::FIELD_PROVIDER_TYPE],
                [Connection::PARAM_STR, Connection::PARAM_STR]
            );
        }
        return Command::SUCCESS;
    }

    protected function collectDomains(array $lists): array
    {
        $domains = [];
        foreach ($lists as $list)
        {
            $domains = array_merge($domains, $this->getList($list));
        }

        return $domains;
    }

    protected function deduplicateDomains(array $domains): array
    {
        return array_flip(array_flip($domains)); // array_flip is more performant than array_unique
    }

    protected function getList(string $endpoint): array
    {
        // Security: custom list endpoints are restricted to absolute HTTPS URLs.
        $this->validateEndpointUrl($endpoint);
        $response = $this->requestFactory->request($endpoint);

        if ($response->getStatusCode() !== 200)
        {
            throw new \RuntimeException(
                'Returned status code is ' . $response->getStatusCode(),
            );
        }

        $contentType = strtolower($response->getHeaderLine('Content-Type'));
        if (strpos($contentType, 'text/plain') !== 0 && strpos($contentType, 'text/csv') !== 0)
        {
            throw new \RuntimeException(
                'The request did not return text/plain or text/csv data',
            );
        }

        $content = $response->getBody()->getContents();
        return GeneralUtility::trimExplode(LF, $content, true);
    }

    protected function validateEndpointUrl(string $endpoint): void
    {
        $parts = parse_url($endpoint);
        if (!is_array($parts))
        {
            throw new \RuntimeException(
                'Invalid endpoint URL for provider list.'
            );
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        $host = (string)($parts['host'] ?? '');

        if ($scheme !== 'https' || $host === '')
        {
            throw new \RuntimeException(
                'Only absolute HTTPS endpoints are allowed for provider lists.'
            );
        }
    }
}
