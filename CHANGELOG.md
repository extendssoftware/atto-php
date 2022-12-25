# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/) and this project adheres to
[Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

### Changed

- Bumped PHP to version 8.1 or higher.

### Deprecated

### Removed

### Fixed

### Security

## [0.2.0] - 2022-04-16

### Added

- Method translation() to set path for translation files to be loaded.
- Method translate() to translate text for global or given locale.
- Method locale() to set global locale.
- Parameter $locale to method assemble() to support route translation.
- Parameter $locale to method match() to support route translation.
- Parameter $locale to method run() for testing.
- Readme section 5.7 with translation support.

### Changed

- Readme section 5.1.2 with path and query string translation.

## [0.1.0] - 2022-04-07

### Added

- Initial commit.

[unreleased]: https://github.com/extendssoftware/atto-php/compare/0.2.0...HEAD

[0.2.0]: https://github.com/extendssoftware/atto-php/commits/0.2.0

[0.1.0]: https://github.com/extendssoftware/atto-php/commits/0.1.0