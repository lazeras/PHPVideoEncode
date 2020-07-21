<?php
/**
 * File : Classes\Config.php
 *
 * @author     Patrick Scott <lazeras@kaoses.com>
 * @copyright  1997-2020 lazeras@kaoses.com
 * @license    https://opensource.org/licenses/MIT  MIT License
 */


namespace Classes;

use PDO;

/**
 *
 * description
 *
 * PHP version 5.5
 * @package    Encoder
 * @version    1.0.0
 */
class Config
{

    public $config = array();
    protected $sourcePath = '';
    protected $encodedPath = '';
    protected $ffmpeg = 'ffmpeg.exe';
    protected $ffprobe = 'ffprobe.exe';
    protected $db = null;
    protected $remove = false;
    protected $debug = false;
    protected $cleanDest = false;
    protected $logFile = 'log';
    protected $encodeLevel = 26;
    protected $preserveNesting = true;
    protected $deleteExisting = false;
    protected $renameIfExists = false;
    protected $copySmallFiles = false;
    protected $minimumSize = 1000000;

    public $mediaTypes = array('avi' => 'avi', 'mpg' => 'mpg', 'mpeg' => 'mpeg',  'mp4' => 'mp4', 'webm' => 'webm', 'xxx' => 'xxx', 'flv' => 'flv', 'wmv' => 'wmv', 'mov' => 'mov', 'mkv' => 'mkv',);


    /**
     * Construct new \Classes\\Config.
     *
     * Config constructor.
     * @param string $iniFile
     */
    function __construct($iniFile = 'config.ini')
    {
        $this->buildConfig($iniFile);
        $this->getDB();

        ini_set('error_log', $this->logFile);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);
        return;
    }

    /**
     * Internal method for building config objects.
     * and setting base values for object parameters
     *
     * @param $iniFile
     */
    private function buildConfig($iniFile)
    {
        $configData = parse_ini_file ( $iniFile , true , INI_SCANNER_TYPED );

        foreach ($configData as $section => $item) {
            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    $this->parseLine($this->config[$section], $key, $value);
                }
            }else {
                $this->parseLine($this->config[$section], $key, $value);
            }
        }

        $this->sourcePath = $this->config['Directories']['source'];
        $this->encodedPath = $this->config['Directories']['encoded'];
        $this->remove = $this->config['Settings']['remove'];
        $this->debug = $this->config['Settings']['debug'];
        $this->cleanDest = $this->config['Settings']['cleanDest'];
        $this->encodeLevel = $this->config['Ffmpeg']['encodeLevel'];
        $this->preserveNesting = $this->config['Settings']['preserveNesting'];
        $this->deleteExisting = $this->config['Settings']['deleteExisting'];
        $this->renameIfExists = $this->config['Settings']['renameIfExists'];
        $this->minimumSize = $this->config['Settings']['minimumSize'];
        $this->copySmallFiles = $this->config['Settings']['copySmallFiles'];

        if ($this->config['Settings']['log'] != '') {
            $this->logFile = $this->config['Settings']['log'];
        }
        if ($this->config['Ffmpeg']['ffmpeg'] != '') {
            $this->ffmpeg = $this->config['Ffmpeg']['ffmpeg'];
        }
        if ($this->config['Ffmpeg']['ffprobe'] != '') {
            $this->ffmpeg = $this->config['Ffmpeg']['ffprobe'];
        }
    }

    /**
     * Internal method to parse a config line.
     *
     * @param $array
     * @param $key
     * @param $value
     */
    private function parseLine(&$array, $key, $value)
    {
        // split the folder name by . into an array
        $path = explode('.', $key);

        // set the pointer to the root of the array
        $root = &$array;

        // loop through the path parts until all parts have been removed (via array_shift below)
        while (count($path) > 1) {
            // extract and remove the first part of the path
            $branch = array_shift($path);
            // if the current path hasn't been created yet..
            if (!isset($root[$branch])) {
                // create it
                $root[$branch] = array();
            }
            // set the root to the current part of the path so we can append the next part directly
            $root = &$root[$branch];
        }
        // set the value of the path to an empty array as per OP request
        $root[$path[0]] = $value;
    }

    /**
     * Method to get a section of the config.
     *
     * @param $section
     * @return bool|mixed
     */
    function getSection($section)
    {
        if (isset($this->config[$section])) {
            return $this->config[$section];
        }
        return false;
    }

    /**
     * Method to log a message to a file.
     *
     * @param $message
     * @param bool $addNewLine
     */
    function logMessage($message, $addNewLine = false)
    {
        if ($addNewLine) {
            $message = $message."\r\n";
        }
        $fp = fopen( $this->logFile, "a+" );
        fwrite($fp, $message);
        fclose($fp);
    }

    /**
     * Method to the the config array.
     *
     * @return array
     */
    function getConfig()
    {
        return $this->config;
    }

    /**
     * Method to get the Database.
     *
     * @return PDO|null
     */
    function getDB()
    {
        if ($this->db == null) {
            $sqlDb = 'sqlite:'.$this->config['Database']['sqllite'];
            //$this->db = new PDO($sqlDb);
        }
        return $this->db;
    }

    /**
     * Method to get the source file path for objects that need to be encoded.
     *
     * @return string
     */
    function getSourcePathBase()
    {
        return $this->sourcePath;
    }

    /**
     * Method to get the folder path desination for encoded files.
     *
     * @return string
     */
    function getDestinationPathBase()
    {
        return $this->encodedPath;
    }

    /**
     * Method to get the full path to FFmpeg.
     * @return string
     */
    function getFfmpegPath()
    {
        return $this->ffmpeg;
    }

    /**
     * Method to get the full path to FFprobe.
     *
     * @return string
     */
    function getFfprobePath()
    {
        return $this->ffprobe;
    }

    /**
     * Method to get the desired encode level
     *
     * @return int
     */
    function getEncodeLevel()
    {
        return $this->encodeLevel;
    }

    /**
     * Method to determine if in debug mode.
     *
     * @return bool
     */
    function isDebug()
    {
        return $this->debug == true;
    }

    /**
     * If should remove source files after encode.
     *
     * @return bool
     */
    function removeSourceFile()
    {
        return $this->remove;
    }

    /**
     * Should destination directory be emptied first.
     *
     * @return bool
     */
    function cleanDestinationDirectory()
    {
        return $this->cleanDest;
    }

    /**
     * Method to get the full array of media extension types.
     *
     * @return array
     */
    function getMediaTypesArray()
    {
        return $this->mediaTypes;
    }

    /**
     * Method to get the preserver nesting value.
     *
     * @return bool
     */
    function getPreserveNesting()
    {
        return $this->preserveNesting;
    }

    function renameIfExists()
    {
        return $this->renameIfExists;
    }

    function deleteIfExists()
    {
        return $this->deleteExisting;
    }

    function getMinimumSize()
    {
        return $this->minimumSize;
    }

    function getCopySmallFiles()
    {
        return $this->copySmallFiles;
    }

    /**
     * Check if extension with out the .
     * is in hte mediaTypes array.
     *
     * @param $fileExtension
     * @return bool
     */
    function isValidExtension($fileExtension)
    {
        if (isset($this->mediaTypes[$fileExtension])) {
            return true;
        }
        return false;
    }
}