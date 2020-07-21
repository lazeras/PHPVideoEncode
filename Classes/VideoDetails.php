<?php
/**
 * File : Classes\VideoDetails.php
 *
 * @author     Patrick Scott <lazeras@kaoses.com>
 * @copyright  1997-2020 lazeras@kaoses.com
 * @license    https://opensource.org/licenses/MIT  MIT License
 */


namespace Classes;

/**
 *
 * Object to get and hold information about the video files details.
 *
 * PHP version 5.5
 * @package    Encoder
 * @version    1.0.0
 */
class VideoDetails
{

    protected $file = '';
    protected $ffprobe = '';
    protected $detailList = array();

    /**
     * StdClass object
     * @var null
     */
    protected $info = null;

    /**
     * Construct new \Classes\VideoDetails
     *
     * @returns VideoDetails
     */
    function __construct($filePath, $ffprobe)
    {
        $this->file = $filePath;
        $this->ffprobe = $ffprobe;
        $this->buildVideoAttributes();
        return;
    }


    /**
     * Method to get the video file attributes
     *
     * @param $video
     * @return array
     */
    private function buildVideoAttributes()
    {
        $cmd = sprintf('%s -v error -show_entries stream=index,codec_name,codec_type "%s" ', $this->ffprobe, $this->file);
        echo $cmd."\r\n";
        $cmd = sprintf('%s -i "%s" -v quiet -print_format json -show_format -show_streams -hide_banner > temp_file', $this->ffprobe, $this->file);
        exec($cmd, $output, $res);
        $info = json_decode(file_get_contents("temp_file"));

        if (isset($info->format->filename)) {
            $this->detailList['filename'] = $info->format->filename;
        }
        if (isset($info->format->nb_streams)) {
            $this->detailList['nb_streams'] = $info->format->nb_streams;
        }
        if (isset($info->format->format_name)) {
            $this->detailList['format_name'] = $info->format->format_name;
        }
        if (isset($info->format->format_long_name)) {
            $this->detailList['format_long_name'] = $info->format->format_long_name;
        }
        if (isset($info->format->duration)) {
            $this->detailList['duration'] = $info->format->duration;
            $this->detailList['runTime'] = date('H:i:s', mktime(0, 0, $info->format->duration));
        }
        if (isset($info->format->size)) {
            $this->detailList['size'] = $info->format->size;
        }
        if (isset($info->format->bit_rate)) {
            $this->detailList['bit_rate'] = $info->format->bit_rate;
            $this->detailList['bitrate'] = number_format($info->format->bit_rate / 1000 , 0 ,'', '');
        }
        /*
        $this->detailList['filename'] = $info->format->filename;
        $this->detailList['nb_streams'] = $info->format->nb_streams;
        $this->detailList['format_name'] = $info->format->format_name;
        $this->detailList['format_long_name'] = $info->format->format_long_name;
        $this->detailList['duration'] = $info->format->duration;
        $this->detailList['runTime'] = date('H:i:s', mktime(0, 0, $info->format->duration));
        $this->detailList['size'] = $info->format->size;
        $this->detailList['bit_rate'] = $info->format->bit_rate;
        $this->detailList['bitrate'] = number_format($info->format->bit_rate / 1000 , 0 ,'', '');
        */
        if (isset($info->streams) && is_array($info->streams)) {
            foreach ($info->streams as $stream) {
                $this->detailList[$stream->codec_type][$stream->index]['index'] = $stream->index; //**subTitle
                $this->detailList[$stream->codec_type][$stream->index]['codec_name'] = $stream->codec_name;//h624 , dts, hdmv_pgs_subtitle  **subTitle
                $this->detailList['videoCodec'] = $stream->codec_name;//h624 , dts, hdmv_pgs_subtitle  **subTitle
                $this->detailList[$stream->codec_type][$stream->index]['codec_long_name'] = $stream->codec_long_name;//**subTitle
                $this->detailList[$stream->codec_type][$stream->index]['profile'] = $stream->profile;
                $this->detailList[$stream->codec_type][$stream->index]['codec_tag_string'] = $stream->codec_tag_string;
                $this->detailList[$stream->codec_type][$stream->index]['codec_tag'] = $stream->codec_tag;
                $this->detailList[$stream->codec_type][$stream->index]['codec_type'] = $stream->codec_type; // video **subTitle
                $this->detailList[$stream->codec_type][$stream->index]['language'] = $stream->tags->language;// eng , spa video&audio **subTitle
                if ($stream->codec_type == 'video') {
                    $this->detailList[$stream->codec_type][$stream->index]['width'] = $stream->width;
                    $this->detailList[$stream->codec_type][$stream->index]['height'] = $stream->height;
                    $this->detailList['width'] = $stream->width;
                    $this->detailList['height'] = $stream->height;
                    $this->detailList[$stream->codec_type][$stream->index]['coded_width'] = $stream->coded_width;
                    $this->detailList[$stream->codec_type][$stream->index]['coded_height'] = $stream->coded_height;
                    $this->detailList[$stream->codec_type][$stream->index]['sample_aspect_ratio'] = $stream->sample_aspect_ratio;
                    $this->detailList[$stream->codec_type][$stream->index]['display_aspect_ratio'] = $stream->display_aspect_ratio;
                    $this->detailList['aspect'] = $stream->display_aspect_ratio;
                    $this->detailList[$stream->codec_type][$stream->index]['pix_fmt'] = $stream->pix_fmt;
                } elseif ($stream->codec_type == 'audio') {
                    $this->detailList['AudioCodec'] = $stream->codec_name; // audio only = 48000
                    $this->detailList[$stream->codec_type][$stream->index]['sample_rate'] = $stream->sample_rate; // audio only = 48000
                    $this->detailList['AudioRate'] = $stream->sample_rate; // audio only = 48000
                    $this->detailList[$stream->codec_type][$stream->index]['channel_layout'] = $stream->channel_layout; // audio only = 5.1
                }
            }
        }
    }

    /**
     * Method to get the Info object
     *
     * @return StdClass
     */
    function getInfoObject()
    {
        return $this->info;
    }

    /**
     * Method to get the details array
     *
     * @return array
     */
    function getDetailsArray()
    {
        return $this->detailList;
    }


}