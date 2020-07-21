<?php
/**
 * File : Classes\paths.php
 *
 * @author     Patrick Scott <lazeras@kaoses.com>
 * @copyright  1997-2020 lazeras@kaoses.com
 * @license    https://opensource.org/licenses/MIT  MIT License
 */


namespace Classes;

/**
 *
 * Object to hold all information about the file
 *
 * PHP version 5.5
 * @package    Encoder
 * @version    1.0.0
 */
class paths
{

    protected $baseSourceFolder = '';
    protected $baseDestinationFolder = '';
    protected $fileName = '';
    protected $fileNameWithExt = '';
    protected $sourceFilePath = '';
    protected $destinationFilePath = '';
    protected $extension = '';
    protected $size = 0;
    protected $isNested = false;
    protected $nestedArray = array();
    protected $preserverNesting = true;

    /**
     * Construct new \Classes\paths
     *
     * @returns paths
     */
    function __construct($filePath, $sourceFolderBase, $DestinationFolderBase, $preserverNesting)
    {
        $this->sourceFilePath = $filePath;
        $this->baseSourceFolder = $sourceFolderBase;
        $this->baseDestinationFolder = $DestinationFolderBase;
        $this->preserverNesting = $preserverNesting;
        $this->parseFile();
        return;
    }

    static public function getSourceFilesList($sourcePath, $destinationPath, $preserverNesting, $minimumSize, $copySmallFiles, $mediaTypes)
    {
        $fileList = array();
        $scanList = scanDirectory($sourcePath);
        foreach ($scanList as $i => $filePath) {
            $ext = getFileExtension($filePath);
            if (isset($mediaTypes[$ext]) || $ext == '') {
                $path = new \Classes\paths($filePath, $sourcePath, $destinationPath, $preserverNesting);
                if ($path->getSourceSize() > $minimumSize) {
                    $fileList[] = $path;
                }elseif ($copySmallFiles) {
                    $path->copyFile();
                }
            }
        }
        return $fileList;
    }

    /**
     *  Internal Method to parse the filePath for the required information
     */
    private function parseFile()
    {
        $this->size = filesize($this->sourceFilePath);
        $this->extension = getFileExtension($this->sourceFilePath);
        if ($this->extension != "mp4" || $this->extension != "mkv" ) {
            $this->extension = "mp4";
        }
        $this->fileNameWithExt = str_replace($this->baseSourceFolder, '', $this->sourceFilePath);
        $this->fileName = str_replace('.'.$this->extension, '', $this->fileNameWithExt);
        $this->destinationFilePath = $this->baseDestinationFolder.$this->fileName.'.'.$this->extension;
        if (preg_match('^/^', $this->fileNameWithExt)) {
            $tmp = explode('/', $this->fileNameWithExt);
            $partsCount = count($tmp);
            if ($partsCount > 2) {
                $pathFolderStr = '';
                $j = 0;
                $this->isNested = true;
                foreach ($tmp as $i => $part) {
                    $maxCount = $partsCount - 1;
                    if ($part != '' && $j < $maxCount) {
                        $pathFolderStr .= $part.'/';
                        $this->nestedArray[] = $part;
                        if (!is_dir($this->baseDestinationFolder.$pathFolderStr)) {
                           // mkdir($this->baseDestinationFolder.$pathFolderStr);
                        }
                    }
                    $j++;
                }
                // Set the below values to allow for nested folders
                $this->fileNameWithExt = $tmp[$partsCount - 1];
                $this->fileName = str_replace('.'.$this->extension, '', $this->fileNameWithExt);
                if ($this->preserverNesting) {
                    $this->destinationFilePath = $this->baseDestinationFolder.$pathFolderStr.$this->fileName.'.'.$this->extension;
                }else {
                    $this->isNested = false;
                    $this->destinationFilePath = $this->baseDestinationFolder.$this->fileName.'.'.$this->extension;
                }

            }else {
                if ($this->fileNameWithExt[0] == '/') {
                    $this->fileNameWithExt = str_replace('/', '', $this->fileNameWithExt);
                    $this->fileName = str_replace('/', '', $this->fileName);
                    $this->destinationFilePath = $this->baseDestinationFolder.$this->fileName.'.'.$this->extension;
                }
            }
        }
    }

