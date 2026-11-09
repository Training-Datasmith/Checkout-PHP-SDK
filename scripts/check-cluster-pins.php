<?php

declare(strict_types=1);

/**
 * Verify org-forked packages match composer-index SHAs and root inline aliases.
 */

$expected = [];

/**
 * @param array<string, array{reference: string, alias: string}> $pins
 * @param array<string, array{reference?: string, aliases?: list<string>}> $installedPhpVersions
 * @param array<string, string> $installedJsonReferences
 */
function check_cluster_pins_failures(
    array $pins,
    array $installedPhpVersions,
    array $installedJsonReferences
): array {
    $failures = [];

    foreach ($pins as $name => $want) {
        if (!isset($installedPhpVersions[$name])) {
            $failures[] = sprintf('check-cluster-pins: %s is not installed (installed.php).', $name);
            continue;
        }

        $pkg = $installedPhpVersions[$name];
        $aliases = $pkg['aliases'] ?? [];
        if (!in_array($want['alias'], $aliases, true)) {
            $failures[] = sprintf(
                'check-cluster-pins: %s alias mismatch (expected %s in aliases %s).',
                $name,
                $want['alias'],
                json_encode($aliases)
            );
        }

        $referencePhp = $pkg['reference'] ?? null;
        if (!is_string($referencePhp) || strlen($referencePhp) !== 40) {
            $failures[] = sprintf('check-cluster-pins: %s has no 40-char reference in installed.php.', $name);
        } elseif (!hash_equals(strtolower($want['reference']), strtolower($referencePhp))) {
            $failures[] = sprintf(
                'check-cluster-pins: %s installed.php reference mismatch (expected %s, got %s).',
                $name,
                strtolower($want['reference']),
                strtolower($referencePhp)
            );
        }

        if (!isset($installedJsonReferences[$name])) {
            $failures[] = sprintf('check-cluster-pins: %s is not installed (installed.json).', $name);
            continue;
        }

        $referenceJson = $installedJsonReferences[$name];
        if (!hash_equals(strtolower($want['reference']), strtolower($referenceJson))) {
            $failures[] = sprintf(
                'check-cluster-pins: %s installed.json source.reference mismatch (expected %s, got %s).',
                $name,
                strtolower($want['reference']),
                strtolower($referenceJson)
            );
        }
    }

    return $failures;
}

/**
 * @return array<string, string>
 */
function load_installed_json_references(string $installedJsonPath): array
{
    $raw = file_get_contents($installedJsonPath);
    if ($raw === false) {
        fwrite(STDERR, "check-cluster-pins: could not read {$installedJsonPath}.\n");
        exit(1);
    }

    try {
        /** @var array{packages?: list<array<string, mixed>>, 'packages-dev'?: list<array<string, mixed>>} $data */
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        fwrite(STDERR, "check-cluster-pins: invalid JSON in {$installedJsonPath}: {$exception->getMessage()}\n");
        exit(1);
    }

    $references = [];
    foreach (array_merge($data['packages'] ?? [], $data['packages-dev'] ?? []) as $package) {
        if (!is_array($package) || !isset($package['name'])) {
            continue;
        }
        $source = $package['source'] ?? null;
        if (!is_array($source) || !isset($source['reference']) || !is_string($source['reference'])) {
            continue;
        }
        $references[$package['name']] = $source['reference'];
    }

    return $references;
}

// In-memory wrong-SHA demo (does not touch vendor/).
$demoGoodSha = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
$demoBadSha = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
$demoWant = [
    'reference' => $demoGoodSha,
    'alias' => '1.2.3',
];
$demoPhpBase = [
    'reference' => $demoGoodSha,
    'aliases' => ['1.2.3'],
];
$demoJsonBase = [
    'demo/pkg' => $demoGoodSha,
];

$demoCaseA = check_cluster_pins_failures(
    ['demo/pkg' => $demoWant],
    ['demo/pkg' => array_merge($demoPhpBase, ['reference' => $demoBadSha])],
    $demoJsonBase
);
if ($demoCaseA === [] || !preg_grep('/installed\.php reference mismatch/', $demoCaseA)) {
    fwrite(STDERR, "check-cluster-pins: wrong-SHA demo case A did not fail on installed.php.\n");
    exit(2);
}

$demoCaseB = check_cluster_pins_failures(
    ['demo/pkg' => $demoWant],
    ['demo/pkg' => $demoPhpBase],
    ['demo/pkg' => $demoBadSha]
);
if ($demoCaseB === [] || !preg_grep('/installed\.json source\.reference mismatch/', $demoCaseB)) {
    fwrite(STDERR, "check-cluster-pins: wrong-SHA demo case B did not fail on installed.json.\n");
    exit(2);
}

$demoCaseC = check_cluster_pins_failures(
    ['demo/pkg' => $demoWant],
    ['demo/pkg' => $demoPhpBase],
    $demoJsonBase
);
if ($demoCaseC !== []) {
    fwrite(STDERR, "check-cluster-pins: wrong-SHA demo case C should pass but reported failures.\n");
    exit(2);
}

$root = dirname(__DIR__);
$installedPhp = $root . '/vendor/composer/installed.php';
$installedJson = $root . '/vendor/composer/installed.json';

if (!is_readable($installedPhp)) {
    fwrite(STDERR, "check-cluster-pins: vendor/composer/installed.php not found; run composer update first.\n");
    exit(1);
}

/** @var array{versions: array<string, array{reference?: string, aliases?: list<string>}>} $installed */
$installed = require $installedPhp;
$installedPhpVersions = $installed['versions'] ?? [];

if (!is_readable($installedJson)) {
    fwrite(STDERR, "check-cluster-pins: vendor/composer/installed.json not found; run composer update first.\n");
    exit(1);
}

$installedJsonReferences = load_installed_json_references($installedJson);

$failures = check_cluster_pins_failures($expected, $installedPhpVersions, $installedJsonReferences);
if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }
    exit(1);
}

if ($expected === []) {
    fwrite(STDOUT, "check-cluster-pins: no org packages pinned; index gaps remain on Packagist.\n");
    exit(0);
}

fwrite(STDOUT, 'check-cluster-pins: all cluster packages match expected SHAs and aliases.' . "\n");
exit(0);
