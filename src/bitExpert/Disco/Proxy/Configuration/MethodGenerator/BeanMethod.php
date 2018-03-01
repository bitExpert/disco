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

namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\BeanException;
use bitExpert\Disco\InitializedBean;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ForceLazyInitProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
use bitExpert\Disco\Proxy\LazyBean\LazyBeanFactory;
use ProxyManager\Exception\InvalidProxiedClassException;
use ProxyManager\Proxy\LazyLoadingInterface;
use ReflectionType;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * The bean method generator will generate a method for each bean definition in the generated
 * configuration class. The method contains the logic to deal with the bean creation as well
 * as taking the configuration options like lazy creation or session-awareness of the bean into
 * account. These configuration options are defined via annotations.
 */
class BeanMethod extends ParameterAwareMethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanMethod}.
     *
     * @param MethodReflection $originalMethod
     * @param Bean $beanMetadata
     * @param ReflectionType|null $beanType
     * @param ForceLazyInitProperty $forceLazyInitProperty
     * @param SessionBeansProperty $sessionBeansProperty
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @param BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
     * @param GetParameter $parameterValuesMethod
     * @param WrapBeanAsLazy $wrapBeanAsLazy
     * @return MethodGenerator
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \ProxyManager\Exception\InvalidProxiedClassException
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        Bean $beanMetadata,
        ?ReflectionType $beanType,
        ForceLazyInitProperty $forceLazyInitProperty,
        SessionBeansProperty $sessionBeansProperty,
        BeanPostProcessorsProperty $postProcessorsProperty,
        BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty,
        GetParameter $parameterValuesMethod,
        WrapBeanAsLazy $wrapBeanAsLazy
    ): MethodGenerator {
        if (null === $beanType) {
            throw new InvalidProxiedClassException(
                sprintf(
                    'Method "%s" on "%s" is missing the return type hint!',
                    $originalMethod->name,
                    $originalMethod->class
                )
            );
        }
        $beanType = (string) $beanType;

        $method = static::fromReflection($originalMethod);
        $methodParams = static::convertMethodParamsToString($beanMetadata->getParameters(), $parameterValuesMethod);
        $beanId = $originalMethod->name;
        $body = '';

        if (in_array($beanType, ['array', 'callable', 'bool', 'float', 'int', 'string'], true)) {
            // return type is a primitive, simply call parent method and return immediately
            $body .= 'return parent::' . $beanId . '(' . $methodParams . ');' . PHP_EOL;
        } elseif (class_exists($beanType) || interface_exists($beanType)) {
            if ($beanMetadata->isLazy()) {
                $body = static::generateLazyBeanCode(
                    '',
                    $beanId,
                    $beanType,
                    $beanMetadata,
                    $methodParams,
                    $forceLazyInitProperty,
                    $sessionBeansProperty,
                    $postProcessorsProperty,
                    $beanFactoryConfigurationProperty
                );
            } else {
                $body = static::generateNonLazyBeanCode(
                    '',
                    $beanId,
                    $beanType,
                    $beanMetadata,
                    $methodParams,
                    $forceLazyInitProperty,
                    $sessionBeansProperty,
                    $postProcessorsProperty,
                    $wrapBeanAsLazy
                );
            }
        } else {
            // return type is unknown, throw an exception
            throw new InvalidProxiedClassException(
                sprintf(
                    'Return type of method "%s" on "%s" cannot be found! Did you use the full qualified name?',
                    $originalMethod->getName(),
                    $originalMethod->getDeclaringClass()->getName()
                )
            );
        }

        $method->setBody($body);
        $method->setDocBlock('{@inheritDoc}');
        return $method;
    }

    /**
     * @override Enforces generation of \ProxyManager\Generator\MethodGenerator.
     *
     * {@inheritDoc}
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     */
    public static function fromReflection(MethodReflection $reflectionMethod): MethodGenerator
    {
        $method = parent::fromReflection($reflectionMethod);

        /*
         * When overwriting methods PHP 7 enforces the same method parameters to be defined as in the base class. Since
         * the {@link \bitExpert\Disco\AnnotationBeanFactory} calls the generated methods without any parameters we
         * simply set a default value of null for each of the method parameters.
         */
        $method->setParameters([]);
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameter = ParameterGenerator::fromReflection($reflectionParameter);
            $parameter->setDefaultValue(null);
            $method->setParameter($parameter);
        }

        return $method;
    }

    /**
     * Helper method to generate the method body for managing lazy bean instances.
     *
     * @param string $padding
     * @param string $beanId
     * @param string $beanType
     * @param Bean $beanMetadata
     * @param string $methodParams
     * @param ForceLazyInitProperty $forceLazyInitProperty
     * @param SessionBeansProperty $sessionBeansProperty
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @param BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
     * @return string
     */
    protected static function generateLazyBeanCode(
        string $padding,
        string $beanId,
        string $beanType,
        Bean $beanMetadata,
        string $methodParams,
        ForceLazyInitProperty $forceLazyInitProperty,
        SessionBeansProperty $sessionBeansProperty,
        BeanPostProcessorsProperty $postProcessorsProperty,
        BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
    ): string {
        $content = '';

        if ($beanMetadata->isSession()) {
            $content .= $padding . 'if($this->' . $sessionBeansProperty->getName() . '->has("' . $beanId . '")) {' .
                PHP_EOL;
            if ($beanMetadata->isSingleton()) {
                $content .= $padding . '    $sessionInstance = clone $this->' . $sessionBeansProperty->getName() .
                    '->get("' . $beanId . '");' . PHP_EOL;
            } else {
                $content .= $padding . '    $sessionInstance = $this->' . $sessionBeansProperty->getName() . '->get("' .
                    $beanId . '");' . PHP_EOL;
            }
            $content .= $padding . '    return $sessionInstance;' . PHP_EOL;
            $content .= $padding . '}' . PHP_EOL;
        }

        if ($beanMetadata->isSingleton()) {
            $content .= $padding . 'static $instance = null;' . PHP_EOL;
            $content .= $padding . 'if ($instance !== null) {' . PHP_EOL;
            $content .= $padding . '    return $instance;' . PHP_EOL;
            $content .= $padding . '}' . PHP_EOL;
        }

        $content .= $padding . '$factory = new \\' . LazyBeanFactory::class . '("' . $beanId . '", $this->' .
            $beanFactoryConfigurationProperty->getName() . '->getProxyManagerConfiguration());' . PHP_EOL;
        $content .= $padding . '$initializer = function (&$instance, \\' . LazyLoadingInterface::class .
            ' $proxy, $method, array $parameters, &$initializer) {' . PHP_EOL;
        $content .= $padding . '    try {' . PHP_EOL;
        $content .= $padding . '        $initializer = null;' . PHP_EOL;

        if ($beanMetadata->isSession()) {
            $content .= $padding . '        $backupForceLazyInit = $this->' . $forceLazyInitProperty->getName() . ';'
                . PHP_EOL;
            $content .= $padding . '        $this->' . $forceLazyInitProperty->getName() . ' = true;' . PHP_EOL;
        }

        $content .= $padding . self::generateBeanCreationCode(
            $padding . '        ',
            $beanId,
            $methodParams,
            $postProcessorsProperty
        );

        if ($beanMetadata->isSession()) {
            $content .= $padding . '        $this->' . $forceLazyInitProperty->getName() . ' = $backupForceLazyInit;' .
                PHP_EOL;
        }

        $content .= $padding . '    } catch (\Throwable $e) {' . PHP_EOL;
        $content .= $padding . '        $message = sprintf(' . PHP_EOL;
        $content .= $padding . '            \'Either return type declaration missing or unknown for bean with id "' .
            $beanId . '": %s\',' . PHP_EOL;
        $content .= $padding . '            $e->getMessage()' . PHP_EOL;
        $content .= $padding . '        );' . PHP_EOL;
        $content .= $padding . '        throw new \\' . BeanException::class . '($message, 0, $e);' . PHP_EOL;
        $content .= $padding . '    }' . PHP_EOL;
        $content .= $padding . '    return true;' . PHP_EOL;
        $content .= $padding . '};' . PHP_EOL;
        $content .= $padding . PHP_EOL;
        $content .= $padding . '$initializer->bindTo($this);' . PHP_EOL;
        $content .= $padding . '$instance = $factory->createProxy("' . $beanType . '", $initializer);' . PHP_EOL;

        if ($beanMetadata->isSession()) {
            $content .= $padding . '$this->' . $sessionBeansProperty->getName() . '->add("' . $beanId . '", $instance);'
                . PHP_EOL;
        }

        $content .= $padding . 'return $instance;' . PHP_EOL;
        return $content;
    }

    /**
     * Helper method to generate the code to initialize a bean.
     *
     * @param string $padding
     * @param string $beanId
     * @param string $methodParams
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @return string
     */
    protected static function generateBeanCreationCode(
        string $padding,
        string $beanId,
        string $methodParams,
        BeanPostProcessorsProperty $postProcessorsProperty
    ): string {
        $content = $padding . '$instance = parent::' . $beanId . '(' . $methodParams . ');' . PHP_EOL;
        $content .= $padding . 'if ($instance instanceof \\' . InitializedBean::class . ') {
        ' . PHP_EOL;
        $content .= $padding . '    $instance->postInitialization();' . PHP_EOL;
        $content .= $padding . '}' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= $padding . 'foreach ($this->' . $postProcessorsProperty->getName() . ' as $postProcessor) {
        ' .
            PHP_EOL;
        $content .= $padding . '    $postProcessor->postProcess($instance, "' . $beanId . '");' . PHP_EOL;
        $content .= $padding . '}' . PHP_EOL;
        return $content;
    }

    /**
     * Helper method to generate the method body for managing non-lazy bean instances.
     *
     * @param string $padding
     * @param string $beanId
     * @param string $beanType
     * @param Bean $beanMetadata
     * @param string $methodParams
     * @param ForceLazyInitProperty $forceLazyInitProperty
     * @param SessionBeansProperty $sessionBeansProperty
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @param WrapBeanAsLazy $wrapBeanAsLazy
     * @return string
     */
    protected static function generateNonLazyBeanCode(
        string $padding,
        string $beanId,
        string $beanType,
        Bean $beanMetadata,
        string $methodParams,
        ForceLazyInitProperty $forceLazyInitProperty,
        SessionBeansProperty $sessionBeansProperty,
        BeanPostProcessorsProperty $postProcessorsProperty,
        WrapBeanAsLazy $wrapBeanAsLazy
    ): string {
        $content = $padding . '$backupForceLazyInit = $this->' . $forceLazyInitProperty->getName() . ';' . PHP_EOL;

        if ($beanMetadata->isSession()) {
            $content .= $padding . 'if($this->' . $sessionBeansProperty->getName() . '->has("' . $beanId . '")) {'
                . PHP_EOL;
            if ($beanMetadata->isSingleton()) {
                $content .= $padding . '    $sessionInstance = clone $this->' . $sessionBeansProperty->getName()
                    . '->get("' . $beanId . '");' . PHP_EOL;
            } else {
                $content .= $padding . '    $sessionInstance = $this->' . $sessionBeansProperty->getName() . '->get("' .
                    $beanId . '");' . PHP_EOL;
            }
            $content .= $padding . '    return ($backupForceLazyInit) ? $this->' . $wrapBeanAsLazy->getName() . '("' .
                $beanId . '", "' . $beanType . '", $sessionInstance) : $sessionInstance;' . PHP_EOL;
            $content .= $padding . '}' . PHP_EOL;
        }

        if ($beanMetadata->isSingleton()) {
            $content .= $padding . 'static $instance = null;' . PHP_EOL;
            $content .= $padding . 'if ($instance !== null) {' . PHP_EOL;
            $content .= $padding . '    return ($backupForceLazyInit) ? $this->' . $wrapBeanAsLazy->getName() . '("' .
                $beanId . '", "' . $beanType . '", $instance) : $instance;' . PHP_EOL;
            $content .= $padding . '}' . PHP_EOL;
        }

        if ($beanMetadata->isSession()) {
            $content .= $padding . '$this->' . $forceLazyInitProperty->getName() . ' = true;' . PHP_EOL;
        }

        $content .= self::generateBeanCreationCode($padding, $beanId, $methodParams, $postProcessorsProperty);

        if ($beanMetadata->isSession()) {
            $content .= $padding . '$this->' . $forceLazyInitProperty->getName() . ' = $backupForceLazyInit;' . PHP_EOL;
            $content .= $padding . '$this->' . $sessionBeansProperty->getName() . '->add("' . $beanId . '", $instance);'
                . PHP_EOL;
        }

        $content .= $padding . 'return ($backupForceLazyInit) ? $this->' . $wrapBeanAsLazy->getName() . '("' .
            $beanId . '", "' . $beanType . '", $instance) : $instance;' . PHP_EOL;

        return $content;
    }
}
