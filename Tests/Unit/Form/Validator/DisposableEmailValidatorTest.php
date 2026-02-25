<?php
declare(strict_types=1);

namespace Belsignum\DisposableEmail\Tests\Unit\Form\Validator;

use Belsignum\DisposableEmail\Factory\DisposableEmailServiceFactory;
use Belsignum\DisposableEmail\Form\Validator\DisposableEmailValidator;
use Belsignum\DisposableEmail\Service\DisposableEmailService;
use Belsignum\DisposableEmail\Utility\ListTypeConfiguration;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DisposableEmailValidatorTest extends TestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    public function testListTypeOverrideIsUsedForValidation(): void
    {
        $service = $this->createMock(DisposableEmailService::class);
        $service->expects(self::once())
            ->method('checkEmail')
            ->with('user@example.com', ListTypeConfiguration::LIST_TYPE_FREEMAIL)
            ->willReturn(false);

        $factory = $this->createMock(DisposableEmailServiceFactory::class);
        $factory->expects(self::once())
            ->method('get')
            ->willReturn($service);

        $this->registerExtensionConfiguration(ListTypeConfiguration::LIST_TYPE_BOTH);
        GeneralUtility::addInstance(DisposableEmailServiceFactory::class, $factory);

        $validator = new TestableDisposableEmailValidator();
        $validator->setOptions(['listType' => ListTypeConfiguration::LIST_TYPE_FREEMAIL]);

        $result = $validator->validate('user@example.com');
        self::assertFalse($result->hasErrors());
    }

    public function testDisableOverrideSkipsDisposableEmailCheck(): void
    {
        $service = $this->createMock(DisposableEmailService::class);
        $service->expects(self::never())->method('checkEmail');

        $factory = $this->createMock(DisposableEmailServiceFactory::class);
        $factory->expects(self::never())->method('get');

        $this->registerExtensionConfiguration(ListTypeConfiguration::LIST_TYPE_BOTH);
        GeneralUtility::addInstance(DisposableEmailServiceFactory::class, $factory);

        $validator = new TestableDisposableEmailValidator();
        $validator->setOptions(['listType' => ListTypeConfiguration::LIST_TYPE_DISABLE]);

        $result = $validator->validate('user@example.com');
        self::assertFalse($result->hasErrors());
    }

    public function testErrorMessageKeyDependsOnListType(): void
    {
        $service = $this->createMock(DisposableEmailService::class);
        $service->expects(self::once())
            ->method('checkEmail')
            ->with('user@example.com', ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY)
            ->willReturn(true);

        $factory = $this->createMock(DisposableEmailServiceFactory::class);
        $factory->expects(self::once())
            ->method('get')
            ->willReturn($service);

        $this->registerExtensionConfiguration(ListTypeConfiguration::LIST_TYPE_BOTH);
        GeneralUtility::addInstance(DisposableEmailServiceFactory::class, $factory);

        $validator = new TestableDisposableEmailValidator();
        $validator->setOptions(['listType' => ListTypeConfiguration::LIST_TYPE_CUSTOM_LISTS_ONLY]);

        $result = $validator->validate('user@example.com');
        self::assertTrue($result->hasErrors());
        self::assertSame(
            'validator.disposableEmail.error.custom',
            $result->getFirstError()->getMessage()
        );
    }

    private function registerExtensionConfiguration(string $listType): void
    {
        $extensionConfiguration = $this->createMock(ExtensionConfiguration::class);
        $extensionConfiguration->method('get')
            ->with('disposable_email')
            ->willReturn(['type' => $listType]);
        GeneralUtility::addInstance(ExtensionConfiguration::class, $extensionConfiguration);
    }
}

final class TestableDisposableEmailValidator extends DisposableEmailValidator
{
    protected function translateErrorMessage(string $translateKey, string $extensionName = '', array $arguments = []): string
    {
        return $translateKey;
    }
}
