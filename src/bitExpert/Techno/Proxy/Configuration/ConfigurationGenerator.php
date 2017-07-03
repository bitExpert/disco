<?php

/*
 * This file is part of the Techno package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace bitExpert\Techno\Proxy\Configuration;

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\BeanPostProcessor;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Annotations\Parameters;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\BeanMethod;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\BeanPostProcessorMethod;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\Constructor;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\GetAlias;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\GetParameter;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\HasAlias;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\MagicSleep;
use bitExpert\Techno\Proxy\Configuration\MethodGenerator\WrapBeanAsLazy;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\AliasesProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\ForceLazyInitProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\ParameterValuesProperty;
use bitExpert\Techno\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Exception;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;
use ReflectionMethod;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Reflection\MethodReflection;

/**
 * Generator for configuration classes.
 */
class ConfigurationGenerator implements ProxyGeneratorInterface
{
    /**
     * Creates a new {@link \bitExpert\Techno\Proxy\Configuration\ConfigurationGenerator}.
     */
    public function __construct()
    {
        // registers all required annotations
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Bean.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Alias.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/BeanPostProcessor.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Configuration.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Parameter.php');
    }

    /**
     * {@inheritDoc}
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $annotation = null;
        $forceLazyInitProperty = new ForceLazyInitProperty();
        $sessionBeansProperty = new SessionBeansProperty();
        $postProcessorsProperty = new BeanPostProcessorsProperty();
        $parameterValuesProperty = new ParameterValuesProperty();
        $beanFactoryConfigurationProperty = new BeanFactoryConfigurationProperty();
        $aliasesProperty = new AliasesProperty();
        $getParameterMethod = new GetParameter($originalClass, $parameterValuesProperty);
        $wrapBeanAsLazyMethod = new WrapBeanAsLazy($originalClass, $beanFactoryConfigurationProperty);

        try {
            $reader = new AnnotationReader();
            $annotation = $reader->getClassAnnotation($originalClass, Configuration::class);
        } catch (Exception $e) {
            throw new InvalidProxiedClassException($e->getMessage(), $e->getCode(), $e);
        }

        if (null === $annotation) {
            throw new InvalidProxiedClassException(
                sprintf(
                    '"%s" seems not to be a valid configuration class. @Configuration annotation missing!',
                    $originalClass->name
                )
            );
        }

        $classGenerator->setExtendedClass($originalClass->getName());
        $classGenerator->setImplementedInterfaces([AliasContainerInterface::class]);
        $classGenerator->addPropertyFromGenerator($forceLazyInitProperty);
        $classGenerator->addPropertyFromGenerator($sessionBeansProperty);
        $classGenerator->addPropertyFromGenerator($postProcessorsProperty);
        $classGenerator->addPropertyFromGenerator($parameterValuesProperty);
        $classGenerator->addPropertyFromGenerator($beanFactoryConfigurationProperty);
        $classGenerator->addPropertyFromGenerator($aliasesProperty);

        $postProcessorMethods = [];
        $aliases = [];
        $methods = $originalClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);
        foreach ($methods as $method) {
            $methodReflection = new MethodReflection(
                $method->class,
                $method->name
            );

            /* @var \bitExpert\Techno\Annotations\Bean $beanAnnotation */
            $beanAnnotation = $reader->getMethodAnnotation($method, Bean::class);
            if (null === $beanAnnotation) {
                /* @var \bitExpert\Techno\Annotations\Bean $beanAnnotation */
                $beanAnnotation = $reader->getMethodAnnotation($method, BeanPostProcessor::class);
                if ($beanAnnotation instanceof BeanPostProcessor) {
                    $postProcessorMethods[] = $method->name;

                    $proxyMethod = BeanPostProcessorMethod::generateMethod(
                        $methodReflection,
                        $beanAnnotation,
                        $getParameterMethod
                    );
                    $classGenerator->addMethodFromGenerator($proxyMethod);
                    continue;
                }

                // every method needs either @Bean or @PostPostprocessor annotation
                throw new InvalidProxiedClassException(
                    sprintf(
                        'Method "%s" on "%s" is missing the @Bean annotation!',
                        $method->name,
                        $originalClass->name
                    )
                );
            }

            foreach ($beanAnnotation->getAliases() as $beanAlias) {
                $alias = $beanAlias->isTypeAlias() ? (string) $method->getReturnType() : $beanAlias->getName();
                if (empty($alias)) {
                    continue;
                }

                if (isset($aliases[$alias])) {
                    throw new InvalidProxiedClassException(
                        sprintf(
                            'Alias "%s" of method "%s" on "%s" is already used by method "%s" of another Bean!'
                            . ' Did you use a type alias twice?',
                            $alias,
                            $method->name,
                            $originalClass->name,
                            $aliases[$alias]
                        )
                    );
                }

                $aliases[$alias] = $method->name;
            }

            $proxyMethod = BeanMethod::generateMethod(
                $methodReflection,
                $beanAnnotation,
                $method->getReturnType(),
                $forceLazyInitProperty,
                $sessionBeansProperty,
                $postProcessorsProperty,
                $beanFactoryConfigurationProperty,
                $getParameterMethod,
                $wrapBeanAsLazyMethod
            );

            $classGenerator->addMethodFromGenerator($proxyMethod);
        }

        $aliasesProperty->setDefaultValue($aliases);

        $classGenerator->addMethodFromGenerator(
            new Constructor(
                $originalClass,
                $parameterValuesProperty,
                $sessionBeansProperty,
                $beanFactoryConfigurationProperty,
                $postProcessorsProperty,
                $postProcessorMethods
            )
        );
        $classGenerator->addMethodFromGenerator($wrapBeanAsLazyMethod);
        $classGenerator->addMethodFromGenerator($getParameterMethod);
        $classGenerator->addMethodFromGenerator(
            new MagicSleep(
                $originalClass,
                $sessionBeansProperty
            )
        );
        $classGenerator->addMethodFromGenerator(
            new GetAlias(
                $originalClass,
                $aliasesProperty
            )
        );
        $classGenerator->addMethodFromGenerator(
            new HasAlias(
                $originalClass,
                $aliasesProperty
            )
        );
    }
}
