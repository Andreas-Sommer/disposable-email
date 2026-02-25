# Changelog

## Unreleased

### Fixed
- Prevented scheduler import failures on duplicate `(domain, provider_type)` entries caused by edge-case domain normalization and DB collation behavior.

## 10.1.0 - 2026-02-25

### Added
- Powermail list type overrides via TypoScript:
  - `plugin.tx_powermail.settings.setup.tx_disposableemail.overrideExtensionSettings.type`
  - `plugin.tx_powermail.settings.setup.tx_disposableemail.overrideExtensionSettings.typeByForm.<formUid>`
- Provider type storage in `tx_disposableemail_list.provider_type` with unique key `uniq_domain_provider_type (domain, provider_type)`.

### Changed
- `DisposableEmailService` now supports optional list type filtering while defaulting to global extension configuration.
- Import command now persists domains by `provider_type` and deduplicates per list type (`disposable`, `freemail`, `custom`).
- Powermail Xclass registration is again controlled by `overloadEmailValidation=1`.
- `typeByForm` resolves localized forms via default language form (`l10n_parent`).
- `typeByForm.<formUid> = disable` disables disposable-email validation for a specific form.
- Global `basic.type = disable` is supported to disable validation by configuration.
- Overload validation error messages now vary by effective list type (`disposable`, `freemail`, `both`, `custom`).
- Provider list endpoints are restricted to absolute HTTPS URLs.
