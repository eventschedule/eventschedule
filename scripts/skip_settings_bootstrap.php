<?php
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$root = $argv[1] ?? getcwd();
$dir = rtrim($root, DIRECTORY_SEPARATOR) . '/app/Providers';

if (!is_dir($dir)) {
    exit(0);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);

$search = "if (Schema::hasTable('settings')";

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $code = file_get_contents($path);

    if (strpos($code, $search) === false) {
        continue;
    }

    if (strpos($code, '$this->app->runningInConsole()') !== false) {
        continue;
    }

    $offset = 0;
    $updated = false;

    while (($pos = strpos($code, $search, $offset)) !== false) {
        $lineStart = strrpos(substr($code, 0, $pos), "\n");
        $lineStart = $lineStart === false ? 0 : $lineStart + 1;

        $indent = substr($code, $lineStart, $pos - $lineStart);

        if (trim($indent, " \t") !== '') {
            $offset = $pos + strlen($search);
            continue;
        }

        $bracePos = strpos($code, '{', $pos);
        if ($bracePos === false) {
            break;
        }

        $length = strlen($code);
        $depth = 0;
        $end = null;

        for ($i = $bracePos; $i < $length; $i++) {
            $char = $code[$i];

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        if ($end === null) {
            break;
        }

        $block = substr($code, $pos, $end - $pos + 1);
        $block = preg_replace('/^' . preg_quote($indent, '/') . '/m', '', $block);
        $blockIndented = preg_replace('/^/m', $indent . '    ', $block);

        $replacement = $indent . "if (\$this->app->runningInConsole()) {\n"
            . $indent . "    return;\n"
            . $indent . "}\n\n"
            . $indent . "try {\n"
            . $blockIndented . "\n"
            . $indent . "} catch (\\\\Throwable \$e) {\n"
            . $indent . "    return;\n"
            . $indent . "}";

        $code = substr($code, 0, $lineStart)
            . $replacement
            . substr($code, $end + 1);

        $updated = true;
        break;
    }

    if ($updated) {
        if ($code !== '' && substr($code, -1) !== "\n") {
            $code .= "\n";
        }

        file_put_contents($path, $code);
    }
}
