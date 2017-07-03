# Bean configuration

The `@Bean` annotation comes with a few attributes which influence how
beans are created or how their internal state will be kept during
different requests.

## Singleton Beans

By default a bean instance is configured to be returned as a singleton
instance which means multiple calls to `\bitExpert\Techno\AnnotationBeanFactory::get()`
will retrieve the very same instance. The explicit bean configuration
to return singleton instances would read as follows:

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean({"singleton" = true})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

In case you explicitly want to return a new instance for every
`\bitExpert\Techno\AnnotationBeanFactory::get()` call, set the singleton
attribute to `false`.

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean({"singleton" = false})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

## Lazy Beans

By default Techno will return non-lazy bean instances, which means you will
get the exact same instance as you have defined in your configuration
code. The explicit bean configuration to return non-lazy instances
would read as follows:

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean({"lazy" = false})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

In case the construction of your bean is quite time consuming - e.g. a
call to a remote service is made - you can instruct Techno to return a
lazy instance of your bean, the concrete instance gets created upon the
first method call. Simply set the lazy attribute to `true` to let Techno
wrap your bean as a lazy instance:

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean({"lazy" = true})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

For the lazy bean management Techno uses [ocramius/proxy-manager](https://github.com/Ocramius/ProxyManager), so the
same rules apply for Techno as for Proxy Manager when it comes to creating
lazy instances. For examle you are not able to return a lazy instance of
a final class as the class cannot be extended - which is the basic
principle when it comes to creating lazy instances.

## Bean scopes

Beans in Techno can either live in the request scope or the session scope.
Request scoped beans are created newly for every new request, whilest session
scoped beans persist beyond a page refresh.

Techno itself is not responsible for managing session state in any way but
will allow you to grab session instances from the container, persists them and
retrieve them during the new request.

By default beans are request scoped which means a new instance gets created
for every new request:

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean({"scope" = request})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

To let Techno create a session scoped bean, simply set the `scope`
attribute to `session`:

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
     * @Bean({"scope" = session})
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

Read in the advanced section of the documentation what else is needed
to let session scoped beans persist beyond a page refresh.

## Bean aliases

Using class method names as bean identifiers is a simple way to avoid
naming collisions as your IDE will warn you in case you define multiple
methods with the same name.

As a drawback you are quite limited when it comes to bean names. To quote
the [PHP manual](http://php.net/manual/en/functions.user-defined.php): "A valid function name starts with a letter or underscore,
followed by any number of letters, numbers, or underscores".

This led to introducing aliases for beans. Each bean can have multiple
aliases and two types of aliases are possible. In case of collisions
(same alias is used for different beans) Techno will throw an exception.
You're asked to avoid/resolve such conflicts.

Simply add the `aliases` attribute to the `@Bean` annotation to define a
list of `@Alias`:

```php
<?php

use bitExpert\Techno\Annotations\Bean;
use bitExpert\Techno\Annotations\Àlias;
use bitExpert\Techno\Annotations\Configuration;
use bitExpert\Techno\Helper\SampleService;

/**
 * @Configuration
 */
class MyConfiguration
{
    /**
      * @Bean({
      *   "aliases"={
      *      @Alias({"name" = "\Identifier\With\Namespace"}),
      *      @Alias({"type" = true})
      *   }
      * })
      */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}
```

Now you can either pass `mySampleService` or `\Identifier\With\Namespace`
or `bitExpert\Techno\Helper\SampleService` to the `\bitExpert\Techno\AnnotationBeanFactory::get()`
or `\bitExpert\Techno\AnnotationBeanFactory::has()` calls.

The example uses both available alias types. Let's look at them one after the other.

### Named Alias

`@Alias({"name" = "\Identifier\With\Namespace"})`

This alias type is pretty much self explaining. What you define as name
can later be used to get the service from the bean factory.

### Return Type Alias

`@Alias({"type" = true})`

Setting the `type` attribute to `true` (name attribute must be omitted in this case) 
tells Techno to use the return type of the method as an alias.

This is very useful because you can work with PHP's `::class` language construct
like in this example: `$annotationBeanFactory->get(SampleService::class)`.
If you now refactor `SampleService` (rename or move to other namespace) a
modern IDE will automatically change the alias and your code continues to
work. Classes or interfaces can be used as an alias as well as PHP's
native types like array, bool, int, float, string.

### Mix and Match

You can use many named aliases and/or one type alias per bean.

Aliases also work for protected beans, exposing the bean by the given alias
but not by their method name!
