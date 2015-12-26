# bitexpert/disco
This package provides a [container-interop](https://github.com/container-interop/container-interop) compatible, 
annotation-based dependency injection container. Have a look at the [adroit-disco-demo](https://github.com/bitExpert/adroit-disco-demo) 
project to find out how to use Disco.

[![Build Status](https://travis-ci.org/bitExpert/disco.svg?branch=master)](https://travis-ci.org/bitExpert/disco)
[![Dependency Status](https://www.versioneye.com/php/bitexpert:disco/dev-master/badge.svg)](https://www.versioneye.com/php/bitexpert:disco/dev-master)

Installation
------------

The preferred way of installation is through Composer. Simply add `bitexpert/disco` as a dependency:

```
composer.phar require bitexpert/disco
```

Usage
-----

Define a configuration class, the class needs to be marked with an @Configuration annotation:

```php
use bitExpert\Disco\Annotations\Configuration;

/**
 * @Configuration
 */
class MyConfiguration
{
}
```

Add a public or protected method for each instance you want to let Disco manage for you. Instances managed by a public
method are accessible via the has() and get() methods of the \bitExpert\Disco\AnnotationBeanFactory class. Any instance 
managed by a protected method is seen as a local dependency and is just accessible "inside" the container configuration. 
This is useful for dependencies like database connectors which you most likely do not want to expose as a "real" dependency.

Each method needs to be marked with an @Bean annotation as well as the @return annotation defining the type of the 
dependency (which is mainly needed for the lazy proxy magic).

```php
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

The @Bean annotation comes with a few configuration settings for influencing the lifetime of the instance. You can 
define the "scope" (which is either the string "request" or "session"), define a boolean value for "lazy" or define
a boolean value for "singleton". The following configuration creates an lazy-loaded, singleton, request aware instance:

```php
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
     * @return SampleService
     */
    public function mySampleService()
    {
        return new SampleService();
    }
}
```

To inject a dependency simply call the respective method:

```php
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
     * @return SampleService
     */
    public function mySampleService()
    {
        return new SampleService();
    }
    
    /**
     * @Bean
     * @return MasterService
     */
    public function myMasterService()
    {
        return new MasterService($this->mySampleService());
    }
}
```

To inject parameters use the @Parameter annotation. Parameters need to be passed as a constructor argument to the 
\bitExpert\Disco\AnnotationBeanFactory instance:

```php
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
     * @return SampleService
     */
    public function mySampleService($test = '')
    {
        $service = new SampleService();
        $service->setTest($test);
        return $service;
    }
}
```

To instantiate the \bitExpert\Disco\AnnotationBeanFactory instance, proceed as follows: 

```php
$parameters = ['test' => 'This is a test.'];

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class, $parameters);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);
```

Since Disco implements the [container-interop](https://github.com/container-interop/container-interop) recommendation,
you are able to call the has() and get() method as expected:

```php
$beanFactory->has('mySampleService');

$beanFactory->get('mySampleService');
```

To enable the caching of the annotation metadata, pass an instance of \Doctrine\Common\Cache\Cache as 3rd parameter
when creating the \bitExpert\Disco\AnnotationBeanFactory instance:

```php
$parameters = ['test' => 'This is a test.'];
$cache = new \Doctrine\Common\Cache\FilesystemCache('/tmp/');

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class, $parameters, $cache);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);
```

To influence the object creation you are able to register post processors by annotating the methods with the 
@BeanPostProcessor annotation. The method needs to return an instance implementing the \bitExpert\Disco\BeanPostProcessor
interface.

```php
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
     * @return SampleServiceBeanPostProcessor
     */
    public function sampleServiceBeanPostProcessor()
    {
        return new SampleServiceBeanPostProcessor();
    }

    /**
     * @Bean({"singleton"=true, "lazy"=true, "scope"="request"})
     * @return SampleService
     */
    public function mySampleService()
    {
        return new SampleService();
    }
}
```

License
-------

Disco is released under the Apache 2.0 license.
