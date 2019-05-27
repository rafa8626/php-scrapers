<?php

spl_autoload_register(function ($className) {
    $namespace= str_replace('\\','/', __NAMESPACE__);
    $className = str_replace('\\','/', $className);
    $class = ($namespace ? "$namespace/" : "") . "$className.php";
    require($class);
});


//$parser = new Parsers\NorthCarolina();
//$parser->parse();
//$parser->process();

$parser = new Parsers\NewYork();
$parser->parse();