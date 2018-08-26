# Roadmap

PHP-Typography follows [Semantic Versioning 2.0.0](https://semver.org/spec/v2.0.0.html), i.e. each release is numbered `MAJOR.MINOR.PATCH`.
*   `MAJOR` version is incremented when there are incompatible API changes (not necessarily huge, just not backwards compatible).
*   `MINOR` version is incremented when a release adds backwards-compatible features.
*   `PATCH` level is incremented for backwards-compatible bug fixes.

The current stable release at the time of this writing is 6.2.0. The API has been mostly stable for a while, however some parts of it need a bit of polishing (documentation, parameter order etc.). Except for the `Settings` class, which so far remains monolithic, everything has been broken up in to self-contained smaller classes.

Partioning the `Settings` class to be more modular is conceptually difficult, but has to happen sooner or later. When it's done, there will be a 7.0.0 release. All development towards that end happens in the `7.0-dev` branch.

**Last updated:** 2018-08-26
