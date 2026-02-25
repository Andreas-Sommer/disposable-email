<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Tests\Unit\Utility;

use Belsignum\DisposableEmail\Utility\DomainNormalizationUtility;
use PHPUnit\Framework\TestCase;

final class DomainNormalizationUtilityTest extends TestCase
{
    public function testNormalizeDomainTrimsLowercasesAndRemovesTrailingDot(): void
    {
        self::assertSame('example.com', DomainNormalizationUtility::normalizeDomain('  ExAmPle.com.  '));
    }

    public function testNormalizeDomainReturnsEmptyStringForInvalidDomain(): void
    {
        self::assertSame('', DomainNormalizationUtility::normalizeDomain('not a domain'));
        self::assertSame('', DomainNormalizationUtility::normalizeDomain(''));
    }

    public function testNormalizeDomainConvertsIdnToAsciiWhenIntlIsAvailable(): void
    {
        if (!function_exists('idn_to_ascii')) {
            self::markTestSkipped('ext-intl is not available');
        }

        self::assertSame('xn--bcher-kva.de', DomainNormalizationUtility::normalizeDomain('bücher.de'));
    }
}
