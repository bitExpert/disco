# Bean Parameters

Bean instances can be parameterized by a given configuration. To access the configuration add a `#[Parameter]` attribute to your Bean method.

The `#[Parameter(name: 'paramName', key: 'config.key')]` attribute requires at least the `param` (which must match a param name of the Bean method) and the `key` which will be used to look for in the configuration array.

In the following example the value of configuration key `configKey` gets passed to the bean configuation for the argument named `$test`.
Configuration parameters are passed to the bean configuration method using [named arguments](https://www.php.net/manual/en/functions.arguments.php#functions.named-arguments):

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

#[Configuration]
class MyConfiguration
{
    #[Bean]
    #[Parameter(name: 'test', key: 'configKey')]
    public function mySampleService(string $test = '') : SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
```

The configuration array gets passed to the `\bitExpert\Disco\AnnotationBeanFactory` instance as second constructor parameter:

```php
<?php

$parameters = ['configKey' => 'This is a test.'];

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(
  MyConfiguration::class,
  $parameters
);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);

$sampleService = $beanFactory->get('mySampleService');

echo $sampleService->test; // Output: This is a test.
```

## Default Parameter Values

Whenever a requested configuration key is not found — the key does not exist or the value is null or empty - Disco will throw an exception. This can be avoided by defining a default value that gets passed to the bean configuration method instead:

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

#[Configuration]
class MyConfiguration
{
    #[Bean]
    #[Parameter(name: 'test', key: 'configKey', default: 'Some default value')]
    public function mySampleService(string $test = '') : SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}

$parameters = []; // empty

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(
  MyConfiguration::class,
  $parameters
);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);

$sampleService = $beanFactory->get('mySampleService');

echo $sampleService->test; // Output: Some default value.
```

## Default Bean Configuration Method Parameter Values

There is no need to define a default parameter for the method parameters, but if you do not provide a default value your IDE might report a problem when calling `$this->mySampleService()` without any method parameters. 

That's why it is recommended to define a default parameter for each of the bean configuration method parameters.

## Nested Configuration Array

This is possible to refer to nested configurations keys as defined like this:

```php
<?php

$parameters = [
    'nested' => [
        'key' => 'This is a test.'
    ]
];

```

Use the '.' notation to access the nested elements:

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Parameter;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

#[Configuration]
class MyConfiguration
{
    #[Bean]
    #[Parameter(name: 'test', key: 'nested.key')]
    public function mySampleService(string $test = '') : SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
```

In this case the same rules apply for the default value handling: If one  of the nested elements is not found, the default value — if defined — gets passed to bean creation method instead. If no default value was provided, an exception gets thrown.
