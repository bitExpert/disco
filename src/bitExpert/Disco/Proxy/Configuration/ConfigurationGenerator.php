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

namespace bitExpert\Disco\Proxy\Configuration;

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\BeanPostProcessor;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanMethod;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanPostProcessorMethod;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\Constructor;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\GetAlias;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\GetParameter;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\HasAlias;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\MagicSleep;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\WrapBeanAsLazy;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\AliasesProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ForceLazyInitProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ParameterValuesProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
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
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\ConfigurationGenerator}.
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
        $parentAliases = [];
        $localAliases = [];
        $methods = $originalClass->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);
        foreach ($methods as $method) {
            $methodReflection = new MethodReflection(
                $method->class,
                $method->name
            );

            /* @var \bitExpert\Disco\Annotations\Bean $beanAnnotation */
            $beanAnnotation = $reader->getMethodAnnotation($method, Bean::class);
            if (null === $beanAnnotation) {
                /* @var \bitExpert\Disco\Annotations\Bean $beanAnnotation */
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

                if ($method->isProtected()) {
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

                $hasAlias = '';
                if ($method->getDeclaringClass()->name === $originalClass->name) {
                    $hasAlias = $localAliases[$alias] ?? '';
                } else {
                    $hasAlias= $parentAliases[$alias] ?? '';
                }

                if ($hasAlias !== '') {
                    throw new InvalidProxiedClassException(
                        sprintf(
                            'Alias "%s" of method "%s" on "%s" is already used by method "%s" of another Bean!'
                            . ' Did you use a type alias twice?',
                            $alias,
                            $method->name,
                            $originalClass->name,
                            $hasAlias
                        )
                    );
                }

                if ($method->getDeclaringClass()->name === $originalClass->name) {
                    $localAliases[$alias] = $method->name;
                } else {
                    $parentAliases[$alias] = $method->name;
                }
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

        $aliasesProperty->setDefaultValue($parentAliases + $localAliases);

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
