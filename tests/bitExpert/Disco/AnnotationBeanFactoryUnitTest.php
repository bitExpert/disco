<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco;

use bitExpert\Disco\Config\BeanConfiguration;
use bitExpert\Disco\Config\BeanConfigurationSubclass;
use bitExpert\Disco\Config\BeanConfigurationTrait;
use bitExpert\Disco\Config\BeanConfigurationWithParameters;
use bitExpert\Disco\Config\BeanConfigurationWithPostProcessor;
use bitExpert\Disco\Config\BeanConfigurationWithProtectedMethod;
use bitExpert\Disco\Helper\BeanFactoryAwareService;
use bitExpert\Disco\Helper\MasterService;
use bitExpert\Disco\Helper\SampleService;
use ProxyManager\Proxy\ValueHolderInterface;
use ProxyManager\Proxy\VirtualProxyInterface;

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
    public function factoryBeanWillReturnRealInstanceNotTheFactoryItself()
    {
        $bean = $this->beanFactory->get('nonLazyFactoryBean');
        $this->assertNotInstanceOf(FactoryBean::class, $bean);
    }

    /**
     * @test
     */
    public function lazyFactoryBeanWillReturnRealInstanceNotTheProxyItself()
    {
        $bean = $this->beanFactory->get('lazyFactoryBean');
        $this->assertNotInstanceOf(FactoryBean::class, $bean);
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
}
