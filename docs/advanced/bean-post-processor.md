# Bean Post Processor

`\bitExpert\Disco\BeanPostProcessor` implementations can be used to
practise interface injection. Interface injection looks a bit like magic
as you do not pass the dependencies explicitly in your configuration code
but Disco will inject the dependency after object construction by executing
some additional logic which only gets triggered when your bean instance
implements a certain marker interface.

First of all you need to create a class that implements the
`\bitExpert\Disco\BeanPostProcessor` interface:

```php
<?php

class SampleServiceBeanPostProcessor implements \bitExpert\Disco\BeanPostProcessor
{
    /**
     * {@inheritdoc}
     */
    public function postProcess($bean, $beanName)
    {
        if ($bean instanceof SampleService) {
            $bean->setTest('Set by Bean Post Processor!');
        }
    }
}

```

To register the `SampleServiceBeanPostProcessor` with Disco create a
method in your configuration class and annotate it with the `@BeanPostProcessor`
annotation.

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

Disco will call every post processor for every new bean instance created.
In case of a singleton bean the call for each post processor will happen once,
in case of a lazy bean the call will happen when the "real" instance gets
created. Disco will manage this all for you.

# BeanFactoryPostProcessor

Disco comes with one post processor implementation out-of-the-box:
`\bitExpert\Disco\BeanFactoryPostProcessor`. This post processor will only
run for classes implementing the `\bitExpert\Disco\BeanFactoryAware`
interface. As the name implies it will inject the current BeanFactory
instance into the created bean instance.
