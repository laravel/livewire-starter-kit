<?php

/**
 * This script adds the Livewire ⚡ emoji prefix to blade files.
 * Run after composer create-project to add emojis to filenames.
 */
$basePath = __DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';

$files = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (
        $file->isFile()
        && str_ends_with($file->getFilename(), '.blade.php')
        && str_contains(file_get_contents($file->getPathname()), 'use Livewire\\Component;')
    ) {
        $files[] = $file->getPathname();
    }
}

$searches = [];
$replacements = [];

foreach ($files as $file) {
    $directory = dirname($file);
    $filename = basename($file);
    $newFilename = '⚡'.$filename;
    $newPath = $directory.DIRECTORY_SEPARATOR.$newFilename;

    if (file_exists($file) && ! file_exists($newPath)) {
        rename($file, $newPath);
        $dirRelative = ltrim(str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $directory), DIRECTORY_SEPARATOR);
        echo "Renamed: {$dirRelative}".DIRECTORY_SEPARATOR."{{$filename} => {$newFilename}}".PHP_EOL;

        $searches[] = $dirRelative.DIRECTORY_SEPARATOR.$filename;
        $replacements[] = $dirRelative.DIRECTORY_SEPARATOR.$newFilename;
    }
}

if (PHP_OS_FAMILY === 'Windows') {
    $toForwardSlashes = fn (string $path): string => str_replace('\\', '/', $path);

    $searches = array_map($toForwardSlashes, $searches);
    $replacements = array_map($toForwardSlashes, $replacements);
}

$chiselPathsFile = __DIR__.DIRECTORY_SEPARATOR.'chisel-paths.php';

if ($searches !== [] && file_exists($chiselPathsFile)) {
    file_put_contents(
        $chiselPathsFile,
        str_replace($searches, $replacements, file_get_contents($chiselPathsFile)),
    );
}

$composerJson = json_decode(
    file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'composer.json'),
    true,
);
$composerJson['scripts']['post-create-project-cmd'] = array_values(
    array_filter(
        $composerJson['scripts']['post-create-project-cmd'],
        fn ($script) => $script !== '@php '.basename(__FILE__),
    ),
);

file_put_contents(
    __DIR__.DIRECTORY_SEPARATOR.'composer.json',
    json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL,
);

unlink(__FILE__);
