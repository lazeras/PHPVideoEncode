<?php
/**
 * File :encode.php
 *
 *  Encode all files from a directory to a destination directory.
 *
 * @author     Patrick Scott <lazeras@kaoses.com>
 * @copyright  1997-2020 lazeras@kaoses.com
 * @license    https://opensource.org/licenses/MIT  MIT License
 */

require_once("Classes/static.php");
echo "start video encoding \r\n";

$config = new \Classes\Config();
$sourceFileObjectsList = array();
$sourceFileList = \Classes\paths::getSourceFilesList($config->getSourcePathBase(), $config->getDestinationPathBase(), $config->getPreserveNesting(), $config->getMinimumSize(), $config->getCopySmallFiles(), $config->getMediaTypesArray());

$encoder = new \Classes\Encoder($config->getEncodeLevel(), $config->getFfmpegPath(), $config->removeSourceFile());
$encoder->processSourceList($sourceFileList, $config->getFfprobePath(), $config->renameIfExists(), $config->deleteIfExists());

echo "end video encoding \r\n";
