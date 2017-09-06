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
    public function setUp()
    {
        parent::setUp();

        $this->beanFactory = new AnnotationBeanFactory(BeanConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);
    }

    /**
     * @test
     */
    public function retrievingNonExistentBeanThrowsException()
    {
        self::expectException(BeanNotFoundException::class);

        $this->beanFactory->get('serviceWhichDoesNotExist');
    }

    /**
     * @test
     */
    public function retrievingBeanWithEmptyStringThrowsException()
    {
        self::expectException(BeanException::class);

        $this->beanFactory->get('');
    }

    /**
     * @test
     */
    public function retrievingBeanWithNonStringThrowsException()
    {
        self::expectException(BeanException::class);

        $this->beanFactory->get(3);
    }

    /**
     * @test
     */
    public function checkForExistingBeanReturnsTrue()
    {
        self::assertTrue($this->beanFactory->has('nonSingletonNonLazyRequestBean'));
    }

    /**
     * @test
     */
    public function checkForBeanWithEmptyIdWillReturnFalse()
    {
        $this->assertFalse($this->beanFactory->has(''));
    }

    /**
     * @test
     */
    public function checkForBeanWithNonStringIdWillReturnFalse()
    {
        $this->assertFalse($this->beanFactory->has(1));
    }

    /**
     * @test
     */
    public function checkForNonExistingBeanReturnsFalse()
    {
        self::assertFalse($this->beanFactory->has('serviceWhichDoesNotExist'));
    }

    /**
     * @test
     */
    public function retrievingNonSingletonBeanReturnsDifferentInstances()
    {
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        $bean2 = $this->beanFactory->get('nonSingletonNonLazyRequestBean');

        self::assertNotSame($bean, $bean2);
    }

    /**
     * @test
     */
    public function retrievingNonSingletonLazyBeanReturnsDifferentInstances()
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
    public function retrievingSingletonLazyBeanReturnsTheSameInstances()
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
    public function retrievingSingletonNonLazySessionBeanReturnsDependencyAsProxy()
    {
        $bean = $this->beanFactory->get('singletonNonLazySessionBean');

        self::assertInstanceOf(VirtualProxyInterface::class, $bean->service);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function retrievingSingletonDependencyMatchesDirectBeanAccess()
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
    public function sessionBeanFetchesDependencyFromBeanFactoryDuringWakeup()
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
    public function initializedBeanHookGetsCalledOnlyWhenBeanGetsCreated()
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
    public function initializedBeanHookGetsCalledOnlyWhenLazyBeanGetsCreated()
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
    public function postProcessorHookRunsAfterBeanCreation()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        self::assertEquals('postProcessed', $bean->test);
    }

    /**
     * @test
     */
    public function postProcessorHookRunsAfterLazyBeanCreation()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonLazyRequestBean');
        self::assertEquals('postProcessed', $bean->test);
    }

    /**
     * @test
     */
    public function beanFactoryPostProcessorAcceptsParameters()
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
    public function beanFactoryPostProcessorCanBeConfiguredWithParameterizedDependency()
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithPostProcessorAndParameterizedDependency::class,
            ['test' => 'injectedValue']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        /** @var SampleService $bean */
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        self::assertInstanceOf(stdClass::class, $bean->test);
        self::assertEquals('injectedValue', $bean->test->property);
    }

    /**
     * @test
     */
    public function parameterPassedToBeanFactoryGetsInjectedInBean()
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            ['test' => 'injectedValue']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithParam');
        self::assertEquals('injectedValue', $bean->test);
    }

    /**
     * @test
     */
    public function nestedParameterKeyPassedToBeanFactoryGetsInjectedInBean()
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameters::class,
            [
                'test' => [
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
    public function missingRequiredParameterWillThrowException()
    {
        self::expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('sampleServiceWithParam');
    }

    /**
     * @test
     */
    public function missingOptionalParameter()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithoutRequiredParam');
        self::assertNull($bean->test);
    }

    /**
     * @test
     */
    public function defaultValueOfParameterDefinitionWillBeUsedWhenNoParameterWasGiven()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class, []);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithParamDefaultValue');
        self::assertEquals('myDefaultValue', $bean->test);
    }

    /**
     * @test
     */
    public function subclassConfigurationCanBeCorrectlyEvaluated()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationSubclass::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('singletonNonLazySessionBeanInSubclass');
        self::assertInstanceOf(MasterService::class, $bean);
    }

    /**
     * @test
     */
    public function traitConfigurationCanBeCorrectlyEvaluated()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationTrait::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBeanInTrait');
        self::assertInstanceOf(SampleService::class, $bean);
    }

    /**
     * @test
     */
    public function protectedSingletonDependencyAlwaysReturnsSameInstance()
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
    public function protectedNonSingletonDependencyReturnsDifferentInstance()
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
    public function protectedDependencyNotVisibleToTheCaller()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithProtectedMethod::class);
        BeanFactoryRegistry::register($this->beanFactory);

        self::assertFalse($this->beanFactory->has('singletonDependency'));
    }

    /**
     * @test
     */
    public function throwsExceptionIfTypeOfReturnedObjectIsNotExpectedOfNonLazyBean()
    {
        self::expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('nonLazyBeanReturningSomethingWrong');
    }

    /**
     * @test
     */
    public function throwsExceptionIfNonLazyBeanMethodDoesNotReturnAnything()
    {
        self::expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('nonLazyBeanNotReturningAnything');
    }

    /**
     * @test
     */
    public function throwsExceptionIfTypeOfReturnedObjectIsNotExpectedOfLazyBean()
    {
        self::expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('lazyBeanReturningSomethingWrong');
        $bean->setTest('test');
    }

    /**
     * @test
     */
    public function throwsExceptionIfLazyBeanMethodDoesNotReturnAnything()
    {
        self::expectException(BeanException::class);

        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('lazyBeanNotReturningAnything');
        $bean->setTest('test');
    }

    /**
     * @test
     */
    public function retrievingArrayPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('arrayPrimitive');
        self::assertTrue(is_array($bean));
    }

    /**
     * @test
     */
    public function retrievingCallablePrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('callablePrimitive');
        self::assertTrue(is_callable($bean));
    }

    /**
     * @test
     */
    public function retrievingBoolPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('boolPrimitive');
        self::assertTrue(is_bool($bean));
    }

    /**
     * @test
     */
    public function retrievingFlotPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('floatPrimitive');
        self::assertTrue(is_float($bean));
    }

    /**
     * @test
     */
    public function retrievingIntPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('intPrimitive');
        self::assertTrue(is_int($bean));
    }

    /**
     * @test
     */
    public function retrievingStringPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('stringPrimitive');
        self::assertTrue(is_string($bean));
    }

    /**
     * @test
     */
    public function retrievingBeanWithStringInjected()
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
     */
    public function retrievingBeanByAlias($beanId, $beanType)
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithAliases::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get($beanId);
        self::assertInstanceOf($beanType, $bean);
    }

    /**
     * @test
     */
    public function retrievingProtectedBeanByAlias()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithAliases::class);
        BeanFactoryRegistry::register($this->beanFactory);

        self::assertFalse($this->beanFactory->has('internalServiceWithAlias'));
        self::assertTrue($this->beanFactory->has('aliasIsPublicForInternalService'));
        self::assertInstanceOf(SampleService::class, $this->beanFactory->get('aliasIsPublicForInternalService'));
    }

    public function beanAliasProvider()
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
