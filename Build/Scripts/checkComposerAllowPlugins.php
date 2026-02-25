<?php
declare(strict_types=1);

$composerJsonPath = getcwd() . '/composer.json';
$composerLockPath = getcwd() . '/composer.lock';

if (!is_file($composerJsonPath)) {
    fwrite(STDERR, "composer.json not found.\n");
    exit(1);
}

if (!is_file($composerLockPath)) {
    fwrite(STDERR, "composer.lock not found. Run 'composer update --no-plugins --no-scripts --no-install' first.\n");
    exit(1);
}

$composerJson = json_decode((string)file_get_contents($composerJsonPath), true);
$composerLock = json_decode((string)file_get_contents($composerLockPath), true);

if (!is_array($composerJson) || !is_array($composerLock)) {
    fwrite(STDERR, "Failed to decode composer.json or composer.lock.\n");
    exit(1);
}

$allowedPlugins = $composerJson['config']['allow-plugins'] ?? [];
if (!is_array($allowedPlugins)) {
    $allowedPlugins = [];
}

$pluginPackages = [];
foreach (['packages', 'packages-dev'] as $section) {
    foreach (($composerLock[$section] ?? []) as $package) {
        if (($package['type'] ?? '') === 'composer-plugin' && !empty($package['name'])) {
            $pluginPackages[] = (string)$package['name'];
        }
    }
}
$pluginPackages = array_values(array_unique($pluginPackages));
sort($pluginPackages);

$missing = [];
foreach ($pluginPackages as $pluginPackage) {
    if (($allowedPlugins[$pluginPackage] ?? false) !== true) {
        $missing[] = $pluginPackage;
    }
}

if ($missing !== []) {
    fwrite(STDERR, "Missing composer allow-plugins entries:\n");
    foreach ($missing as $pluginPackage) {
        fwrite(STDERR, " - {$pluginPackage}\n");
    }
    fwrite(STDERR, "\nAdd them to composer.json under config.allow-plugins with value true.\n");
    exit(1);
}

echo "Composer plugin allow-list check passed.\n";
if ($pluginPackages !== []) {
    echo "Detected composer plugins:\n";
    foreach ($pluginPackages as $pluginPackage) {
        echo " - {$pluginPackage}\n";
    }
}
