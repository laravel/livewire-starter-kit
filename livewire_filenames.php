<?php

/**
 * This script adds the Livewire ⚡ emoji prefix to blade files.
 * Run after composer create-project to add emojis to filenames.
 */

$basePath = __DIR__.'/resources/views/pages/settings';

$files = [
    'appearance.blade.php',
    'delete-user-form.blade.php',
    'password.blade.php',
    'profile.blade.php',
    'two-factor.blade.php',
    'two-factor/recovery-codes.blade.php',
];

foreach ($files as $file) {
    $currentPath = $basePath.'/'.$file;
    $directory = dirname($currentPath);
    $filename = basename($file);
    $newFilename = '⚡'.$filename;
    $newPath = $directory.'/'.$newFilename;

    if (file_exists($currentPath) && ! file_exists($newPath)) {
        rename($currentPath, $newPath);
        $dirRelative = ltrim(str_replace(__DIR__.'/', '', $directory), '/');
        echo "Renamed: {$dirRelative}/{{$filename} => {$newFilename}}\n";
    }
}

unlink(__FILE__);
