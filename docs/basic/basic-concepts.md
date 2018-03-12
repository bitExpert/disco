# Basic concepts

## @Bean and @Configuration

The central artifacts used in Disco's PHP based configuration language are `@Configuration`-annotated classes and `@Bean`-annotated methods.

The `@Bean` annotation is used to indicate that a method instantiates, configures and initializes a new object which is managed by Disco.

Annotating a class with `@Configuration` indicates that its primary purpose is as a source of bean definitions. The simplest possible `@Configuration` class would read as follows:

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

## Using the @Bean Annotation

To declare a bean, simply annotate a method with the `@Bean` annotation. You use this method to register a bean instance within Disco of the type specified as the method’s return value. The bean identifier is the method name. The following is a simple example of a `@Bean` method declaration:

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

Beans with a `public` visibility can be retrieved via the `\bitExpert\Disco\AnnotationBeanFactory`. Beans with a `protected` visibility are so-called internal dependencies and thus cannot be retrieved via the `\bitExpert\Disco\AnnotationBeanFactory`.

Public methods of the configuration class have to while protected classes may be marked with the `@Bean` annotation. The `\bitExpert\Disco\AnnotationBeanFactory` will throw an exception when public methods without a `@Bean` annotation are found.

## PSR-11

Disco implements the [PSR-11](http://www.php-fig.org/psr/psr-11/) interface. That means you can use Disco in any application that can deal with PSR-11 containers, e.g., [zend-expressive](https://github.com/zendframework/zend-expressive).

In a nutshell the container-interop project provides a interface for DI containers that consists of two methods: `get()` & `has()`.

```php
<?php

namespace Psr\Container;

interface ContainerInterface
{
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool
     */
    public function has($id);
}
```

The caller can retrieve an instance via calling the `get()` method. The `$id` passed to get needs either to be a method name declaring a bean instance or an alias or a bean method.

The call to `has()` simply checks if potentially a dependency by the given `$id` exists. In theory the call to `get()` for the same `$id` can still fail when an error occurs during the instantiation phase of the bean.
