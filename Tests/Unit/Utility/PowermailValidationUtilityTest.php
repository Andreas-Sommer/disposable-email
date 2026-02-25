<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Tests\Unit\Utility;

use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use Belsignum\DisposableEmail\Utility\PowermailValidationUtility;
use PHPUnit\Framework\TestCase;

final class PowermailValidationUtilityTest extends TestCase
{
    public function testCmsFormValidationMessageKeyUsesTypeSpecificSuffix(): void
    {
        self::assertSame(
            'validator.disposableEmail.error.disposable',
            PowermailValidationUtility::getCmsFormValidationErrorMessageKey(ListTypeConfiguration::LIST_TYPE_DISPOSABLE)
        );
        self::assertSame(
            'validator.disposableEmail.error.freemail',
            PowermailValidationUtility::getCmsFormValidationErrorMessageKey(ListTypeConfiguration::LIST_TYPE_FREEMAIL)
        );
        self::assertSame(
            'validator.disposableEmail.error.custom',
            PowermailValidationUtility::getCmsFormValidationErrorMessageKey(ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY)
        );
        self::assertSame(
            'validator.disposableEmail.error.both',
            PowermailValidationUtility::getCmsFormValidationErrorMessageKey(ListTypeConfiguration::LIST_TYPE_DISABLE)
        );
    }
}
