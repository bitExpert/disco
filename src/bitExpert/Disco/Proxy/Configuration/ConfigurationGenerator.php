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

use Attribute;
use bitExpert\Disco\Annotations\Alias;
use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\BeanPostProcessor;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\TypeAlias;
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
use Exception;
use Laminas\Code\Reflection\ClassReflection;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\ProxyGenerator\Assertion\CanProxyAssertion;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;
use ReflectionClass;
use ReflectionMethod;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Reflection\MethodReflection;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Generator for configuration classes.
 */
class ConfigurationGenerator implements ProxyGeneratorInterface
{
    /**
     * {@inheritDoc}
     * @param ReflectionClass $originalClass
     * @param ClassGenerator $classGenerator
     * @throws InvalidProxiedClassException
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator)
    {
        CanProxyAssertion::assertClassCanBeProxied($originalClass, false);

        $forceLazyInitProperty = new ForceLazyInitProperty();
        $sessionBeansProperty = new SessionBeansProperty();
        $postProcessorsProperty = new BeanPostProcessorsProperty();
        $parameterValuesProperty = new ParameterValuesProperty();
        $beanFactoryConfigurationProperty = new BeanFactoryConfigurationProperty();
        $aliasesProperty = new AliasesProperty();
        $getParameterMethod = new GetParameter($parameterValuesProperty);
        $wrapBeanAsLazyMethod = new WrapBeanAsLazy($beanFactoryConfigurationProperty);

        $configurationAttribute = $originalClass->getAttributes(Configuration::class)[0] ?? null;

        if (null === $configurationAttribute) {
            throw new InvalidProxiedClassException(
                sprintf(
                    '"%s" seems not to be a valid configuration class. #[Configuration] attribute missing!',
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
            /** @var null|ReflectionUnionType|ReflectionNamedType $returnTypeRefl */
            $returnTypeRefl = $method->getReturnType();
            if ($returnTypeRefl instanceof ReflectionUnionType) {
                throw new InvalidProxiedClassException(
                    sprintf(
                        'Method "%s" on "%s" uses the unsupported union type.',
                        $method->name,
                        $originalClass->name
                    )
                );
            }

            $reflectionMethod = new MethodReflection(
                $method->class,
                $method->name
            );

            /** @var Bean|null $beanAttribute */
            $beanAttribute = ($reflectionMethod->getAttributes(Bean::class)[0] ?? null)?->newInstance();
            /** @var Parameter[] $parameterAttributes */
            $parameterAttributes = \array_map(
                fn($attributeRefl) => $attributeRefl->newInstance(),
                $reflectionMethod->getAttributes(Parameter::class)
            );
            if (null === $beanAttribute) {
                $postProcessorAttribute = $reflectionMethod->getAttributes(BeanPostProcessor::class)[0] ?? null;
                if (null !== $postProcessorAttribute) {
                    $postProcessorMethods[] = $method->name;

                    $proxyMethod = BeanPostProcessorMethod::generateMethod(
                        $reflectionMethod,
                        $parameterAttributes,
                        $getParameterMethod
                    );
                    $classGenerator->addMethodFromGenerator($proxyMethod);
                    continue;
                }

                if ($method->isProtected()) {
                    continue;
                }

                // every method needs either #[Bean] or #[PostPostprocessor] attribute
                throw new InvalidProxiedClassException(
                    sprintf(
                        'Method "%s" on "%s" is missing the #[Bean] (or #[BeanPostProcessor]) attribute '
                        . 'or its scope must be protected!',
                        $method->name,
                        $originalClass->name
                    )
                );
            }

            $beanAliases = [];

            /** @var TypeAlias|null $returnTypeAlias */
            $returnTypeAlias = ($reflectionMethod->getAttributes(TypeAlias::class)[0] ?? null)
                ?->newInstance();
            if (null !== $returnTypeAlias) {
                if (null === $returnTypeRefl || $returnTypeRefl->allowsNull() || $returnTypeRefl->isBuiltin()) {
                    throw new InvalidProxiedClassException(
                        sprintf(
                            'Cannot use #[ReturnTypeAlias] on method "%s" on "%s" because it\'s returning a '
                            . 'builtin type ("%s").',
                            $method->name,
                            $originalClass->name,
                            $returnTypeRefl === null || $returnTypeRefl->allowsNull()
                                ? 'null'
                                : $returnTypeRefl->getName()
                        )
                    );
                }

                $beanAliases[] = $returnTypeRefl->getName();
            }

            $beanAliases = [...$beanAliases, ...\array_map(
                /** @phpstan-ignore-next-line */
                fn($attr) => $attr->newInstance()->getName(),
                $reflectionMethod->getAttributes(Alias::class)
            )];

            foreach ($beanAliases as $beanAlias) {
                if ($method->getDeclaringClass()->name === $originalClass->name) {
                    $hasAlias = $localAliases[$beanAlias] ?? '';
                } else {
                    $hasAlias = $parentAliases[$beanAlias] ?? '';
                }

                if ($hasAlias !== '') {
                    throw new InvalidProxiedClassException(
                        sprintf(
                            'Alias "%s" of method "%s" on "%s" is already used by method "%s" of another Bean!',
                            $beanAlias,
                            $method->name,
                            $originalClass->name,
                            $hasAlias
                        )
                    );
                }

                if ($method->getDeclaringClass()->name === $originalClass->name) {
                    $localAliases[$beanAlias] = $method->name;
                } else {
                    $parentAliases[$beanAlias] = $method->name;
                }
            }

            $proxyMethod = BeanMethod::generateMethod(
                $reflectionMethod,
                $beanAttribute,
                $parameterAttributes,
                $returnTypeRefl,
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
            new GetAlias($aliasesProperty)
        );
        $classGenerator->addMethodFromGenerator(
            new HasAlias($aliasesProperty)
        );
    }
}
