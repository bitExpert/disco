# What Is Disco?

Disco is a [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible, annotation-based dependency injection container.

## Why Disco?

Instead of using _XML_, _YAML_, or _JSON_ Disco uses PHP code - specifically a PHP class — to define objects and their dependencies.

Using PHP code as a configuration language has the following advantages:

- Relies on the language the developer knows best.
- No need to "compile" the configuration to determine if it is "correct" or not.
- The developer can rely on the same refactoring features of their IDE for the configuration as for the code.
- The developer can rely on the same auto-complete features of their IDE for the configuration as for the code.

There are a few other DI containers in PHP that also rely on PHP code to configure their dependencies. What makes Disco unique in its design is that it enforces the usage of typed dependencies everywhere in the configuration code. This helps a developer to spot type errors right where they happen given a sophisticated IDE is used.