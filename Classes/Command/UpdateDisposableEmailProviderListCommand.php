<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Command;

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
        $this->setHelp('This command does nothing. It always succeeds.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extConf = $this->extensionConfiguration->get('disposable_email');

        switch ($extConf['type'])
        {
            case 'disposable':
                $lists = self::ENDPOINT_LISTS;
                break;
            case 'freemail':
                $lists = self::FREE_EMAIL_LISTS;
                break;
            case 'both':
                $lists = array_merge(self::ENDPOINT_LISTS, self::FREE_EMAIL_LISTS);
                break;
            case 'customListsOnly':
                $lists = [];
                break;
            default:
                throw new \RuntimeException(
                    'Extension configuration list type is missing!'
                );
        }

        if (empty($extConf['customLists'] ?? []) === false)
        {
            $customLists = GeneralUtility::trimExplode(',', $extConf['customLists'], true);
            $lists = array_merge($lists, $customLists);
        }

        $data = [];

        foreach ($lists as $list)
        {
            $res = $this->getList($list);
            $data = array_merge($data, $res);
        }

        // transform to match bulk import
        $formattedData = array_map(function ($value)
        {
            return [
                self::FIELD_NAME => $value
            ];
        }, array_flip(array_flip($data))); // array_flip is more performant than array_unique

        // truncate table
        $this->connectionPool
            ->getConnectionForTable(self::TABLE_NAME)
            ->truncate(self::TABLE_NAME);

        $chunks = array_chunk($formattedData, 10000);
        foreach ($chunks as $chunk)
        {
            // insert data
            $queryBuilder = $this->connectionPool
                ->getQueryBuilderForTable(self::TABLE_NAME);
            $queryBuilder->getConnection()->bulkInsert(
                self::TABLE_NAME,
                $chunk,
                [self::FIELD_NAME],
                [Connection::PARAM_STR]
            );
        }
        return Command::SUCCESS;
    }

    protected function getList(string $endpoint): array
    {
        $response = $this->requestFactory->request($endpoint);

        if ($response->getStatusCode() !== 200)
        {
            throw new \RuntimeException(
                'Returned status code is ' . $response->getStatusCode(),
            );
        }

        if (strpos($response->getHeaderLine('Content-Type'), 'text/plain') !== 0)
        {
            throw new \RuntimeException(
                'The request did not return JSON data',
            );
        }

        $content = $response->getBody()->getContents();
        return GeneralUtility::trimExplode(LF, $content, true);
    }
}
