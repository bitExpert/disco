# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.4.0

### Added

-  [#40](https://github.com/bitExpert/disco/pull/40) Check bean return type against return type annotation.
-  [#37](https://github.com/bitExpert/disco/pull/37) Editing README

### Deprecated

- Nothing.

### Removed

-  [#41](https://github.com/bitExpert/disco/pull/41) Removed FactoryBean interface as it does not make sense any more.

### Fixed

-  [#38](https://github.com/bitExpert/disco/issues/38) has() returns true for internal dependencies.

## 0.3.0

### Added

-  [#35](https://github.com/bitExpert/disco/issues/35) Add flag to tune ProxyManager for production.
-  [#34](https://github.com/bitExpert/disco/pull/34) Added missing previous exception as parameter.
-  [#32](https://github.com/bitExpert/disco/issues/32) BeanPostProcessor cannot depend on method with parameter.
-  [#31](https://github.com/bitExpert/disco/pull/31) Enhanced exception handling for BeanExceptions in AnnotationBeanFactory.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#28](https://github.com/bitExpert/disco/issues/28) Pass GeneratorStrategy to proxyManagerConfiguration only when defined.

## 0.2.1

### Added

-  [#27](https://github.com/bitExpert/disco/issues/27) Update Composer dependencies.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0

### Added

-  [#25](https://github.com/bitExpert/disco/pull/26) Exposing proxymanager configuration
-  [#5](https://github.com/bitExpert/disco/issues/5) Add BeanFactoryBeanPostProcessor
-  [#22](https://github.com/bitExpert/disco/pull/23) Extended exception message thrown in getParameter() to contain property name. 
-  [#20](https://github.com/bitExpert/disco/issues/21) Document the new "protected methods" behaviour. 
-  [#17](https://github.com/bitExpert/disco/issues/18) Fix the issues reported by SensioLabs Insight
-  [#14](https://github.com/bitExpert/disco/pull/14) Ported bitExpert internal Phing setup over to the disco package. 
-  [#11](https://github.com/bitExpert/disco/pull/12) Removed PHP 7 from allow_failures configuration of Travis.
-  [#9](https://github.com/bitExpert/disco/pull/9) Extended documentation with a link to the adroit-disco-demo project. 
-  [#7](https://github.com/bitExpert/disco/pull/7) Added Travis and versioneye badges to the README file.
-  [#6](https://github.com/bitExpert/disco/pull/6) Adding bitexpert/phing-securitychecker dependency.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0

Initial release of the Disco package.
