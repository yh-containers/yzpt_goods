<?php
namespace app\api\controller;


class Merge extends Common
{
    private $root_path;
    private $resource_path ;
    private $resource_video_path ;
    private $resource_audio_path ;
    public function __construct()
    {
        parent::__construct();

        $this->root_path = \think\facade\Env::get('root_path');
        $this->resource_path = $this->root_path.'uploads'.DIRECTORY_SEPARATOR.'merge_video_audio'.DIRECTORY_SEPARATOR;
        $this->resource_video_path = $this->root_path.'uploads'.DIRECTORY_SEPARATOR.'merge_video_audio'.DIRECTORY_SEPARATOR.'video'.DIRECTORY_SEPARATOR;
        $this->resource_audio_path = $this->root_path.'uploads'.DIRECTORY_SEPARATOR.'merge_video_audio'.DIRECTORY_SEPARATOR.'audio'.DIRECTORY_SEPARATOR;
    }

    public function handle()
    {

        $ffmpeg = \FFMpeg\FFMpeg::create();
        // Open your video file
        $video = $ffmpeg->open( $this->resource_video_path.'m03.mp4' );

// Set an audio format
        $audio_format = new \FFMpeg\Format\Audio\Mp3();

// Extract the audio into a new file as mp3
        $video->save($audio_format, $this->resource_audio_path.'audio.mp3');

// Set the audio file
        $audio = $ffmpeg->open( $this->resource_audio_path.'audio.mp3' );
        $audio->
// Create the waveform
        $waveform = $audio->waveform();
        $waveform->save( $this->resource_path.'waveform.png' );

    }
}