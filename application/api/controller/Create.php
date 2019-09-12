<?php
namespace app\api\controller;

class Create extends Common {
	/**
	 * 视频上传 优化第一步
	 */
	public function upload () {
		if ($this->request->isPost()) {
            
		    //屏蔽
            return ['code'=>0,'msg'=>'接口已被屏蔽'];
		    
			$video = request()->file('video');
			if (empty($video)) return ['code'=>1,'msg'=>'请先选择视频'];
			$info = $video->validate(['ext'=>'mp4'])->rule('uniqid')->move('../uploads/video/');
			if ($info) {
				//后期有需要可以直接尝试在服务器上操作视频生成一张张图片取代前端的canvas画图
				//$result = (shell_exec('ffmpeg -i ../uploads/video/' . $info->getSaveName() . ' -r 1 ../uploads/%d.jpeg && echo \'success\''));
				return ['code'=>0,'msg'=>'上传成功','video'=>'http://www.chinacarechain.com/uploads/video/' . $info->getSaveName()];
			} else {
				return ['code'=>1,'msg'=>$video->getError()];
			}
		}
		return $this->_empty();
	}
	
	/**
	 * 视频裁剪 优化第二步
	 */
	public function clip () {
		if ($this->request->isPost()) {

            //屏蔽
            return ['code'=>0,'msg'=>'接口已被屏蔽'];
            
			$video = str_replace('http://www.chinacarechain.com/uploads/', '../uploads/', input('video', ''));
			$save = '';

			$duration = input('duration', 0, 'intval');
			$start = input('start', 0, 'intval');
			$end = input('end', 0, 'intval');
			$time = $duration - $start - $end;
			if ($duration == 0 || $time == 0) return ['code'=>1,'msg'=>'视频参数错误'];
			
			if ($time == $duration) {
				$save = $video;
			} else {
				$cuter = $time == $duration ? '' : '-ss ' . $start . ' -t ' . $time;
				$save = '../uploads/video/' . uniqid() . '.mp4';
				$res = shell_exec('ffmpeg ' . $cuter . ' -i ' . $video . ' -vcodec copy -acodec copy -y ' . $save . ' && echo \'success\'');
				if (empty($res)) return ['code'=>1,'msg'=>'视频处理失败'];
			}
			if ($save != $video) @unlink($video); // 如果生成了新的视频删除原来的视频
			return ['code'=>0,'msg'=>'success','video'=>str_replace('../uploads/', 'http://www.chinacarechain.com/uploads/', $save)];
		}
		return $this->_empty();
	}
	
	/**
	 * 添加背景音乐 优化第三步
	 */
	public function music () {
		if ($this->request->isPost()) {

            //屏蔽
            return ['code'=>0,'msg'=>'接口已被屏蔽'];
            $audio = input('audio');
            if(!empty($audio)){
                $audio_arr = explode('/',$audio);
                $music_name = end($audio_arr);
                $path = '/uploads/music/'.$music_name;
                $audio = '..'.$path;
                $audio_ab_path = \think\facade\Env::get('root_path').$path;
                if(!file_exists($audio_ab_path)){
                    unset($audio);//销毁实例
                }
            }

//			$audio = str_replace('http://www.chinacarechain.com/uploads/', '../uploads/', input('audio', ''));
			$video = str_replace('http://www.chinacarechain.com/uploads/', '../uploads/', input('video', ''));
			$save = '';
			if (empty($audio)) {
				$save = $video;
			} else {
				$mute = input('mute', 0);
				$time = input('time', 0, 'intval');
				if ($time == 0) return ['code'=>1,'msg'=>'视频参数错误'];
				
				$save = '../uploads/video/' . uniqid() . '.mp4';
				$merge = $mute ? '' : ' -filter_complex amix=inputs=2';
				$res = shell_exec('ffmpeg -i ' . $audio . ' -i ' . $video . $merge . ' -t ' . $time . ' -y ' . $save . ' && echo \'success\'');
				if (empty($res)) return ['code'=>1,'msg'=>'音频处理失败'];
			}
			if ($save != $video) @unlink($video); // 如果生成了新的视频删除原来的视频
			return ['code'=>0,'msg'=>'success','video'=>str_replace('../uploads/', 'http://www.chinacarechain.com/uploads/', $save)];
		}
		return $this->_empty();
	}
	
	/**
	 * 背景音乐分类列表
	 */
	public function musicCate () {
		$cates = [
			['id'=>1,'title'=>'热门'],
//			['id'=>1,'title'=>'流行'],
//			['id'=>1,'title'=>'轻快'],
//			['id'=>1,'title'=>'影视']
		];
		
		return ['code'=>0,'msg'=>'success','cates'=>$cates];
	}
	
	/**
	 * 背景音乐列表
	 */
	public function musicList ($cid = 0) {
        $where = [];
        $where[] = ['status','=',1];
        $list =[];
        $info=\app\common\model\Music::where($where)
            ->order('sort asc')->paginate(100)
            ->each(function($item,$index)use(&$list){
                array_push($list,[
                    'id'=>$item['id'],
                    'title'=>$item['name'],
                    'singer'=>'未知',
                    'cover'=>'http://www.chinacarechain.com/uploads/music/001.jpg',
                    'url'=>$item['file'],
                ]);
            });
//        $data = ['list'=>$list,'total'=>$info->total()];
//		$lists = [
//			['id'=>1,'title'=>'测试音乐','singer'=>'未知','cover'=>'http://www.chinacarechain.com/uploads/music/001.jpg','url'=>'http://www.chinacarechain.com/uploads/music/001.mp3']
//		];
		
		return ['code'=>0,'msg'=>'success','lists'=>$list,'total'=>$info->total()];
	}
}