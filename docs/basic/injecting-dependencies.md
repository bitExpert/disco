# Injecting Dependencies

To inject a bean as a dependency simply call the respective method of the configuration class. It is not possible to refer to a defined alias, you can only inject a dependency by its primary name which is the method name.

```php
<?php

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
     */
    public function mySampleService() : SampleService
    {
        return new SampleService();
    }

    /**
     * @Bean
     */
    public function myMasterService() : MasterService
    {
        return new MasterService($this->mySampleService());
    }
}
```

Disco will respect the bean configuration of `mySampleService` when it gets injected as a dependency — e.g., when `mySampleService` is configured to return a non-singleton instance, Disco will provide a new instance for every call of `$this->mySampleService()`.
