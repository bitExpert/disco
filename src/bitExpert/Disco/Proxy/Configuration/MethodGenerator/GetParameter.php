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

use bitExpert\Disco\Proxy\Configuration\PropertyGenerator\ParameterValuesProperty;
use ProxyManager\Generator\MethodGenerator;
use ProxyManager\Generator\Util\UniqueIdentifierGenerator;
use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;

/**
 * `getParameter` method for the generated config proxy class.
 */
class GetParameter extends MethodGenerator
{
    /**
     * Creates a new {@link \bitExpert\Disco\Proxy\Configuration\MethodGenerator\GetParameter}.
     *
     * @param ParameterValuesProperty $parameterValueProperty
     * @throws InvalidArgumentException
     */
    public function __construct(ParameterValuesProperty $parameterValueProperty)
    {
        parent::__construct(UniqueIdentifierGenerator::getIdentifier('getParameter'));

        $propertyNameParameter = new ParameterGenerator('propertyName');
        $requiredParameter = new ParameterGenerator('required');
        $requiredParameter->setDefaultValue(true);
        $defaultValueParameter = new ParameterGenerator('defaultValue');
        $defaultValueParameter->setDefaultValue(null);

        $body = '$steps = explode(\'.\', $' . $propertyNameParameter->getName() . ');' . PHP_EOL;
        $body .= '$value = $this->' . $parameterValueProperty->getName() . ';' . PHP_EOL;
        $body .= '$currentPath = [];' . PHP_EOL;
        $body .= 'foreach ($steps as $step) {' . PHP_EOL;
        $body .= '    $currentPath[] = $step;' . PHP_EOL;
        $body .= '    if (isset($value[$step])) {' . PHP_EOL;
        $body .= '        $value = $value[$step];' . PHP_EOL;
        $body .= '    } else {' . PHP_EOL;
        $body .= '        $value = $' . $defaultValueParameter->getName() . ';' . PHP_EOL;
        $body .= '        break;' . PHP_EOL;
        $body .= '    }' . PHP_EOL;
        $body .= '}' . PHP_EOL . PHP_EOL;
        $body .= 'if ($' . $requiredParameter->getName() . ' && (null === $value)) {' . PHP_EOL;
        $body .= '    if (null === $' . $defaultValueParameter->getName() . ') {' . PHP_EOL;
        $body .= '        throw new \RuntimeException(\'Parameter "\' .$' . $propertyNameParameter->getName() .
            '. \'" is required but not defined and no default value provided!\');' . PHP_EOL;
        $body .= '    }' . PHP_EOL;
        $body .= '    throw new \RuntimeException(\'Parameter "\' .$' . $propertyNameParameter->getName() .
            '. \'" not defined!\');' . PHP_EOL;
        $body .= '}' . PHP_EOL . PHP_EOL;
        $body .= 'return $value;' . PHP_EOL;

        $this->setParameter($propertyNameParameter);
        $this->setParameter($requiredParameter);
        $this->setParameter($defaultValueParameter);
        $this->setVisibility(self::VISIBILITY_PROTECTED);
        $this->setBody($body);
    }
}
