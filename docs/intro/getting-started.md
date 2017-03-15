# Getting Started

## Prerequisites

Disco needs at least [PHP](http://php.net) 7.0, as Disco relies on the [return type declarations](http://php.net/manual/en/functions.returning-values.php#functions.returning-values.type-declaration) feature introduced with PHP 7.0.

## Installation

The preferred way of installing `bitexpert/disco` is through Composer, by calling the following command:

```php
composer.phar require bitexpert/disco
```

## Using Disco

To instantiate Disco you need to do two things:

1. Create a configuration class
2. Bootstrap Disco

### Create a configuration class

First, you need to create a configuration class, an example of which you can see below.
To operate as a configuration class it needs to be marked with a `@Configuration` annotation.

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

### Bootstrap Disco

Next, you need to bootstrap disco.
To do so, use the following code in your bootstrapping logic.

```php
<?php

use \bitExpert\Disco\{
  AnnotationBeanFactory,
  BeanFactoryRegistry
}

$beanFactory = new AnnotationBeanFactory(MyConfiguration::class);
BeanFactoryRegistry::register($beanFactory);
```

This creates a `\bitExpert\Disco\AnnotationBeanFactory` instance and registers it with the `\bitExpert\Disco\BeanFactoryRegistry`.
This is necessary because Disco needs to retrieve the active container instance in several locations where it does not have access to the container instance itself.
