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

namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\InitializedBean;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanFactoryConfigurationProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ForceLazyInitProperty;
use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\SessionBeansProperty;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * The bean method generator will generate a method for each bean definition in the generated
 * configuration class. The method contains the logic to deal with the bean creation as well
 * as taking the configuration options like lazy creation or session-awareness of the bean into
 * account. These configuration options are defined via annotations.
 */
class BeanMethod extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\BeanMethod}.
     *
     * @param MethodReflection $originalMethod
     * @param Bean $methodAnnotation
     * @param Parameters $methodParameters
     * @param GetParameter $parameterValuesMethod
     * @param ForceLazyInitProperty $forceLazyInitProperty
     * @param SessionBeansProperty $sessionBeansProperty
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @param BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty
     * @param $beanType
     * @return BeanMethod|MethodGenerator
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        Bean $methodAnnotation,
        Parameters $methodParameters,
        GetParameter $parameterValuesMethod,
        ForceLazyInitProperty $forceLazyInitProperty,
        SessionBeansProperty $sessionBeansProperty,
        BeanPostProcessorsProperty $postProcessorsProperty,
        BeanFactoryConfigurationProperty $beanFactoryConfigurationProperty,
        $beanType
    ) : self {
        /* @var $method self */
        $method = static::fromReflection($originalMethod);
        $methodName = $originalMethod->getName();
        $padding = '';

        $methodParamTpl = [];
        foreach ($methodParameters->value as $methodParameter) {
            /** @var $methodParameter Parameter */
            $name = $methodParameter->getName();
            $defaultValue = $methodParameter->getDefaultValue();
            $required = $methodParameter->isRequired() ? 'true' : 'false';
            if (is_string($defaultValue)) {
                $defaultValue = '"' . $defaultValue . '"';
            } elseif (is_null($defaultValue)) {
                $defaultValue = 'null';
            } elseif (is_bool($defaultValue)) {
                $defaultValue = ($defaultValue) ? 'true' : 'false';
            }

            if (!empty($defaultValue)) {
                $methodParamTpl[] = '$this->' . $parameterValuesMethod->getName() . '("' . $name . '", ' . $required .
                    ', ' . $defaultValue . ')';
            } else {
                $methodParamTpl[] = '$this->' . $parameterValuesMethod->getName() . '("' . $name . '", ' . $required .
                    ')';
            }
        }
        $methodParamTpl = implode(', ', $methodParamTpl);

        $body = '';

        if (in_array($beanType, ['array', 'callable', 'bool', 'float', 'int', 'string'])) {
            // return type is a primitive, simply call parent method and return immediately.
            $body .= $padding . 'return parent::' . $methodName . '(' . $methodParamTpl . ');' . PHP_EOL;
        } elseif (class_exists($beanType) || interface_exists($beanType)) {
            // return type is either class or interface
            if ($methodAnnotation->isSingleton()) {
                $padding = '    ';
                $body .= 'static $instance = null;' . PHP_EOL . PHP_EOL;
                $body .= 'if ($instance === null) {' . PHP_EOL;
            }

            if ($methodAnnotation->isSession()) {
                $body .= $padding . 'if($this->'.$sessionBeansProperty->getName().'->has("' . $methodName . '")) {' .
                    PHP_EOL;
                if ($methodAnnotation->isSingleton()) {
                    $body .= $padding . '    $instance = $this->'.$sessionBeansProperty->getName().
                        '->get("' . $methodName . '");' . PHP_EOL;
                } else {
                    $body .= $padding . '    return $this->'.$sessionBeansProperty->getName().
                        '->get("' . $methodName . '");' . PHP_EOL;
                }
                $body .= $padding . '}' . PHP_EOL . PHP_EOL;

                // Sessionbeans "force" their dependencies to be lazy proxies
                $body .= $padding . '$this->' . $forceLazyInitProperty->getName() . ' = true;' . PHP_EOL;
            }

            if ($methodAnnotation->isLazy()) {
                $ipadding = $padding . '    ';
                $body .= $padding . '$factory     = new \bitExpert\Disco\Proxy\LazyBean\LazyBeanFactory("' .
                    $methodName . '", $this->'.$beanFactoryConfigurationProperty->getName().
                    '->getProxyManagerConfiguration());' . PHP_EOL;
                $body .= $padding . '$initializer = function (& $wrappedObject, '.
                    '\ProxyManager\Proxy\LazyLoadingInterface $proxy, $method, array $parameters, & $initializer) {' .
                    PHP_EOL;
                $body .= $ipadding . '$initializer   = null;' . PHP_EOL;
                $body .= $ipadding . 'try {' . PHP_EOL;
                $body .= $ipadding . '    $wrappedObject = parent::' . $methodName . '(' . $methodParamTpl . ');' .
                    PHP_EOL;
                $body .= static::generateBeanInitCode(
                    $ipadding,
                    'wrappedObject',
                    $methodName,
                    $beanType,
                    $postProcessorsProperty
                );
                $body .= $ipadding . '} catch (\Throwable $e) {' . PHP_EOL;
                $body .= $ipadding . '    $message = sprintf(' . PHP_EOL;
                $body .= $ipadding . '        \'Exception occured while instanciating "' . $methodName . '": %s\',' .
                    PHP_EOL;
                $body .= $ipadding . '        $e->getMessage()' . PHP_EOL;
                $body .= $ipadding . '    );' . PHP_EOL;
                $body .= $ipadding . '    throw new \bitExpert\Disco\BeanException($message, $e->getCode(), $e);' .
                    PHP_EOL;
                $body .= $ipadding . '}' . PHP_EOL;
                $body .= $ipadding . 'return true;' . PHP_EOL;
                $body .= $padding . '};' . PHP_EOL . PHP_EOL;
                $body .= $padding . '$instance = $factory->createProxy("' . $beanType . '", $initializer);' .
                    PHP_EOL . PHP_EOL;
            } else {
                $ipadding = $padding;
                if ($methodAnnotation->isSingleton()) {
                    $ipadding .= $padding;
                    $body .= $padding . 'if ($instance === null) {' . PHP_EOL;
                }

                $body .= $ipadding . '$instance = parent::' . $methodName . '(' . $methodParamTpl . ');' . PHP_EOL;
                $body .= static::generateBeanInitCode(
                    $ipadding,
                    'instance',
                    $methodName,
                    $beanType,
                    $postProcessorsProperty
                );

                if ($methodAnnotation->isSingleton()) {
                    $body .= $padding . '}' . PHP_EOL;
                }
            }

            if ($methodAnnotation->isSession()) {
                $body .= $padding . '$this->' . $forceLazyInitProperty->getName() . ' = false;' . PHP_EOL;
            }

            if ($methodAnnotation->isSingleton()) {
                $body .= '}' . PHP_EOL;
            }

            if ($methodAnnotation->isSession()) {
                $body .= '$this->'.$sessionBeansProperty->getName().'->add("' . $methodName . '", $instance);' .
                    PHP_EOL . PHP_EOL;
            }

            $body .= PHP_EOL . 'if ($this->' . $forceLazyInitProperty->getName() . ') {' . PHP_EOL;
            $body .= '    if ($instance instanceof \ProxyManager\Proxy\VirtualProxyInterface) {' . PHP_EOL;
            $body .= '        return $instance;' . PHP_EOL;
            $body .= '    }' . PHP_EOL . PHP_EOL;
            $body .= '    $factory     = new \bitExpert\Disco\Proxy\LazyBean\LazyBeanFactory("' . $methodName .
                '", $this->'.$beanFactoryConfigurationProperty->getName().'->getProxyManagerConfiguration());' .
                PHP_EOL;
            $body .= '    $initializer = function (& $wrappedObject, \ProxyManager\Proxy\LazyLoadingInterface ' .
                ' $proxy, $method, array $parameters, & $initializer) use ($instance) {' . PHP_EOL;
            $body .= '        $initializer   = null;' . PHP_EOL;
            $body .= '        $wrappedObject = $instance;' . PHP_EOL;
            $body .= '        return true;' . PHP_EOL;
            $body .= '    };' . PHP_EOL . PHP_EOL;
            $body .= '    return $factory->createProxy("' . $beanType . '", $initializer);' . PHP_EOL;
            $body .= '}' . PHP_EOL . PHP_EOL;

            $body .= 'return $instance;' . PHP_EOL;
        } else {
            // return type is unknown, throw an exception
            $body .= $padding . '$message = sprintf(' . PHP_EOL;
            $body .= $padding . '    \'Either return type declaration missing or unkown for bean with id "'
                . $methodName . '": %s\',' . PHP_EOL;
            $body .= $padding . '    $e->getMessage()' . PHP_EOL;
            $body .= $padding . ');' . PHP_EOL;
            $body .= $padding . 'throw new \bitExpert\Disco\BeanException($message, 0, $e);' . PHP_EOL;
        }

        $method->setBody($body);
        $method->setDocBlock('{@inheritDoc}');

        return $method;
    }

    /**
     * @override Enforces generation of \ProxyManager\Generator\MethodGenerator.
     *
     * {@inheritDoc}
     */
    public static function fromReflection(MethodReflection $reflectionMethod) : MethodGenerator
    {
        /* @var $method self */
        $method = new static();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);

        if ($reflectionMethod->getDocComment() != '') {
            $method->setDocBlock(DocBlockGenerator::fromReflection($reflectionMethod->getDocBlock()));
        }

        $method->setFinal($reflectionMethod->isFinal());
        $method->setVisibility(self::extractVisibility($reflectionMethod));

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameter = ParameterGenerator::fromReflection($reflectionParameter);
            /*
             * Needed for PHP 7: When overwriting methods PHP 7 enforces the same method parameters
             * as defined in the base class. Since the {@link \bitExpert\Disco\AnnotationBeanFactory}
             * calls the methods without any parameters we simply define a default value for each of
             * the method parameters.
             */
            $parameter->setDefaultValue(null);
            $method->setParameter($parameter);
        }

        $method->setStatic($reflectionMethod->isStatic());
        $method->setName($reflectionMethod->getName());
        $method->setBody($reflectionMethod->getBody());
        $method->setReturnsReference($reflectionMethod->returnsReference());
        $method->setReturnType($reflectionMethod->getReturnType());

        return $method;
    }

    /**
     * Retrieves the visibility for the given method reflection
     *
     * @param MethodReflection $reflectionMethod
     * @return string
     */
    private static function extractVisibility(MethodReflection $reflectionMethod)
    {
        if ($reflectionMethod->isPrivate()) {
            return static::VISIBILITY_PRIVATE;
        }

        if ($reflectionMethod->isProtected()) {
            return static::VISIBILITY_PROTECTED;
        }

        return static::VISIBILITY_PUBLIC;
    }

    /**
     * Helper method to create the general initialization logic for a new bean instance.
     *
     * @param string $padding
     * @param string $beanVar
     * @param string $beanName
     * @param string $beanType
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @return string
     */
    protected static function generateBeanInitCode(
        string $padding,
        string $beanVar,
        string $beanName,
        string $beanType,
        BeanPostProcessorsProperty $postProcessorsProperty
    ) : string {
        $body = $padding . 'if ($' . $beanVar . ' instanceof \\' . InitializedBean::class . ') {' . PHP_EOL;
        $body .= $padding . '    $' . $beanVar . '->postInitialization();' . PHP_EOL;
        $body .= $padding . '}' . PHP_EOL . PHP_EOL;

        $body .= $padding . 'if (!($' . $beanVar .' instanceof \\' . $beanType . ')) {' . PHP_EOL;
        $body .= $padding . '    throw new \bitExpert\Disco\BeanException(sprintf(' . PHP_EOL;
        $body .= $padding . '        \'Bean "%s" has declared "%s" as return type but returned "%s"\',' . PHP_EOL;
        $body .= $padding . '        \'' . $beanName . '\',' . PHP_EOL;
        $body .= $padding . '        \'' . $beanType . '\',' . PHP_EOL;
        $body .= $padding . '        $' . $beanVar . ' ? get_class($' . $beanVar . ') : \'null\'' . PHP_EOL;
        $body .= $padding . '    ));' . PHP_EOL;
        $body .= $padding . '}' . PHP_EOL . PHP_EOL;

        $body .= $padding . 'foreach ($this->' . $postProcessorsProperty->getName() . ' as $postProcessor) {' . PHP_EOL;
        $body .= $padding . '    $postProcessor->postProcess($' . $beanVar . ', "' . $beanName . '");' . PHP_EOL;
        $body .= $padding . '}' . PHP_EOL;

        return $body;
    }
}
