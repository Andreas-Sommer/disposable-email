# disposable-email
A service extension to compile lists of disposable and free mail providers, offering a simple way to validate email addresses against them.

## install
````
composer req belsignum/disposable-email
````

## Extension configuration options
**basic.type - Lists to use**
- Disable validation
- Disposable email provider
- Free email provider
- Disposable & free email provider
- Custom lists only

**basic.customLists - Custom Lists (comma seperated list of public uri)**
Only absolute `https://` URLs are supported.

**powermail.overloadEmailValidation - Overload Email Validation, else adds additional validation rule**

## Powermail validation rule
- Disposable Email
- Or overload Email validation rule - Set extension configuration powermail.overloadEmailValidation=1

## Powermail list type override via TypoScript
Use TypoScript to override the list type for Powermail validation.

```typoscript
plugin.tx_powermail.settings.setup.tx_disposableemail.overrideExtensionSettings {
  # one override for current Powermail context
  type = disposable

  # optional form specific mapping
  typeByForm {
    # disable disposable email validation for this form
    100 = disable

    123 = disposable
    456 = freemail
  }
}
```

`typeByForm` has priority over `type`.
In `typeByForm`, use the UID of the default language form record. Localized forms are resolved via `l10n_parent`.
Supported values are `disable`, `disposable`, `freemail`, `both`, `customListsOnly`.

## List storage and deduplication
- Domains are stored with `provider_type` (`disposable`, `freemail`, `custom`).
- Deduplication is done per `provider_type`.
- Custom lists are stored as `custom` and are checked in addition to selected built-in list types.
- DB-level uniqueness is enforced by `uniq_domain_provider_type (domain, provider_type)`.

## Update list

### CLI
````
php vendor/bin/typo3 disposable-email:update
````

### Scheduler
1. Add new Sceduler Task
2. Choose Class "Execute console commands"
3. Choose Schedulable Command "disposable-email:update: Updates the current list with the remote endpoint. updates are provided usually weekly."
