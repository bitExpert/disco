# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.9.0

### Added

- [#101](https://github.com/bitExpert/techno/pull/101) Allow parameters in BeanPostProcessor configuration
- [#100](https://github.com/bitExpert/techno/pull/100) Convert @Parameters annotation to "parameters" attribute of @Bean annotation
- [#99](https://github.com/bitExpert/techno/pull/99) Allow multiple aliases per bean and add return type aliases
- [#97](https://github.com/bitExpert/techno/pull/97) Fix of markdown for "Sending a PR" headline
- [#93](https://github.com/bitExpert/techno/pull/93) Enable coveralls support
- [#91](https://github.com/bitExpert/techno/pull/91) Remove the develop branch references from the contribution guide
- [#89](https://github.com/bitExpert/techno/pull/89) Update to PHPUnit 6
- [#82](https://github.com/bitExpert/techno/pull/82) Add bookdown docs and restructure main README.md file

### Deprecated

- [#102](https://github.com/bitExpert/techno/pull/102) Remove BeanFactoryPostProcessor
- [#95](https://github.com/bitExpert/techno/pull/95) Upgrade to ProxyManager 2.1.x to allow to set the minimum PHP version to 7.1

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.8.0

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#90](https://github.com/bitExpert/techno/pull/90) Migrate container-interop dependency to PSR-11

### Fixed

- Nothing.

## 0.7.0

### Added

-  [#81](https://github.com/bitExpert/techno/pull/81) Generate unique names for helper methods
-  [#80](https://github.com/bitExpert/techno/pull/80) Adds setup for simple benchmarks
-  [#78](https://github.com/bitExpert/techno/pull/78) Optimize the code formatting of the generated config class
-  [#73](https://github.com/bitExpert/techno/issues/73) Benchmark Techno and add results to README

### Deprecated

- Nothing.

### Removed

-  [#77](https://github.com/bitExpert/techno/pull/77) Change in Travis config: Remove hhvm, add PHP 7.1 to build matrix

### Fixed

-  [#76](https://github.com/bitExpert/techno/pull/76) Change visibility of wrapBeanAsLazy helper method to protected
-  [#69](https://github.com/bitExpert/techno/pull/69) Use UniqueIdentifierGenerator::getIdentifier to generate unique names for helper methods
-  [#68](https://github.com/bitExpert/techno/pull/68) Change visibility of wrapBeanAsLazy helper method
-  [#66](https://github.com/bitExpert/techno/pull/66) APC fix as suggested by Scrutinizer

## 0.6.3

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#85](https://github.com/bitExpert/techno/pull/85) Fix bool cast for @Parameter required attribute

## 0.6.2

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#83](https://github.com/bitExpert/techno/pull/83) Make Techno not depend on a fixed version of Doctrine Annotations

## 0.6.1

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#75](https://github.com/bitExpert/techno/pull/75) Made sure that Proxy Manager 2.1.0 does not yet get installed

## 0.6.0

### Added

-  [#65](https://github.com/bitExpert/techno/pull/65) Restructure the bean method code generator

### Deprecated

- Nothing.

### Removed

-  [#64](https://github.com/bitExpert/techno/pull/64) Remove Doctrine Cache dependency
-  [#63](https://github.com/bitExpert/techno/pull/63) Remove type check code in generated class.

### Fixed

-  [#61](https://github.com/bitExpert/techno/issues/61) Check given $id for being a non-empty string

## 0.5.0

### Added

-  [#55](https://github.com/bitExpert/techno/pull/55) Introducing aliases for Beans
-  [#53](https://github.com/bitExpert/techno/pull/53) Switched to PHP_EOL.
-  [#52](https://github.com/bitExpert/techno/pull/52) Primitive types can be returned from the bean methods.
-  [#49](https://github.com/bitExpert/techno/issues/49) Upgraded ProxyManger to version 2.x and dropped support for PHP 5.x.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#59](https://github.com/bitExpert/techno/pull/59) Session refactoring
-  [#56](https://github.com/bitExpert/techno/pull/56) Fixed an issue with the serialization of the BeanFactory instance.
-  [#51](https://github.com/bitExpert/techno/pull/51) Fixed $reader property type hint.
-  [#50](https://github.com/bitExpert/techno/pull/50) Added null check in BeanFactoryPostProcessor.

## 0.4.0

### Added

-  [#40](https://github.com/bitExpert/techno/pull/40) Check bean return type against return type annotation.
-  [#37](https://github.com/bitExpert/techno/pull/37) Editing README

### Deprecated

- Nothing.

### Removed

-  [#41](https://github.com/bitExpert/techno/pull/41) Removed FactoryBean interface as it does not make sense any more.

### Fixed

-  [#38](https://github.com/bitExpert/techno/issues/38) has() returns true for internal dependencies.

## 0.3.0

### Added

-  [#35](https://github.com/bitExpert/techno/issues/35) Add flag to tune ProxyManager for production.
-  [#34](https://github.com/bitExpert/techno/pull/34) Added missing previous exception as parameter.
-  [#32](https://github.com/bitExpert/techno/issues/32) BeanPostProcessor cannot depend on method with parameter.
-  [#31](https://github.com/bitExpert/techno/pull/31) Enhanced exception handling for BeanExceptions in AnnotationBeanFactory.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#28](https://github.com/bitExpert/techno/issues/28) Pass GeneratorStrategy to proxyManagerConfiguration only when defined.

## 0.2.1

### Added

-  [#27](https://github.com/bitExpert/techno/issues/27) Update Composer dependencies.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0

### Added

-  [#25](https://github.com/bitExpert/techno/pull/26) Exposing proxymanager configuration
-  [#5](https://github.com/bitExpert/techno/issues/5) Add BeanFactoryBeanPostProcessor
-  [#22](https://github.com/bitExpert/techno/pull/23) Extended exception message thrown in getParameter() to contain property name. 
-  [#20](https://github.com/bitExpert/techno/issues/21) Document the new "protected methods" behaviour. 
-  [#17](https://github.com/bitExpert/techno/issues/18) Fix the issues reported by SensioLabs Insight
-  [#14](https://github.com/bitExpert/techno/pull/14) Ported bitExpert internal Phing setup over to the techno package. 
-  [#11](https://github.com/bitExpert/techno/pull/12) Removed PHP 7 from allow_failures configuration of Travis.
-  [#9](https://github.com/bitExpert/techno/pull/9) Extended documentation with a link to the adroit-techno-demo project. 
-  [#7](https://github.com/bitExpert/techno/pull/7) Added Travis and versioneye badges to the README file.
-  [#6](https://github.com/bitExpert/techno/pull/6) Adding bitexpert/phing-securitychecker dependency.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0

Initial release of the Techno package.
