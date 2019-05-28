#!/usr/bin/php
<?php

spl_autoload_register(function ($className) {
    $namespace= str_replace('\\','/', __NAMESPACE__);
    $className = str_replace('\\','/', $className);
    $class = ($namespace ? "$namespace/" : "") . "$className.php";
    require($class);
});

function _read ($prompt, $default = null) {
    echo $prompt . " ";
    if ($default) {
        echo "({$default}) ";
    }
    $line = trim(fgets(STDIN));
    if ($line || $default === null) {
        return $line;
    } else {
        return $default;
    }
}

while (true) {
    echo "\nParsers:
 1: North Carolina - Supreme Court
 2: New York - Court of Appeals
\n";
    $exit = false;
    $parser = null;

    try {
        switch (_read('Action?', 'exit')) {
            case '1':
                $parser = new Parsers\NorthCarolina();
                break;
            case '2':
                $parser = new Parsers\NewYork();
                break;
            case 'exit':
                $exit = true;
                break;
            default:
                break;
        }

        if ($exit || $parser === null) {
            break;
        } else {
            $start = time();
            echo "\n\nStarting scraping process...\n";
            $parser->parse();
            echo "\n\nSaving files locally; please wait...\n";
            $total = $parser->process();
            $end = time() - $start;
            echo "Saved files: {$total}\n";
            echo "Total time of the operation: {$end} ms\n";
        }
    } catch (\Exception $e) {
        die($e->getMessage());
    }
}

echo "Bye!\n";


