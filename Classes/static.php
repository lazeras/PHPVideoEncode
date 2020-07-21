<?php
//require_once("Autoloader.php");


/**
 * Method to auto load class files
 * @param $className
 * @return bool
 */
function loader($className) {
    $filename = "Classes/" . str_replace("\\", '/', $className) . ".php";
    if (file_exists($filename)) {
        include($filename);
        if (class_exists($className)) {
            return TRUE;
        }
    }
    return FALSE;
}
//spl_autoload_register('loader');
spl_autoload_register();

/**
 * Function to sacan a directory and return all files
 * found in the path as an array
 *
 * @param $dir
 * @param string $ext
 * @param bool $recurse
 * @return array
 */
function scanDirectory($dir, $ext = '*', $recurse = true)
{
    $files = array();
    if ($handle = opendir($dir)) {
        while(false !== ($file = readdir($handle))) {
            if ($file == '.' || $file == '..' || $file == 'CVS' || preg_match('/^\./', $file)
            ) {
                continue;
            }
            /*
             * Call ourself recursively to scan a subdirectory
             */
            if (is_dir($dir . '/' . $file)) {
                if ($recurse == true) {
                    $files = array_merge($files, scanDirectory($dir . '/' . $file, $ext));
                }
            } else {
                if ($ext == getFileExtension($file) || $ext == '*') {
                    $files[] = $dir . '/' . $file;
                }
            }
        }
        closedir($handle);
    }

    return $files;
}

/**
 * Return the extension of a file
 *
 * @param string $file the file name to examine
 *
 * @return string the file's extension
 */
function getFileExtension($file)
{
    $temp_vals = explode('.', $file);
    $file_ext = strtolower(rtrim(array_pop($temp_vals)));
    unset ($temp_vals);

    return ($file_ext);
}

/**
 * Function to get the human readable file size
 *
 * @param $bytes
 * @param int $decimals
 * @return string
 */
function getHumanFileSize($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

