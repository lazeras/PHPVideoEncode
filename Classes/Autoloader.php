<?php
/**
 * File :Autoloader.php
 *
 * @author     Patrick Scott <lazeras@kaoses.com>
 * @copyright  1997-2020 lazeras@kaoses.com
 * @license    https://opensource.org/licenses/MIT  MIT License
 */


/**
 *
 * description
 *
 * PHP version 5.5
 * @package    PHPVideo
 * @version    1.0.0
 */
class Autoloader
{

    /**
     * Construct new Autoloader
     *
     * @returns Autoloader
     */
    function __construct()
    {
        return;
    }

    /**
     * Method to auto load class files
     * @param $className
     * @return bool
     */
    static public function loader($className) {
        $filename = "Classes/" . str_replace("\\", '/', $className) . ".php";
        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}
spl_autoload_register('Autoloader::loader');