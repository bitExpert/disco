# Upgrade to 0.5.0

## BC BREAK: Rasing minimum PHP version to 7.0

With the 0.5.0 release of Disco support for PHP 5.x is dropped. Disco
will only work in PHP 7.x environments since Disco relies on some of the
PHP 7.x features, e.g. return type definitions.

## BC BREAK: Return type definitions

Disco 0.5.0 dropped support for the @return annotations and strictly
relies on the return type definitions now. 

While this was valid configuration code before the 0.5.0 release:
```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean
     * @return SampleService 
     */
    public function mySampleService()
    {
        return new SampleService();
    }
}
```

This is how you have to define bean instances for the 0.5.0 release:
```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

Relying on return type definitions also fixes a few issues with traits
defined in different namespaces. PHP now can properly resolve these types
and we do no longer need to "guess" which type might be the correct one.

## BC BREAK: BeanFactoryConfiguration

The `\bitExpert\Disco\BeanFactoryConfiguration::__construct()` method 
changed. The 4th parameter is no longer a bool but an instance of 
`\ProxyManager\Autoloader\AutoloaderInterface`

To make your life easier make use of the factory method 
`\bitExpert\Disco\BeanFactoryConfiguration::getDefault()` to quickly
set up a configuration instance.

## BC BREAK: EvalStrategy (ProxyManager)

When not using the BeanFactoryConfiguration to configure the BeanFactory
internals, ProxyManager in version 2.x will make use of the 
`\ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy` instead of 
`\ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy`. Depending 
on the complexitity of your configuration class and depending on how many
traits you might use this can lead to a poor performance.

Follow the advice in the README.md file on performance tuning.
