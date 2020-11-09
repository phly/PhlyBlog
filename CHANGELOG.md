# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.1.1 - TBD

### Added

- Nothing.

### Changed

- [#31](https://github.com/phly/PhlyBlog/pull/31) changes the `Compiler` to implement `EventsCapableInterface` instead of `EventManagerAwareInterface` (the latter is a superset of the former). Doing so ensures that any initializers for `EventManagerAwareInterface` do not trigger, which prevents double-injection of the `EventManager` instance, and thus prevents overwriting any listeners attached via delegator factories. The `setEventManager()` method is still defined.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.1.0 - 2020-11-04

### Added

- [#29](https://github.com/phly/PhlyBlog/pull/29) adds a `PhlyBlog\CompilerFactory`, and wires the `PhlyBlog\Compiler` service to be created via that factory.

### Changed

- [#29](https://github.com/phly/PhlyBlog/pull/29) adds an optional `?Compiler $compiler = null` argument to the `CompileCommand` constructor. When provided, the command will use that `Compiler` instance. The `CompileCommandFactory` now pulls the `Compiler` service from the container and passes it for that argument.


-----

### Release Notes for [2.1.0](https://github.com/phly/PhlyBlog/milestone/3)



### 2.1.0

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### enhancement

 - [29: Add Compiler as a container service](https://github.com/phly/PhlyBlog/pull/29) thanks to @weierophinney and @vrkansagara

## 2.0.2 - 2020-10-13

### Fixed

- [#26](https://github.com/phly/PhlyBlog/pull/26) fixes issues with display of progress bars, ensuring they display along with the label detailing what is being compiled.

- [#26](https://github.com/phly/PhlyBlog/pull/26) fixes the remaining issues with rendering the blog posts using the `phly-blog:compile` tooling, ensuring that the view is capable of identifying a renderer, posts are rendered within the layout, and that no duplication of metadata occurs.

-----

### Release Notes for [2.0.2](https://github.com/phly/PhlyBlog/milestone/5)

- Total issues resolved: **0**
- Total pull requests resolved: **1**
- Total contributors: **1**

#### bug

 - [26: Fix rendering of posts and pagination](https://github.com/phly/PhlyBlog/pull/26) thanks to @weierophinney

## 2.0.1 - 2020-10-13

### Fixed

- [#23](https://github.com/phly/PhlyBlog/pull/23) fixes an issue in the phly-blog:compile command with resolution of the Tags listener, which led to a fatal error.

-----

### Release Notes for [2.0.1](https://github.com/phly/PhlyBlog/milestone/2)

- Total issues resolved: **1**
- Total pull requests resolved: **1**
- Total contributors: **2**

#### bug

 - [23: Fix tags listener resolution](https://github.com/phly/PhlyBlog/pull/23) thanks to @weierophinney and @vrkansagara

