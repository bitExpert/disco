# Bean Post Processor

`\bitExpert\Disco\BeanPostProcessor` implementations can be used to practice interface injection. Interface injection looks a bit like magic, as you do not pass the dependencies explicitly in your configuration code. 

Instead, Disco will inject the dependency after object construction, by executing some additional logic which only gets triggered when your bean instance implements a certain marker interface.

First of all you need to create a class that implements the
`\bitExpert\Disco\BeanPostProcessor` interface:

```php
<?php

class SampleServiceBeanPostProcessor implements \bitExpert\Disco\BeanPostProcessor
{
    /**
     * {@inheritdoc}
     */
    public function postProcess(object $bean, string $beanName): void
    {
        if ($bean instanceof SampleService) {
            $bean->setTest('Set by Bean Post Processor!');
        }
    }
}

```

To register the `SampleServiceBeanPostProcessor` with Disco create a method in your configuration class and annotate it with the `@BeanPostProcessor` annotation.

```php
<?php

use bitExpert\Disco\Annotations\BeanPostProcessor;
use bitExpert\Disco\Annotations\Configuration;
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
}
```

Disco will call every post processor for every new bean instance created. In case of a singleton bean the call for each post processor will happen once, in case of a lazy bean the call will happen when the *"real"* instance gets created. Disco will manage this all for you.
