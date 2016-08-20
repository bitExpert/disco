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

use bitExpert\Disco\Config\BeanConfiguration;
use bitExpert\Disco\Config\BeanConfigurationSubclass;
use bitExpert\Disco\Config\BeanConfigurationTrait;
use bitExpert\Disco\Config\BeanConfigurationWithAliases;
use bitExpert\Disco\Config\BeanConfigurationWithParameterizedPostProcessor;
use bitExpert\Disco\Config\BeanConfigurationWithParameters;
use bitExpert\Disco\Config\BeanConfigurationWithPostProcessor;
use bitExpert\Disco\Config\BeanConfigurationWithPrimitives;
use bitExpert\Disco\Config\BeanConfigurationWithProtectedMethod;
use bitExpert\Disco\Config\WrongReturnTypeConfiguration;
use bitExpert\Disco\Helper\BeanFactoryAwareService;
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use stdClass;

/**
 * Unit test for {@link \bitExpert\Disco\AnnotationBeanFactory}.
 */
class AnnotationBeanFactoryUnitTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException \bitExpert\Disco\BeanNotFoundException
     */
    public function retrievingNonExistentBeanThrowsException()
    {
        $this->beanFactory->get('serviceWhichDoesNotExist');
    }

    /**
     * @test
     */
    public function checkForExistingBeanReturnsTrue()
    {
        $this->assertTrue($this->beanFactory->has('nonSingletonNonLazyRequestBean'));
    }

    /**
     * @test
     */
    public function checkForBeanWithEmptyIdReturnsFalse()
    {
        $this->assertFalse($this->beanFactory->has(''));
    }

    /**
     * @test
     */
    public function checkForNonExistingBeanReturnsFalse()
    {
        $this->assertFalse($this->beanFactory->has('serviceWhichDoesNotExist'));
    }

    /**
     * @test
     */
    public function retrievingNonSingletonBeanReturnsDifferentInstances()
    {
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        $bean2 = $this->beanFactory->get('nonSingletonNonLazyRequestBean');

        $this->assertNotSame($bean, $bean2);
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

        $this->assertInstanceOf(ValueHolderInterface::class, $bean);
        $this->assertInstanceOf(ValueHolderInterface::class, $bean2);
        $this->assertNotSame($bean->getWrappedValueHolderValue(), $bean2->getWrappedValueHolderValue());
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

        $this->assertInstanceOf(ValueHolderInterface::class, $bean);
        $this->assertInstanceOf(ValueHolderInterface::class, $bean2);
        $this->assertSame($bean->getWrappedValueHolderValue(), $bean2->getWrappedValueHolderValue());
    }

    /**
     * @test
     */
    public function retrievingSingletonNonLazySessionBeanReturnsDependencyAsProxy()
    {
        $bean = $this->beanFactory->get('singletonNonLazySessionBean');

        $this->assertInstanceOf(VirtualProxyInterface::class, $bean->service);
    }

    /**
     * @test
     */
    public function retrievingSingletonDependencyMatchesDirectBeanAccess()
    {
        $dependency = $this->beanFactory->get('singletonNonLazyRequestBean');
        $bean = $this->beanFactory->get('singletonNonLazySessionBean');

        $bean->service->initializeProxy();
        $this->assertSame($dependency, $bean->service->getWrappedValueHolderValue());
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

        $this->assertSame($beanBefore, $beanAfter);
        $this->assertSame($dependency, $beanBefore->service->getWrappedValueHolderValue());
        $this->assertSame($dependency, $beanAfter->service->getWrappedValueHolderValue());
    }

    /**
     * @test
     */
    public function initializedBeanHookGetsCalledOnlyWhenBeanGetsCreated()
    {
        $bean = $this->beanFactory->get('singletonInitializedService');
        $this->assertEquals(1, $bean->postInitCnt);

        // pulling the dependency a second time does not trigger the postInitialization() call!
        $bean = $this->beanFactory->get('singletonInitializedService');
        $this->assertEquals(1, $bean->postInitCnt);
    }

    /**
     * @test
     */
    public function initializedBeanHookGetsCalledOnlyWhenLazyBeanGetsCreated()
    {
        $bean = $this->beanFactory->get('singletonLazyInitializedService');
        $this->assertEquals(1, $bean->postInitCnt);

        // pulling the dependency a second time does not trigger the postInitialization() call!
        $bean = $this->beanFactory->get('singletonLazyInitializedService');
        $this->assertEquals(1, $bean->postInitCnt);
    }

    /**
     * @test
     */
    public function postProcessorHookRunsAfterBeanCreation()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        $this->assertEquals('postProcessed', $bean->test);
    }

    /**
     * @test
     */
    public function postProcessorHookRunsAfterLazyBeanCreation()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonLazyRequestBean');
        $this->assertEquals('postProcessed', $bean->test);
    }

    /**
     * @test
     */
    public function beanFactoryPostProcessorHookRuns()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPostProcessor::class);
        BeanFactoryRegistry::register($this->beanFactory);

        /** @var BeanFactoryAwareService $bean */
        $bean = $this->beanFactory->get('beanFactoryAwareBean');
        $this->assertInstanceOf(BeanFactory::class, $bean->getBeanFactory());
    }

    /**
     * @test
     */
    public function beanFactoryPostProcessorIsInitializedAfterParametersAreSet()
    {
        $this->beanFactory = new AnnotationBeanFactory(
            BeanConfigurationWithParameterizedPostProcessor::class,
            ['test' => 'injectedValue']
        );
        BeanFactoryRegistry::register($this->beanFactory);

        /** @var SampleService $bean */
        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBean');
        $this->assertInstanceOf(stdClass::class, $bean->test);
        $this->assertEquals('injectedValue', $bean->test->property);
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
        $this->assertEquals('injectedValue', $bean->test);
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
        $this->assertEquals('injectedValue', $bean->test);
    }

    /**
     * @test
     * @expectedException \bitExpert\Disco\BeanException
     */
    public function missingRequiredParameterWillThrowException()
    {
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
        $this->assertNull($bean->test);
    }

    /**
     * @test
     */
    public function defaultValueOfParameterDefinitionWillBeUsedWhenNoParameterWasGiven()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithParameters::class, []);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('sampleServiceWithParamDefaultValue');
        $this->assertEquals('myDefaultValue', $bean->test);
    }

    /**
     * @test
     */
    public function subclassConfigurationCanBeCorrectlyEvaluated()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationSubclass::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('singletonNonLazySessionBeanInSubclass');
        $this->assertInstanceOf(MasterService::class, $bean);
    }

    /**
     * @test
     */
    public function traitConfigurationCanBeCorrectlyEvaluated()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationTrait::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('nonSingletonNonLazyRequestBeanInTrait');
        $this->assertInstanceOf(SampleService::class, $bean);
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

        $this->assertSame($bean1->service, $bean2->service);
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

        $this->assertNotSame($bean1->service, $bean2->service);
    }
    /**
     * @test
     */
    public function protectedDependencyNotVisibleToTheCaller()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithProtectedMethod::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->assertFalse($this->beanFactory->has('singletonDependency'));
    }

    /**
     * @test
     */
    public function enablingProxyAutoloaderRegistersAdditionalAutoloader()
    {
        $useProxyAutoloader = true;
        $autoloaderFunctionsBeforeBeanFactoryInit = spl_autoload_functions();

        $beanFactoryConfig = new BeanFactoryConfiguration(sys_get_temp_dir(), null, null, $useProxyAutoloader);
        $this->beanFactory = new AnnotationBeanFactory(BeanConfiguration::class, [], $beanFactoryConfig);
        BeanFactoryRegistry::register($this->beanFactory);

        $autoloaderFunctionsAfterBeanFactoryInit = spl_autoload_functions();
        $this->assertTrue(
            count($autoloaderFunctionsBeforeBeanFactoryInit) + 1 === count($autoloaderFunctionsAfterBeanFactoryInit)
        );
    }

    /**
     * @test
     * @expectedException \bitExpert\Disco\BeanException
     */
    public function throwsExceptionIfTypeOfReturnedObjectIsNotExpectedOfNonLazyBean()
    {
        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('nonLazyBeanReturningSomethingWrong');
    }

    /**
     * @test
     * @expectedException \bitExpert\Disco\BeanException
     */
    public function throwsExceptionIfNonLazyBeanMethodDoesNotReturnAnything()
    {
        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->beanFactory->get('nonLazyBeanNotReturningAnything');
    }

    /**
     * @test
     * @expectedException \bitExpert\Disco\BeanException
     */
    public function throwsExceptionIfTypeOfReturnedObjectIsNotExpectedOfLazyBean()
    {
        $this->beanFactory = new AnnotationBeanFactory(WrongReturnTypeConfiguration::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('lazyBeanReturningSomethingWrong');
        $bean->setTest('test');
    }

    /**
     * @test
     * @expectedException \bitExpert\Disco\BeanException
     */
    public function throwsExceptionIfLazyBeanMethodDoesNotReturnAnything()
    {
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
        $this->assertTrue(is_array($bean));
    }

    /**
     * @test
     */
    public function retrievingCallablePrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('callablePrimitive');
        $this->assertTrue(is_callable($bean));
    }

    /**
     * @test
     */
    public function retrievingBoolPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('boolPrimitive');
        $this->assertTrue(is_bool($bean));
    }

    /**
     * @test
     */
    public function retrievingFlotPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('floatPrimitive');
        $this->assertTrue(is_float($bean));
    }

    /**
     * @test
     */
    public function retrievingIntPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('intPrimitive');
        $this->assertTrue(is_int($bean));
    }

    /**
     * @test
     */
    public function retrievingStringPrimitive()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('stringPrimitive');
        $this->assertTrue(is_string($bean));
    }

    /**
     * @test
     */
    public function retrievingBeanWithStringInjected()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithPrimitives::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $bean = $this->beanFactory->get('serviceWithStringInjected');
        $this->assertInstanceOf(SampleService::class, $bean);
        $this->assertTrue(is_string($bean->test));
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
        $this->assertInstanceOf($beanType, $bean);
    }

    /**
     * @test
     */
    public function retrievingProtectedBeanByAlias()
    {
        $this->beanFactory = new AnnotationBeanFactory(BeanConfigurationWithAliases::class);
        BeanFactoryRegistry::register($this->beanFactory);

        $this->assertFalse($this->beanFactory->has('internalServiceWithAlias'));
        $this->assertTrue($this->beanFactory->has('aliasIsPublicForInternalService'));
        $this->assertInstanceOf(SampleService::class, $this->beanFactory->get('aliasIsPublicForInternalService'));
    }

    public function beanAliasProvider()
    {
        return [
            ['\my\Custom\Namespace', SampleService::class],
            ['my::Custom::Namespace', SampleService::class],
            ['Alias_With_Underscore', SampleService::class],
            ['123456', SampleService::class],
        ];
    }
}
