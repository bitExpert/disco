# Structure the Configuration

You basically have two ways to structure your configuration classes, you
can either extend parent classes or use traits to mix and match your
configuration.

## Extending Configuration

One way to "customize" your configuration code is to simply sub-classing
a parent configuration class and then overwrite the bean configuration
method that you want to change.

Due to a limitation of PHP's design you are not able to return a different
type thus you are bound to return the same instance.

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
class ParentConfig
{
    /**
     * @Bean
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}

/**
 * @Configuration
 */
class ChildConfig extends ParentConfig
{
}
```
## Traits

As of PHP 5.4.0, PHP implements a method of code reuse called Traits. To
quote the [PHP manual](http://php.net/manual/en/language.oop5.traits.php):

> "Traits are a mechanism for code reuse in single inheritance languages
such as PHP. A Trait is intended to reduce some limitations of single
inheritance by enabling a developer to reuse sets of methods freely in
several independent classes living in different class hierarchies".

In the simplest form define your trait and include it via the `use`
statement in your config class.

```php
<?php

use bitExpert\Disco\Annotations\Bean;
use bitExpert\Disco\Annotations\Configuration;
use bitExpert\Disco\Helper\SampleService;

/**
 * @Configuration
 */
trait SampleServiceConfig
{
    /**
     * @Bean
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }
}

/**
 * @Configuration
 */
class Config
{
    use SampleServiceConfig;
}
```

Traits offer powerful ways of conflict resolution, see the part in the
[PHP manual](http://php.net/manual/en/language.oop5.traits.php#language.oop5.traits.conflict) to learn about the `as` and `insteadof` operators.
