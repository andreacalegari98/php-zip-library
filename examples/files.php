<?php
    $files_path = "files";
    $results_path = "results";
    $result_name = date("YmdHis") . ".zip";

    // Clean results folder
    $files = glob("$results_path/*");
    foreach ($files as $key => $file) {
        unlink($file);
    }

    // Include zip library
    include_once("../Zip.php");

    // Init Class with zip filepath
    $zip = new Zip("$results_path/$result_name");

    // Get folder files
    $files = glob("$files_path/*");

    foreach ($files as $key => $file) {
        // Add entire /files folder
        $zip->add($file);
    }

    // Generate zip file
    $zip->generate();

    // Unzip file into results folder
    $zip->unzip($results_path);
?>