# Session-aware Beans

Techno will not persists session-aware Beans on its own. You can choose
freely how to accomplish that task yourself. Techno by default provides
a bean store instance `\bitExpert\Techno\Store\SerializableBeanStore`
which contains all created session-aware bean instances. The store can
be serialized and unserialized.

To be able to access the `\bitExpert\Techno\Store\SerializableBeanStore`
instance you have to create a `\bitExpert\Techno\BeanFactoryConfiguration`
instance first which you have to pass to the `\bitExpert\Techno\AnnotationBeanFactory`:

```php
$config = new \bitExpert\Techno\BeanFactoryConfiguration('/tmp/');
$beanFactory = new AnnotationBeanFactory(Config::class, [], $config);
BeanFactoryRegistry::register($beanFactory);
```

At the end of the request grab the session bean store instance from the
`\bitExpert\Techno\BeanFactoryConfiguration`, serialize it and store the
result somewhere, e.g. Session, Redis, Memache:

```php
$sessionBeans = serialize($config->getSessionBeanStore());
```

At the beginning of the request you need to unserialize the session bean
store instance and pass it to the `\bitExpert\Techno\BeanFactoryConfiguration`
object before the `\bitExpert\Techno\AnnotationBeanFactory` instance gets
created.

In addition to that you need to define a custom proxy autoloader to be
able to load the classes before unserializing the `\bitExpert\Techno\Store\SerializableBeanStore`
instance otherwise PHP is not able to find the class and will return an error.

```php
$config = new \bitExpert\Techno\BeanFactoryConfiguration('/tmp/');
$config->setProxyAutoloader(
    new \ProxyManager\Autoloader\Autoloader(
        new \ProxyManager\FileLocator\FileLocator('/tmp/'),
        new \ProxyManager\Inflector\ClassNameInflector('Techno')
    )
);
$beanFactory = new \bitExpert\Techno\AnnotationBeanFactory(Config::class, [], $config);
BeanFactoryRegistry::register($beanFactory);

$sessionBeans = unserialize($sessionBeans);
$config->setBeanStore($sessionBeans);
```
