# Production Tuning

By default Disco configures [ocramius/proxy-manager](https://github.com/Ocramius/ProxyManager) to use the `\ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy`
which dumps generated config class as well as all the configured lazy proxy instances on disk.

In default development mode without a defined proxy autoloader the files get overwritten for every request. This can be quite slow in development and should be avoided in production.

To enable the custom autoloader configure a `\ProxyManager\Autoloader\Autoloader`
instance via  `\bitExpert\Disco\BeanFactoryConfiguration::setProxyAutoloader()`.

```php
$config = new \bitExpert\Disco\BeanFactoryConfiguration('/tmp/');
$config->setProxyAutoloader(
    new \ProxyManager\Autoloader\Autoloader(
        new \ProxyManager\FileLocator\FileLocator('/tmp/'),
        new \ProxyManager\Inflector\ClassNameInflector('Disco')
    )
);
$beanFactory = new \bitExpert\Disco\AnnotationBeanFactory(
  Config::class,
  [],
  $config
);
BeanFactoryRegistry::register($beanFactory);
```

When enabling the proxy autoloader make sure you regularly clean your cache storage directory after changing your configuration code. Otherwise changes made to the configuration might not work as expected.
