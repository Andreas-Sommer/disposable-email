<?php

namespace Belsignum\DisposableEmail\Form\Validator;

use Doctrine\DBAL\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;


class ExistingFeUserValidator extends AbstractValidator
{
    protected $supportedOptions = [
        'feUsersPids' => ['', 'Frontend Users Repository PID (comma seperated)', 'string']
    ];

    /**
     * Validate the given value.
     *
     * @param mixed $value
     */
    public function isValid(mixed $value): void
    {
        // Syntactic email validation first
        if (!is_string($value) || !GeneralUtility::validEmail($value)) {
            // do not display error while this is done by email validator, but stop executing!
            return;
        }

        // 2) Optional fe_users existence check, only if PIDs given
        $pidList = trim((string)($this->options['feUsersPids'] ?? ''));
        if ($pidList !== '') {
            $pids = GeneralUtility::intExplode(',', $pidList, true);

            if (!empty($pids)) {

                $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
                $qb = $connectionPool->getQueryBuilderForTable('fe_users');
                $expr = $qb->expr();

                $now = (new \DateTimeImmutable())->getTimestamp();

                $qb->select('uid')
                    ->from('fe_users')
                    ->where(
                        $expr->or(
                            $expr->eq('username', $qb->createNamedParameter($value)),
                            $expr->eq('email', $qb->createNamedParameter($value))
                        ),
                        $expr->in('pid', $qb->createNamedParameter($pids, Connection::PARAM_INT_ARRAY)),
                        $expr->eq('deleted', 0),
                        $expr->eq('disable', 0),
                    )
                    ->setMaxResults(1);

                $uid = $qb->executeQuery()->fetchOne();
                if ($uid !== false) {
                    $this->addError(
                        $this->translateErrorMessage('validator.existingFeUser.error', 'disposable_email')
                        ?: 'A user account already exists for this email address.',
                        170000792
                    );
                }
            }
        }
    }
}
