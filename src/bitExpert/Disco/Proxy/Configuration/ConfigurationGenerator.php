<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types = 1);

namespace bitExpert\Disco\Proxy\Configuration;

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\BeanPostProcessor;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanMethod;
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
    private const NATIVE_RETURN_TYPES = ['array', 'callable', 'bool', 'float', 'int', 'string'];

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
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Parameters.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Parameter.php');
    }

    /**
     * {@inheritDoc}
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
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
                    $originalClass->getName()
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
            if (null !== $reader->getMethodAnnotation($method, BeanPostProcessor::class)) {
                $postProcessorMethods[] = $method->getName();
                continue;
            }

            /* @var \bitExpert\Disco\Annotations\Bean $beanAnnotation */
            $beanAnnotation = $reader->getMethodAnnotation($method, Bean::class);
            if (null === $beanAnnotation) {
                throw new InvalidProxiedClassException(
                    sprintf(
                        'Method "%s" on "%s" is missing the @Bean annotation!',
                        $method->getName(),
                        $originalClass->getName()
                    )
                );
            }

            /* @var \bitExpert\Disco\Annotations\Parameters $parametersAnnotation */
            $parametersAnnotation = $reader->getMethodAnnotation($method, Parameters::class);
            if (null === $parametersAnnotation) {
                $parametersAnnotation = new Parameters();
            }

            $beanType = $method->getReturnType();
            if (null === $beanType) {
                throw new InvalidProxiedClassException(
                    sprintf(
                        'Method "%s" on "%s" is missing the return type hint!',
                        $method->getName(),
                        $originalClass->getName()
                    )
                );
            }

            $beanType = (string) $beanType;
            if (!in_array($beanType, self::NATIVE_RETURN_TYPES) &&
                !class_exists($beanType) &&
                !interface_exists($beanType)
            ) {
                throw new InvalidProxiedClassException(
                    sprintf(
                        'Return type of method "%s" on "%s" cannot be found! Did you use the full qualified name?',
                        $method->getName(),
                        $originalClass->getName()
                    )
                );
            }

            foreach ($beanAnnotation->getAliases() as $beanAlias) {
                if ($beanAlias->isTypeAlias()) {
                    if (in_array($beanType, self::NATIVE_RETURN_TYPES)) {
                        throw new InvalidProxiedClassException(
                            sprintf(
                                'Native PHP type cannot be used as alias. Check return type of method "%s" on "%s"!',
                                $method->getName(),
                                $originalClass->getName()
                            )
                        );
                    }

                    $alias = $beanType;
                } else {
                    $alias = $beanAlias->getName();
                }

                if (isset($aliases[$alias])) {
                    throw new InvalidProxiedClassException(
                        sprintf(
                            'Alias "%s" of method "%s" on "%s" is already used by another Bean!'
                            . ' Did you use a type alias twice?',
                            $alias,
                            $method->getName(),
                            $originalClass->getName()
                        )
                    );
                }

                $aliases[$alias] = $method->getName();
            }

            $methodReflection = new MethodReflection(
                $method->class,
                $method->getName()
            );
            $proxyMethod = BeanMethod::generateMethod(
                $methodReflection,
                $beanAnnotation,
                $parametersAnnotation,
                $beanType,
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
