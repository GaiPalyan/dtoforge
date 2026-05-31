# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `#[CastEachTo(Item::class)]` attribute to cast each element of an `array` property into a given DTO class. The class name is a compiler-resolved FQCN, so casting works regardless of namespace or `use` aliases.

### Changed

- **BREAKING:** Array-item casting is now opt-in via `#[CastEachTo]` only. Docblock `@var Item[]` annotations are treated as documentation and no longer drive casting. Properties that previously relied on a fully-qualified `@var \App\Item[]` docblock to cast must add the `#[CastEachTo]` attribute.

### Fixed

- Casting a collection no longer fails with `Class not found` when the docblock item type is an unqualified short name. The previous implementation passed the raw docblock token to `new`, bypassing namespace resolution.

## [1.0.0] - 2026-04-10

### Added

- Initial release: DTO creation from arrays/JSON/setters, attribute-based validation, scalar type casting, nested DTOs, default value generation, and `fill`/`merge`/`diff`/`clone`/`toArray`/`toJson` helpers.

[Unreleased]: https://github.com/GaiPalyan/dtoforge/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/GaiPalyan/dtoforge/releases/tag/v1.0.0
