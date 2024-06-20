<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Disco;

use bitExpert\Disco\BeanException;
use bitExpert\Disco\BeanNotFoundException;
use bitExpert\Disco\Config\BeanConfiguration;
use bitExpert\Disco\Config\BeanConfigurationSubclass;
use bitExpert\Disco\Config\BeanConfigurationTrait;
use bitExpert\Disco\Config\BeanConfigurationWithAliases;
use bitExpert\Disco\Config\BeanConfigurationWithParameterizedPostProcessor;
use bitExpert\Disco\Config\BeanConfigurationWithParameters;
use bitExpert\Disco\Config\BeanConfigurationWithPostProcessor;
use bitExpert\Disco\Config\BeanConfigurationWithPostProcessorAndParameterizedDependency;
use bitExpert\Disco\Config\BeanConfigurationWithPrimitives;
use bitExpert\Disco\Config\BeanConfigurationWithProtectedMethod;
use bitExpert\Disco\Config\WrongReturnTypeConfiguration;
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;
use bitExpert\Disco\Helper\SampleServiceInterface;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use stdClass;

/**
 * Unit tests for {@link \bitExpert\Disco\AnnotationBeanFactory}.
 */
class AnnotationBeanFactoryUnitTest extends TestCase
{
    /**
     * @var AnnotationBeanFactory
     */
    private $beanFactory;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->beanFactory = new AnnotationBeanFactory(BeanConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);
    }

    /**
     * @test
     */
    public function retrievingNonExistentBeanThrowsException(): void
    {
        $this->expectException(BeanNotFoundException::class);

        $this->beanFactory->get('serviceWhichDoesNotExist');
    }

    /**
     * @test
     */
    public function retrievingBeanWithEmptyStringThrowsException(): void
    {
        $this->expectException(BeanException::class);

        $this->beanFactory->get('');
    }

    /**
     * @test
     */
    public function checkForExistingBeanReturnsTrue(): void
    {
        self::assertTrue($this->beanFactory->has('nonSingletonNonLazyRequestBean'));
    }

    /**
     * @test
     */
    public function checkForBeanWithEmptyIdWillReturnFalse(): void
    {
        self::assertFalse($this->beanFactory->has(''));
    }

    /**
     * @test
     */
    public function checkForNonExistingBeanReturnsFalse(): void
    {
        self::assertFalse($this->beanFactory->has('serviceWhichDoesNotExist'));
    }

    /**
     * @test
     */
    public function retrievingNonSingletonBeanReturnsDifferentInstances(): void
    {
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        $bean2 = $this->beanFactory->get('nonSingletonNonLazyRequestBean');

        self::assertNotSame($bean, $bean2);
    }

    /**
     * @test
     */
    public function retrievingNonSingletonLazyBeanReturnsDifferentInstances(): void
    {
        $bean = $this->beanFactory->get('nonSingletonLazyRequestBean');
        $bean2 = $this->beanFactory->get('nonSingletonLazyRequestBean');

        $bean->initializeProxy();
        $bean2->initializeProxy();

        self::assertInstanceOf(ValueHolderInterface::class, $bean);
        self::assertInstanceOf(ValueHolderInterface::class, $bean2);
        self::assertNotSame($bean->getWrappedValueHolderValue(), $bean2->getWrappedValueHolderValue());
    }

    /**
     * @test
     */
    public function retrievingSingletonLazyBeanReturnsTheSameInstances(): void
    {
        $bean = $this->beanFactory->get('singletonLazyRequestBean');
        $bean2 = $this->beanFactory->get('singletonLazyRequestBean');

        $bean->initializeProxy();
        $bean2->initializeProxy();

        self::assertInstanceOf(ValueHolderInterface::class, $bean);
        self::assertInstanceOf(ValueHolderInterface::class, $bean2);
        self::assertSame($bean->getWrappedValueHolderValue(), $bean2->getWrappedValueHolderValue());
    }

    /**
     * @test
     */
    public function retrievingSingletonNonLazySessionBeanReturnsDependencyAsProxy(): void
    {
        $bean = $this->beanFactory->get('singletonNonLazySessionBean');

        self::assertInstanceOf(VirtualProxyInterface::class, $bean->service);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function retrievingSingletonDependencyMatchesDirectBeanAccess(): void
    {
        $dependency = $this->beanFactory->get('singletonNonLazyRequestBean');
        $bean = $this->beanFactory->get('singletonNonLazySessionBean');
        $dependency2 = $this->beanFactory->get('singletonNonLazyRequestBean');

        $bean->service->initializeProxy();
        self::assertSame($dependency, $bean->service->getWrappedValueHolderValue());
        self::assertSame($dependency, $dependency2);
    }

    /**
     * @test
     */
    public function sessionBeanFetchesDependencyFromBeanFactoryDuringWakeup(): void
    {
        $dependency = $this->beanFactory->get('singletonNonLazyRequestBean');
        $beanBefore = $this->beanFactory->get('singletonNonLazySessionBean');
        $beanBefore->service->initializeProxy();

        $serialized = serialize($this->beanFactory);
        $this->beanFactory = unserialize($serialized);

        $beanAfter = $this->beanFactory->get('singletonNonLazySessionBean');
        $beanAfter->service->initializeProxy();

        self::assertSame($beanBefore, $beanAfter);
        self::assertSame($dependency, $beanBefore->service->getWrappedValueHolderValue());
        self::assertSame($dependency, $beanAfter->service->getWrappedValueHolderValue());
    }

    /**
     * @test
     */
    public function initializedBeanHookGetsCalledOnlyWhenBeanGetsCreated(): void
    {
        $bean = $this->beanFactory->get('singletonInitializedService');
        self::assertEquals(1, $bean->postInitCnt);

        // pulling the dependency a second time does not trigger the postInitialization() call!
        $bean = $this->beanFactory->get('singletonInitializedService');
        self::assertEquals(1, $bean->postInitCnt);
    }

    /**
     * @test
     */
    public function initializedBeanHookGetsCalledOnlyWhenLazyBeanGetsCreated(): void
    {
        $bean = $this->beanFactory->get('singletonLazyInitializedService');
        self::assertEquals(1, $bean->postInitCnt);

        // pulling the dependency a second time does not trigger the postInitialization() call!
        $bean = $this->beanFactory->get('singletonLazyInitializedService');
        self::assertEquals(1, $bean->postInitCnt);
    }

    /**
     * @test
     */
    public function postProcessorHookRunsAfterBeanCreation(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        self::assertEquals('postProcessed', $bean->test);
    }

    /**
     * @test
     */
    public function postProcessorHookRunsAfterLazyBeanCreation(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonLazyRequestBean');
        self::assertEquals('postProcessed', $bean->test);
    }

    /**
     * @test
     */
    public function beanFactoryPostProcessorAcceptsParameters(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameterizedPostProcessor::class,
            ['test' => 'injectedValue']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        /** @var SampleService $bean */
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        self::assertEquals('injectedValue', $bean->test);
    }

    /**
     * @test
     */
    public function beanFactoryPostProcessorCanBeConfiguredWithParameterizedDependency(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithPostProcessorAndParameterizedDependency::class,
            ['configKey1' => 'injectedValue1', 'configKey2' => 'injectedValue2']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        /** @var SampleService $bean */
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        self::assertInstanceOf(stdClass::class, $bean->test);
        self::assertEquals('injectedValue1', $bean->test->property1);
        self::assertEquals('injectedValue2', $bean->test->property2);
    }

    /**
     * @test
     */
    public function parameterPassedToBeanFactoryGetsInjectedInBean(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            ['configKey' => 'injectedValue']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithParam');
        self::assertEquals('injectedValue', $bean->test);
    }

    /**
     * @test
     */
    public function parametersPassedToBeanFactoryGetsInjectedInBeanWithPositionalParams(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            ['configKey1' => 'injectedValue1', 'configKey2' => 'injectedValue2']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithPositionalParams');
        self::assertEquals('injectedValue1', $bean->test);
        self::assertEquals('injectedValue2', $bean->anotherTest);
    }

    /**
     * @test
     */
    public function parametersPassedToBeanFactoryGetsInjectedInBeanWithNamedParams(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            ['configKey1' => 'injectedValue1', 'configKey2' => 'injectedValue2']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithNamedParams');
        self::assertEquals('injectedValue1', $bean->test);
        self::assertEquals('injectedValue2', $bean->anotherTest);
    }

    /**
     * @test
     */
    public function parametersPassedToBeanFactoryGetsInjectedInBeanWithMixedPositionalAndNamedParams(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            ['configKey1' => 'injectedValue1', 'configKey2' => 'injectedValue2']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithMixedPositionalAndNamedParams');
        self::assertEquals('injectedValue1', $bean->test);
        self::assertEquals('injectedValue2', $bean->anotherTest);
    }

    /**
     * @test
     */
    public function nestedParameterKeyPassedToBeanFactoryGetsInjectedInBean(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            [
                'config' => [
                    'nested' => [
                        'key' => 'injectedValue'
                    ]
                ]
            ]
        );

        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithNestedParamKey');
        self::assertEquals('injectedValue', $bean->test);
    }

    /**
     * @test
     */
    public function missingRequiredParameterWillThrowException(): void
    {
        $this->expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('sampleServiceWithParam');
    }

    /**
     * @test
     */
    public function missingOptionalParameter(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithoutRequiredParam');
        self::assertNull($bean->test);
    }

    /**
     * @test
     */
    public function defaultValueOfParameterDefinitionWillBeUsedWhenNoParameterWasGiven(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class, []);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithParamDefaultValue');
        self::assertEquals('myDefaultValue', $bean->test);
    }

    /**
     * @test
     */
    public function subclassConfigurationCanBeCorrectlyEvaluated(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationSubclass::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('singletonNonLazySessionBeanInSubclass');
        self::assertInstanceOf(MasterService::class, $bean);
    }

    /**
     * @test
     */
    public function traitConfigurationCanBeCorrectlyEvaluated(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationTrait::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBeanInTrait');
        self::assertInstanceOf(SampleService::class, $bean);
    }

    /**
     * @test
     */
    public function protectedSingletonDependencyAlwaysReturnsSameInstance(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithProtectedMethod::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean1 = $this->beanFactory->get('masterServiceWithSingletonDependency');
        $bean2 = $this->beanFactory->get('masterServiceWithSingletonDependency');

        self::assertSame($bean1->service, $bean2->service);
    }

    /**
     * @test
     */
    public function protectedNonSingletonDependencyReturnsDifferentInstance(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithProtectedMethod::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean1 = $this->beanFactory->get('masterServiceWithNonSingletonDependency');
        $bean2 = $this->beanFactory->get('masterServiceWithNonSingletonDependency');

        self::assertNotSame($bean1->service, $bean2->service);
    }

    /**
     * @test
     */
    public function protectedDependencyNotVisibleToTheCaller(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithProtectedMethod::class);
        BeanFactoryRegistry::register($this->beanFactory);

        self::assertFalse($this->beanFactory->has('singletonDependency'));
    }

    /**
     * @test
     */
    public function throwsExceptionIfTypeOfReturnedObjectIsNotExpectedOfNonLazyBean(): void
    {
        $this->expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('nonLazyBeanReturningSomethingWrong');
    }

    /**
     * @test
     */
    public function throwsExceptionIfNonLazyBeanMethodDoesNotReturnAnything(): void
    {
        $this->expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('nonLazyBeanNotReturningAnything');
    }

    /**
     * @test
     */
    public function throwsExceptionIfTypeOfReturnedObjectIsNotExpectedOfLazyBean(): void
    {
        $this->expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('lazyBeanReturningSomethingWrong');
        $bean->setTest('test');
    }

    /**
     * @test
     */
    public function throwsExceptionIfLazyBeanMethodDoesNotReturnAnything(): void
    {
        $this->expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('lazyBeanNotReturningAnything');
        $bean->setTest('test');
    }

    /**
     * @test
     */
    public function retrievingArrayPrimitive(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('arrayPrimitive');
        self::assertTrue(is_array($bean));
    }

    /**
     * @test
     */
    public function retrievingCallablePrimitive(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('callablePrimitive');
        self::assertTrue(is_callable($bean));
    }

    /**
     * @test
     */
    public function retrievingBoolPrimitive(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('boolPrimitive');
        self::assertTrue(is_bool($bean));
    }

    /**
     * @test
     */
    public function retrievingFlotPrimitive(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('floatPrimitive');
        self::assertTrue(is_float($bean));
    }

    /**
     * @test
     */
    public function retrievingIntPrimitive(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('intPrimitive');
        self::assertTrue(is_int($bean));
    }

    /**
     * @test
     */
    public function retrievingStringPrimitive(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('stringPrimitive');
        self::assertTrue(is_string($bean));
    }

    /**
     * @test
     */
    public function retrievingBeanWithStringInjected(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('serviceWithStringInjected');
        self::assertInstanceOf(SampleService::class, $bean);
        self::assertTrue(is_string($bean->test));
    }

    /**
     * @test
     * @dataProvider beanAliasProvider
     * @param string $beanId
     * @param class-string<Object> $beanType
     */
    public function retrievingBeanByAlias(string $beanId, string $beanType): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithAliases::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get($beanId);
        self::assertInstanceOf($beanType, $bean);
    }

    /**
     * @test
     */
    public function retrievingProtectedBeanByAlias(): void
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithAliases::class);
        BeanFactoryRegistry::register($this->beanFactory);

        self::assertFalse($this->beanFactory->has('internalServiceWithAlias'));
        self::assertTrue($this->beanFactory->has('aliasIsPublicForInternalService'));
        self::assertInstanceOf(SampleService::class, $this->beanFactory->get('aliasIsPublicForInternalService'));
    }

    /**
     * @return  array<int, array<int, string>>
     */
    public function beanAliasProvider(): array
    {
        return [
            ['\my\Custom\Namespace', SampleService::class],
            ['my::Custom::Namespace', SampleService::class],
            ['Alias_With_Underscore', SampleService::class],
            ['123456', SampleService::class],
            [SampleService::class, SampleService::class],
            [SampleServiceInterface::class, SampleService::class],
        ];
    }
}
