# bitexpert/disco

This package provides a [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible, annotation-based dependency injection container. Have a look at the [disco-demos](https://github.com/bitExpert/disco-demos) project to find out how to use Disco.

[![Build Status](https://travis-ci.org/bitExpert/disco.svg?branch=master)](https://travis-ci.org/bitExpert/disco)
[![Coverage Status](https://coveralls.io/repos/github/bitExpert/disco/badge.svg?branch=master)](https://coveralls.io/github/bitExpert/disco?branch=master)

## Installation

The preferred way of installing `bitexpert/disco` is through Composer.
You can add `bitexpert/disco` as a dependency, as follows:

```
composer.phar require bitexpert/disco
```

## Usage

To instantiate Disco use the following code in your bootstrapping logic.
Create an instance of the `\bitExpert\Disco\AnnotationBeanFactory` and register the instance with the `\bitExpert\Disco\BeanFactoryRegistry`.
The second step is important as Disco needs to grab the active container instance in a few locations where it does not have access to the container instance itself.

```php
<?php

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);
```

Next up you need to create a configuration class `MyConfiguration` and document it with a `@Configuration` annotation.

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

To declare a configuration entry, 1) add a method to your `MyConfiguration` class, and 2) annotate the method with the `@Bean` annotation.
Doing this registers the instance with Disco and uses the type specified by the method’s return value. The primary identifier is the method name:

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

To let Disco return the entry with the id `mySampleService` call the `get()` method of `\bitExpert\Disco\AnnotationBeanFactory`, as follows:

```php
<?php

$beanFactory->get('mySampleService');
```

## Documentation

Documentation is [in the docs tree](docs/), and can be compiled using [bookdown](http://bookdown.io).

```console
$ php ./vendor/bin/bookdown docs/bookdown.json
$ php -S 0.0.0.0:8080 -t docs/html/
```

Then point your browser to [http://localhost:8080/](http://localhost:8080/)

## Contribute

Please feel free to fork and extend existing or add new features and send a pull request with your changes! To establish a consistent code quality, please provide unit tests for all your changes and adapt the documentation.

## Want To Contribute?

If you feel that you have something to share, then we’d love to have you.
Check out [the contributing guide](CONTRIBUTING.md) to find out how, as well as what we expect from you.

## Resources

 - sitepoint.com: [Disco with Design Patterns: A Fresh Look at Dependency Injection](https://www.sitepoint.com/disco-with-frameworks-and-design-patterns-a-fresh-look-at-dependency-injection/)
 - php[architect]: [Education Station: Your Dependency Injection Needs a Disco](https://www.phparch.com/magazine/2016-2/september/)
 - Presentation: [Disco - A Fresh Look at DI](https://talks.bitexpert.de/phpugffm16-disco/) at [PHPUGFFM V/2016](http://www.phpugffm.de)

## License

Disco is released under the Apache 2.0 license.
