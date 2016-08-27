# bitexpert/disco

This package provides a [container-interop](https://github.com/container-interop/container-interop) compatible,
annotation-based dependency injection container. Have a look at the [adroit-disco-demo](https://github.com/bitExpert/adroit-disco-demo)
project to find out how to use Disco.

[![Build Status](https://travis-ci.org/bitExpert/disco.svg?branch=master)](https://travis-ci.org/bitExpert/disco)
[![Dependency Status](https://www.versioneye.com/user/projects/563e5b144d415e0018000121/badge.svg?style=flat)](https://www.versioneye.com/user/projects/563e5b144d415e0018000121)

## Installation


The preferred way of installing `bitexpert/disco` is through Composer. Simply add `bitexpert/disco` as a dependency:

```
composer.phar require bitexpert/disco
```

## Usage

First, you need to define a configuration class. The class needs to be marked with `@Configuration` annotation:

```php
<?php

use bitExpert\Disco\Annotations\Configuration;

/**
 * @Configuration
 */
class MyConfiguration
{
}
```

Add a public or protected method for each instance you want Disco to manage for you. Instances managed by a public
method are accessible via the `has()` and `get()` methods of the `\bitExpert\Disco\AnnotationBeanFactory` class. Any
instance managed by a protected method is seen as a local dependency, and is just accessible "inside" the container's
configuration class.  This is useful for dependencies like database connectors, which you most likely do not want to
expose to the outside world.

Each method needs to be marked with `@Bean` annotation as well as define the [return type declaration](http://php.net/manual/en/functions.returning-values.php#functions.returning-values.type-declaration)
(which is mainly needed for the lazy proxy magic).

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

The `@Bean` annotation comes with a few configuration settings for influencing the lifetime of the instance. You can
define the `scope` (which is either the string `request` or `session`), define a boolean value for `lazy`, or define
a boolean value for `singleton`. The following configuration creates a lazy-loaded, singleton, request-aware instance:

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
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

To inject a dependency, simply call the respective method:

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;
use bitExpert\Disco\Helper\MasterService;

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
    
    /**
     * @Bean
     */
    public function myMasterService() : MasterService
    {
        return new MasterService($this->mySampleService());
    }
}
```

To inject parameters use the `@Parameter` annotation. Parameters need to be passed as a constructor argument to the
`\bitExpert\Disco\AnnotationBeanFactory` instance:

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
     * @Parameters({
     *  @Parameter({"name" = "test"})
     * })
     */
    public function mySampleService($test = '') : SampleService
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
```

To instantiate the `\bitExpert\Disco\AnnotationBeanFactory` instance, proceed as follows: 

```php
<?php

$parameters = ['test' => 'This is a test.'];

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class, $parameters);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);
```

Since Disco implements the [container-interop](https://github.com/container-interop/container-interop) recommendation,
you can call the `has()` and `get()` methods as expected:

```php
<?php
//...

$beanFactory->has('mySampleService');

$beanFactory->get('mySampleService');
```

To configure there Disco will store the generated proxy classes or the annotation metadata pass an instance of
`\bitExpert\Disco\BeanFactoryConfiguration` as third parameter when creating the `\bitExpert\Disco\AnnotationBeanFactory`
instance:

```php
<?php

$parameters = ['test' => 'This is a test.'];
$config = new \bitExpert\Disco\BeanFactoryConfiguration('/tmp/');

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class, $parameters, $config);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);
```

To influence the object creation, you are able to register post processors by annotating the methods with the
`@BeanPostProcessor` annotation. The method needs to return an instance, implementing the `\bitExpert\Disco\BeanPostProcessor`
interface.

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\BeanPostProcessor;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;
use bitExpert\Disco\Helper\SampleServiceBeanPostProcessor;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @BeanPostProcessor
     */
    public function sampleServiceBeanPostProcessor() : SampleServiceBeanPostProcessor
    {
        return new SampleServiceBeanPostProcessor();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

## Performance Tuning

Since a lot of parsing and reflection logic is involved during the conversion process of the configuration code 
into its "final format" Disco can be rather slow in production mode and during development, especially when 
running  Disco in a virtual machine with a shared hosts folder.

### doctrine/annotations

Make sure to follow the hints on how to improve performance for [Doctrine Annotations](http://doctrine-orm.readthedocs.org/projects/doctrine-common/en/latest/reference/annotations.html) and pick a 
`\Doctrine\Common\Cache\Cache` implementation that suites your needs. To use a specific cache 
implementation pass an instance of it to `\bitExpert\Disco\BeanFactoryConfiguration::construct()` as the third parameter.

### ocramius/ProxyManager

[ProxyManager](https://github.com/Ocramius/ProxyManager) also needs to be configured for faster performance. Read about the details [here](https://ocramius.github.io/ProxyManager/docs/tuning-for-production.html).

For production mode you need to configure the `\ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy` 
by passing an instance of it as second parameter to `\bitExpert\Disco\BeanFactoryConfiguration::construct()`.

In addition to that enable the custom autoloader by passing an instance of `\ProxyManager\Autoloader\Autoloader` as fourth parameter of `\bitExpert\Disco\BeanFactoryConfiguration::construct()`. This will significantly increase
the overall performance of Disco.

When enabling the caching methods, make sure you regularly clean your cache storage directory after 
changing your configuration code!

## Resources

 - sitepoint.com: [Disco with Design Patterns: A Fresh Look at Dependency Injection](https://www.sitepoint.com/disco-with-frameworks-and-design-patterns-a-fresh-look-at-dependency-injection/)

## License

Disco is released under the Apache 2.0 license.
