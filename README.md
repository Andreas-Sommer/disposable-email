# disposable-email
A service extension to compile lists of disposable and free mail providers, offering a simple way to validate email addresses against them.

## install
````
composer req belsignum/disposable-email
````

## Extension configuration options
**basic.type - Lists to use**
- Disposal email provider
- Free email provider
- Disposable & free email provider
- Custom lists only



**basic.customLists - Custom Lists (comma seperated list of public uri)**

## Update list

### CLI
````
php vendor/bin/typo3 disposable-email:update
````

### Scheduler
1. Add new Sceduler Task
2. Choose Class "Execute console commands"
3. Choose Schedulable Command "disposable-email:update: Updates the current list with the remote endpoint. updates are provided usually weekly."
