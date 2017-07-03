# What Is Techno?

Techno is a a [PSR-11](http://www.php-fig.org/psr/psr-11/) compatible, annotation-based dependency injection container.

## Why Techno?

Instead of using XML, YAML, or JSON Techno uses PHP code - specifically a
PHP class - to define objects and their dependencies.

Using PHP code as a configuration language has the following advantages:
- Relies on the language the developer knows best
- No need to "compile" the configuration to determine if it is "correct" or not
- The developer can rely on the same refactoring features of their IDE for the configuration as for the code.
- The developer can rely on the same auto-complete features of their IDE for the configuration as for the code.

There a a few other DI containers in PHP that also rely on PHP code to
configure the dependencies. What makes Techno unique in its design is that
it enforces the usage of typed dependencies everywhere in the configuration
code. This helps a developer to spot type errors right where they happen
given a sophisticated IDE is used.
