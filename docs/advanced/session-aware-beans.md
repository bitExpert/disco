# Session-aware Beans

Disco will not persists session-aware Beans on its own. You can choose freely how to accomplish that task yourself. Disco by default provides a bean store instance `\bitExpert\Disco\Store\SerializableBeanStore` which contains all created session-aware bean instances. The store can be serialized and unserialized.

To be able to access the `\bitExpert\Disco\Store\SerializableBeanStore` instance you have to create a `\bitExpert\Disco\BeanFactoryConfiguration`
instance first which you have to pass to the  `\bitExpert\Disco\AnnotationBeanFactory`:

```php
$config = new \bitExpert\Disco\BeanFactoryConfiguration('/tmp/');
$beanFactory = new AnnotationBeanFactory(Config::class, [], $config);
BeanFactoryRegistry::register($beanFactory);
```

At the end of the request grab the session bean store instance from the
`\bitExpert\Disco\BeanFactoryConfiguration`, serialize it and store the result somewhere, e.g., *Session*, *Redis*, *Memcache*:

```php
$sessionBeans = serialize($config->getSessionBeanStore());
```

At the beginning of the request you need to unserialize the session bean store instance and pass it to the `\bitExpert\Disco\BeanFactoryConfiguration`
object before the `\bitExpert\Disco\AnnotationBeanFactory` instance gets created.

In addition to that you need to define a custom proxy autoloader to be able to load the classes before unserializing the
`\bitExpert\Disco\Store\SerializableBeanStore` instance otherwise PHP is not able to find the class and will return an error.

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

$sessionBeans = unserialize($sessionBeans);
$config->setBeanStore($sessionBeans);
```
