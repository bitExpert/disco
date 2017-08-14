# Beans

Objects managed by Disco are called beans. Disco is responsible for instantiating, assembling and returning bean instances. Disco is aware of the bean life-cycle and knows how to create a bean and its dependencies.

Beans can be marked as being either singleton instances or lazy instances, and can be scoped to the current request or the current session.
