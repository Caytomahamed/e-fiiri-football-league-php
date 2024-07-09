<?php

spl_autoload_register('myautoloader');

function myautoloader($file)
{
    $paths = array(
        "/classes/",
    );

    $extention = ".php";

    foreach ($paths as $path) {
        $full = __DIR__ . $path . $file . $extention;

        if (file_exists($full)) {
            // echo "\n" . $full . "\n";
            include_once $full;
            return;
        }

    }

}
