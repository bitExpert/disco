# What Is Disco?

Disco is a [container-interop](https://github.com/container-interop/container-interop) compatible,
annotation-based dependency injection container.

## Why Disco?

Instead of using _XML_, _YAML_, or _JSON_, Disco uses PHP code, specifically a PHP class, to define both objects and their dependencies.

Using PHP code as a configuration language has the following advantages:
- Relies on the language the developer knows best
- No need to "compile" the configuration to determine if it is "correct" or not
- The developer can rely on the same refactoring features of their IDE (or text editor) for the configuration as for the code.
- The developer can depend on the same auto-complete features of their IDE for the configuration as for the code.

Several other DI containers in PHP also rely on PHP code for configuring dependencies. What makes Disco unique in its design is that it enforces the usage of typed dependencies everywhere in the configuration code. Doing so helps developers to spot type errors right where they happen, assuming a sophisticated IDE is used.

