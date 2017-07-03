# Getting Started

## Prerequisites

Techno needs at least [PHP](http://php.net) 7.1 to run. Techno relies on the [return type declarations](http://php.net/manual/en/functions.returning-values.php#functions.returning-values.type-declaration)
feature introduced with PHP 7.0 as well as the `ocramius/proxy-manager` package
which in the latest version is only compatible with PHP 7.1.

## Installation

The preferred way of installing `bitexpert/techno` is through Composer. Simply require the `bitexpert/techno` package:

```
composer.phar require bitexpert/techno
```

## Using Techno

To instanciate Techno use the following code in your bootstrapping logic. Create
an instance of the ```\bitExpert\Techno\AnnotationBeanFactory``` and register the
instance with the ```\bitExpert\Techno\BeanFactoryRegistry```. The second step is
important as Techno needs to grab the active container instance in a few locations
where it does not have access to the container instance itself.

```php
<?php

$beanFactory = new \bitExpert\Techno\AnnotationBeanFactory(MyConfiguration::class);
\bitExpert\Techno\BeanFactoryRegistry::register($beanFactory);
```

Next up you need to create a configuration class ```MyConfiguration```.
The class needs to be marked with a `@Configuration` annotation.

```php
<?php

use bitExpert\Techno\Annotations\Configuration;

/**
 * @Configuration
 */
class MyConfiguration
{
}
```