    function checkMovedFile($filePath)
    {
        $filesMoved = true;
        echo 'File to check: '.$filePath."\r\n";
        if (!is_file($filePath)) {
            $filesMoved = false;
        }
        return $filesMoved;
    }

    function checkMovedFiles($fileList, $destFolder)
    {
        $filesMoved = true;
        if (is_array($fileList)) {
            foreach ($fileList as $files) {
                foreach ($files as $item) {
                    if (!is_file($destFolder.'/'.$item[0]) && !is_dir($destFolder.'/'.$item[0])) {
                        $filesMoved = false;
                    }
                }
            }
        }else {
            echo 'File to check: '.$destFolder.$fileList."\r\n";
            if (!is_file($destFolder)) {
                $filesMoved = false;
            }
        }
        return $filesMoved;
    }

    function getIsNested()
    {
        return $this->isNested;
    }

    /**
     * Method To make the required Nested Directories
     */
    function makeDirectories()
    {
        $pathFolderStr = '';
        foreach ($this->nestedArray as $i => $part) {
            $pathFolderStr .= $part.'/';
            if (!is_dir($this->baseDestinationFolder.$pathFolderStr)) {
                mkdir($this->baseDestinationFolder.$pathFolderStr);
            }
        }
    }

    function removeSource()
    {
        if (count($this->nestedArray) > 0)
        {
            unlink($this->sourceFilePath);
            $folderNameStr = $this->baseSourceFolder.'/'.$this->nestedArray[0];
            $this->delTree($folderNameStr);
        }else {
            unlink($this->sourceFilePath);
        }
    }

    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function getNewName()
    {
        $i = 1;
        $newName = '';
        $found = false;
        while ($found == false) {
            if ($this->isNested) {
                $tmpName = $this->getNestedName().$this->fileName." (".$i.")";
            }else {
                $tmpName = $this->fileName." (".$i.")";
            }
            if (file_exists($this->baseDestinationFolder.$tmpName.'.'.$this->extension)) {
                $i++;
            }else {
                $newName = $tmpName;
                $found = true;
            }
        }
        $this->fileName = $newName;
        $this->fileNameWithExt = $this->fileName.'.'.$this->extension;
        $this->destinationFilePath = $this->baseDestinationFolder.$this->fileNameWithExt;
    }

    private function getNestedName()
    {
        $pathFolderStr = '';
        foreach ($this->nestedArray as $i => $part) {
            $pathFolderStr .= $part.'/';
        }
        return $pathFolderStr;
    }

    function copyFile()
    {
        if ($this->isNested) {
            $this->makeDirectories();
        }
        copy($this->sourceFilePath, $this->destinationFilePath);
        if (file_exists($this->destinationFilePath)) {
            $this->removeSource();
        }
    }

    /**
     * Method to get the source file size in bytes
     * @return  false|int
     */
    function getSourceSize()
    {
        return $this->size;
    }

    /**
     * Method to get the destination file size in bytes
     * @return false|int
     */
    function getDestinationSize()
    {
        return filesize($this->destinationFilePath);;
    }

    /**
     * Method to get the file name with out paths or extension
     * @return string
     */
    function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Method to get the file name with extension no path
     * @return string
     */
    function getFileNameWithExtension()
    {
        return $this->fileNameWithExt;
    }

    /**
     * Method to get the files source path
     * @return string
     */
    function getSourceFilePath()
    {
        return $this->sourceFilePath;
    }

    /**
     * Method to get the files Destination path
     * @return string
     */
    function getDestinationPath()
    {
        return $this->destinationFilePath;
    }
}