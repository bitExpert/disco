<?php

/*
 * This file is part of the Disco package.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace bitExpert\Disco\Proxy\Configuration\MethodGenerator;

use bitExpert\Disco\Proxy\Configuration\ParameterValuesProperty;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\ParameterGenerator;
use ReflectionClass;

/**
 * `getParameter` method for the generated config proxy class.
 */
class GetParameter extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\ParameterValue}.
     *
     * @param ReflectionClass $originalClass
     * @param ParameterValuesProperty $parameterValueProperty
     */
    public function __construct(ReflectionClass $originalClass, ParameterValuesProperty $parameterValueProperty)
    {
        parent::__construct('getParameter');

        $propertyNameParameter = new ParameterGenerator('propertyName');
        $requiredParameter = new ParameterGenerator('required');
        $requiredParameter->setDefaultValue(true);
        $defaultValueParameter = new ParameterGenerator('defaultValue');
        $defaultValueParameter->setDefaultValue(null);

        $body = '$steps = explode(\'.\', $' . $propertyNameParameter->getName() . ');' . "\n";
        $body .= '$value = $this->' . $parameterValueProperty->getName() . ';' . "\n";
        $body .= '$currentPath = [];' . "\n";
        $body .= 'foreach ($steps as $step) {' . "\n";
        $body .= '    $currentPath[] = $step;' . "\n";
        $body .= '    if (isset($value[$step])) {' . "\n";
        $body .= '        $value = $value[$step];' . "\n";
        $body .= '    } else {' . "\n";
        $body .= '        $value = $' . $defaultValueParameter->getName() . ';' . "\n";
        $body .= '        break;' . "\n";
        $body .= '    }' . "\n";
        $body .= '}' . "\n\n";
        $body .= 'if ($' . $requiredParameter->getName() . ' && (null === $value)) {' . "\n";
        $body .= '    if (null === $' . $defaultValueParameter->getName() . ') {' . "\n";
        $body .= '        throw new \RuntimeException(\'Parameter "\' .$' . $propertyNameParameter->getName() .
            '. \'" is required but not defined and no default value provided!\');' . "\n";
        $body .= '    }' . "\n";
        $body .= '    throw new \RuntimeException(\'Parameter "\' .$' . $propertyNameParameter->getName() .
            '. \'" not defined!\');' . "\n";
        $body .= '}' . "\n\n";
        $body .= 'return $value;' . "\n";

        $this->setParameter($propertyNameParameter);
        $this->setParameter($requiredParameter);
        $this->setParameter($defaultValueParameter);
        $this->setVisibility(self::VISIBILITY_PROTECTED);
        $this->setBody($body);
    }
}
