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
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Parameters;
use bitExpert\Disco\InitializedBean;
use bitExpert\Disco\Proxy\Configuration\BeanPostProcessorsProperty;
use bitExpert\Disco\Proxy\Configuration\ForceLazyInitProperty;
use ProxyManager\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\DocBlockGenerator;
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
     * Generates a bean creation method.
     *
     * @param MethodReflection $originalMethod
     * @param Bean $methodAnnotation
     * @param Parameters $methodParameters
     * @param GetParameter $parameterValuesMethod
     * @param ForceLazyInitProperty $forceLazyInitProperty
     * @param BeanPostProcessorsProperty $postProcessorsProperty
     * @param $beanType
     * @return MethodGenerator|static
     * @internal param SessionBeansProperty $sessionBeansProperty
     */
    public static function generateMethod(
        MethodReflection $originalMethod,
        Bean $methodAnnotation,
        Parameters $methodParameters,
        GetParameter $parameterValuesMethod,
        ForceLazyInitProperty $forceLazyInitProperty,
        BeanPostProcessorsProperty $postProcessorsProperty,
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
        if ($methodAnnotation->isSingleton()) {
            $padding = '    ';
            $body .= 'static $instance = null;' . "\n\n";
            $body .= 'if ($instance === null) {' . "\n";
        }

        if ($methodAnnotation->isSession()) {
            $body .= $padding . 'if(isset($this->sessionBeans["' . $methodName . '"])) {' . "\n";
            if ($methodAnnotation->isSingleton()) {
                $body .= $padding . '    $instance = $this->sessionBeans["' . $methodName . '"];' . "\n";
            } else {
                $body .= $padding . '    return $this->sessionBeans["' . $methodName . '"];' . "\n";
            }
            $body .= $padding . '}' . "\n\n";
        }

        // Sessionbeans "force" their dependencies to be lazy proxies
        if ($methodAnnotation->isSession()) {
            $body .= $padding . '$this->' . $forceLazyInitProperty->getName() . ' = true;' . "\n";
        }

        if ($methodAnnotation->isLazy()) {
            $ipadding = $padding . '    ';
            $body .= $padding . '$factory     = new \\bitExpert\\Disco\\Proxy\\LazyBean\\LazyBeanFactory("' .
                $methodName . '");' . "\n";
            $body .= $padding . '$initializer = function (& $wrappedObject, ' .
                '\\ProxyManager\\Proxy\\LazyLoadingInterface $proxy, $method, array $parameters, & $initializer) {' .
                "\n";
            $body .= $ipadding . '$initializer   = null;' . "\n";
            $body .= $ipadding . 'try {' . "\n";
            $body .= $ipadding . '    $wrappedObject = parent::' . $methodName . '(' . $methodParamTpl . ');' . "\n";
            $body .= static::genInitCode($ipadding, 'wrappedObject', $methodName, $beanType, $postProcessorsProperty);
            $body .= $ipadding . '} catch (\Throwable $e) {' . "\n";
            $body .= $ipadding . '    $message = sprintf(' . "\n";
            $body .= $ipadding . '        \'Exception occured while instanciating "'.$methodName.'": %s\',' . "\n";
            $body .= $ipadding . '        $e->getMessage()' . "\n";
            $body .= $ipadding . '    );' . "\n";
            $body .= $ipadding . '    throw new \bitExpert\Disco\BeanException($message, 0, $e);' . "\n";
            $body .= $ipadding . '}' . "\n";
            $body .= $ipadding . 'return true;' . "\n";
            $body .= $padding . '};' . "\n\n";
            $body .= $padding . '$instance = $factory->createProxy("' . $beanType . '", $initializer);' . "\n\n";
        } else {
            $ipadding = $padding;
            if ($methodAnnotation->isSingleton()) {
                $ipadding .= $padding;
                $body .= $padding . 'if ($instance === null) {' . "\n";
            }

            $body .= $ipadding . '$instance = parent::' . $methodName . '(' . $methodParamTpl . ');' . "\n";
            $body .= static::genInitCode($ipadding, 'instance', $methodName, $beanType, $postProcessorsProperty);

            if ($methodAnnotation->isSingleton()) {
                $body .= $padding . '}' . "\n";
            }
        }

        if ($methodAnnotation->isSession()) {
            $body .= $padding . '$this->' . $forceLazyInitProperty->getName() . ' = false;' . "\n";
        }

        if ($methodAnnotation->isSingleton()) {
            $body .= '}' . "\n";
        }

        if ($methodAnnotation->isSession()) {
            $body .= '$this->sessionBeans["' . $methodName . '"] = $instance;' . "\n\n";
        }

        $body .= "\n" . 'if ($this->' . $forceLazyInitProperty->getName() . ') {' . "\n";
        $body .= '    if ($instance instanceof \\ProxyManager\\Proxy\\VirtualProxyInterface) {' . "\n";
        $body .= '        return $instance;' . "\n";
        $body .= '    }' . "\n\n";
        $body .= '    $factory     = new \\bitExpert\\Disco\\Proxy\\LazyBean\\LazyBeanFactory("' . $methodName .
            '");' . "\n";
        $body .= '    $initializer = function (& $wrappedObject, \\ProxyManager\\Proxy\\LazyLoadingInterface ' .
            ' $proxy, $method, array $parameters, & $initializer) use ($instance) {' . "\n";
        $body .= '        $initializer   = null;' . "\n";
        $body .= '        $wrappedObject = $instance;' . "\n";
        $body .= '        return true;' . "\n";
        $body .= '    };' . "\n\n";
        $body .= '    return $factory->createProxy("' . $beanType . '", $initializer);' . "\n";
        $body .= '}' . "\n\n";

        $body .= 'return $instance;' . "\n";

        $method->setBody($body);
        $method->setDocBlock('{@inheritDoc}');

        return $method;
    }

    /**
     * @override Enforces generation of \ProxyManager\Generator\MethodGenerator.
     *
     * {@inheritDoc}
     */
    public static function fromReflection(MethodReflection $reflectionMethod) : \ProxyManager\Generator\MethodGenerator
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
    private static function genInitCode(
        $padding,
        $beanVar,
        $beanName,
        $beanType,
        BeanPostProcessorsProperty $postProcessorsProperty
    ) {
        $body = $padding . 'if ($' . $beanVar . ' instanceof \\' . InitializedBean::class . ') {' . "\n";
        $body .= $padding . '    $' . $beanVar . '->postInitialization();' . "\n";
        $body .= $padding . '}' . "\n\n";

        // $body .= $padding . 'if (!($' . $beanVar .' instanceof ' . $beanType . ')) {' . "\n";
        $body .= $padding . 'if (false) {' . "\n";
        $body .= $padding . '    throw new \\bitExpert\\Disco\\BeanException(sprintf(' . "\n";
        $body .= $padding . '        \'Bean "%s" has declared "%s" as return type but returned "%s"\',' . "\n";
        $body .= $padding . '        \'' . $beanName . '\',' . "\n";
        $body .= $padding . '        \'' . $beanType . '\',' . "\n";
        $body .= $padding . '        $' . $beanVar . ' ? get_class($' . $beanVar . ') : \'null\'' . "\n";
        $body .= $padding . '    ));' . "\n";
        $body .= $padding . '}' . "\n\n";

        $body .= $padding . 'foreach ($this->' . $postProcessorsProperty->getName() . ' as $postProcessor) {' . "\n";
        $body .= $padding . '    $postProcessor->postProcess($' . $beanVar . ', "' . $beanName . '");' . "\n";
        $body .= $padding . '}' . "\n";

        return $body;
    }
}
