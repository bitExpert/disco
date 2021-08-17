# Getting Started

## Prerequisites

Version 1 of Disco needs at least [PHP](http://php.net) 8.0 to run. Disco relies on the [return type declarations](http://php.net/manual/en/functions.returning-values.php#functions.returning-values.type-declaration) 
and [attributes](https://www.php.net/manual/en/language.attributes.overview.php) feature introduced with PHP 8.0 as well as the `ocramius/proxy-manager` package.

:warning: Look at version 0.x if you are still on [PHP](http://php.net) 7.4.

## Installation

The preferred way of installing `bitexpert/disco` is through Composer. Simply require the `bitexpert/disco` package:

```
composer.phar require bitexpert/disco
```

## Using Disco

To instantiate Disco use the following code in your bootstrapping logic. Create an instance of the ```\bitExpert\Disco\AnnotationBeanFactory``` and register the
instance with the ```\bitExpert\Disco\BeanFactoryRegistry```. 

The second step is important as Disco needs to grab the active container instance in a few locations where it does not have access to the container instance itself.

```php
<?php

$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(MyConfiguration::class);
\bitExpert\Disco\BeanFactoryRegistry::register($beanFactory);
```

Next up you need to create a configuration class ```MyConfiguration```. The class needs to be attributed with `#[Configuration]`.

```php
<?php

use bitExpert\Disco\Annotations\Configuration;

#[Configuration]
class MyConfiguration
{
}
```
