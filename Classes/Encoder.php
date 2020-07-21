<?php
/**
 * File : Classes\Encoder.php
 *
 * @author     Patrick Scott <lazeras@kaoses.com>
 * @copyright  1997-2020 lazeras@kaoses.com
 * @license    https://opensource.org/licenses/MIT  MIT License
 */


namespace Classes;

/**
 *
 * Object to handle the running the encode command on each file to be encoded
 *
 * PHP version 5.5
 * @package    Encoder
 * @version    1.0.0
 */
class Encoder
{

    protected $encodeLevel = 26;
    protected $ffmpegPath = '';
    protected $remove = false;

    /**
     * Holder for the Config Object
     * @var null
     */
    protected $config = null;

    /**
     * Construct new \Classes\\Encode
     *
     * @returns Encode();
     */
    function __construct($EncodeLevel, $ffmpegPath, $RemoveSourceFiles)
    {
        $this->encodeLevel = $EncodeLevel;
        $this->ffmpegPath = $ffmpegPath;
        $this->remove = $RemoveSourceFiles;
        return;
    }


    function processSourceList($sourceFileList, $FfprobePath, $renameIfExists, $deleteExisting)
    {

        foreach ($sourceFileList as $i => $path) {
            $videoDetails = new \Classes\VideoDetails($path->getSourceFilePath(), $FfprobePath);
            $videoAttributes = $videoDetails->getDetailsArray();
            if (isset($videoAttributes['width'])) {
                $width =   $videoAttributes['width'];
                $height =   $videoAttributes['height'];
                $bitrate = $videoAttributes['bitrate'];

                if ($width < 640) {
                    $width = $width;
                }elseif ($width < 720 && $width > 640) {
                    $width = 640;
                    $height = 480 ;
                }elseif ($width > 720 ) {
                    $width = 720;
                    $height = 576;
                }
                if ($bitrate > 5000) {
                    $bitrate = 3500;
                }elseif ($bitrate > 2500 && $bitrate < 5000) {
                    $bitrate = 2500;
                }elseif ($bitrate > 2000 && $bitrate < 2500) {
                    $bitrate = 2000;
                }
                $size = $path->getSourceSize();
                $humanSize = getHumanFileSize($size);
                /*
                 echo 'W:'.$width.'  H:'.$height.'  S:'.$size.'  Bitrate:'.$bitrate."\r\n";
                  echo "\r\n";
                */
                if ($path->getIsNested()) {
                    $path->makeDirectories();
                }
                if (file_exists($path->getDestinationPath())) {
                    if ($deleteExisting) {
                        unlink($path->getDestinationPath());
                    }elseif ($renameIfExists) {
                        $path->getNewName();
                    }
                }
                $output = $this->encodeVideo($path->getSourceFilePath(), $path->getDestinationPath(), $width, $height);
                echo '  Size:'.$humanSize."\r\n";
                echo "\r\n";
                $humanSize2 = getHumanFileSize($path->getDestinationSize());
                echo '  Size:'.$humanSize2."\r\n";
                echo "\r\n";

                echo 'Moved: '.is_file($path->getDestinationPath())."\r\n";
                echo 'Remove: '."$this->remove\r\n";
                if ($this->remove == true && is_file($path->getDestinationPath()) == true) {
                    $path->removeSource();
                }

            }
        }
    }

    /**
     * Method to Encode the specified video
     *
     * @param $inPutFile
     * @param $outPutFile
     * @param $width
     * @param $height
     * @param int $level
     * @return string|null
     */
    function encodeVideo($inPutFile, $outPutFile, $width, $height)
    {
        $cmd = sprintf('%s -i "%s" -vf scale=%s:%s -c:v libx264 -preset medium -crf %s -f matroska -c:s copy "%s"', $this->ffmpegPath, $inPutFile, $width,$height, $this->encodeLevel, $outPutFile);
        $output = shell_exec($cmd);
        echo $cmd."\r\n";
        return $output;
    }

}