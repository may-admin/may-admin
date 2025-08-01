<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\model\UploadFile;
use think\facade\View;

class Uploads extends BaseController
{
    //默认配置
    protected $config = [
        'upload_path' => 'uploads',  //上传目录
        'upload_size' => '2',        //上传文件大小限制【单位:MB】
        'image_format' => 'jpg,jpeg,png',     //图片上传格式限制
        'file_format' => 'doc,docx,xls,xlsx,ppt,zip,rar',     //文件上传格式限制
        'flash_format' => 'swf,flv',     //视频上传格式限制
        'media_format' => 'swf,flv,mp3,mp4,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb',     //音频上传格式限制
        'isprint' => '0',            //是否添加水印
        'print_image' => '',         //水印图片地址
        'print_position' => '9',     //水印位置
        'print_blur' => '100',       //水印透明度
        'file_url' => '',            //上传图片主机URL地址
    ];
    
    private $public_path;       //public目录
    
    private $up_type;           //上传类型
    private $file_move_path;    //上传文件移动服务器位置
    private $file_back_path;    //上传文件返回文件地址
    
    private $root_path;         //根目录路径
    private $root_url;          //根目录URL
    private $order;             //文件排序
    
    public function initialize()
    {
        $this->public_path = public_path();
        
        if (config('dbconfig.up')) {
            $this->config = array_merge($this->config, config('dbconfig.up'));      // 请新扩展配置文件
        }
        $this->up_type = input('param.dir');   //上传文件类型
        $this->file_move_path = $this->public_path.$this->config['upload_path'].DIRECTORY_SEPARATOR.$this->up_type;
        $this->file_back_path = DIRECTORY_SEPARATOR.$this->config['upload_path'].DIRECTORY_SEPARATOR.$this->up_type;
        
        $this->root_path = $this->public_path.$this->config['upload_path'].'/';
        $this->root_url = '/'.$this->config['upload_path'].'/';
        $this->order = empty(input('get.order')) ? 'name' : strtolower(input('get.order'));
        if (!file_exists($this->root_path)) {
            mkdir($this->root_path, 0755, true);
        }
    }
    
    /**
     * @Description: (文件上传方法)
     * @param object $imgFile 上传文件
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function upload()
    {
        if (!in_array($this->up_type, array('image', 'flash', 'media', 'file'))) {   //允许的文件目录名
            return json(['code' => 1, 'message' => '不允许目录', 'info' => '不允许目录']);
        }
        $file = request()->file('imgFile');
        if ($file){
            try {
                validate(['file' => [
                    'fileSize' => intval($this->config['upload_size'] * 1048576),
                    'fileExt' => $this->config[$this->up_type.'_format'],
                ]])->check(['file' => $file]);
                $savename = \think\facade\Filesystem::putFile($this->up_type, $file);
                $file_path = $this->root_url.$savename;
                $file_path = $this->config['file_url'].str_replace('\\', '/', $file_path);
                if(config('dbconfig.up.isprint') == '1' && !empty(config('dbconfig.up.print_image')) && $this->up_type == 'image'){
                    $image = \think\Image::open($this->public_path.$this->root_url.$savename);
                    $image->water($this->public_path.config('dbconfig.up.print_image'), config('dbconfig.up.print_position'), config('dbconfig.up.print_blur'))->save($this->public_path.$this->root_url.$savename);
                }
                $data = [];
                $data['format'] = $this->up_type;
                $data['name'] = $file->getOriginalName();
                $data['tag'] = input('param.tag');
                $data['url'] = $file_path;
                if($this->up_type == 'image'){
                    $file_info = getimagesize($file);
                    $data['width'] = $file_info[0];
                    $data['height'] = $file_info[1];
                }
                $data['filesize'] = filesize($file);
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $this->public_path.$file_path);
                finfo_close($finfo);
                $data['mime'] =  explode('/', $mimeType)[1];
                $data['sorts'] = 50;
                
                $uploadFileModel = new UploadFile();
                $uploadFileModel->save($data);
                
                return json(['code' => 0, 'link' => $file_path, 'message' => '上传成功', 'info' => '上传成功']);
            } catch (\think\exception\ValidateException $e) {
                return json(['code' => 1, 'message' => $e->getMessage(), 'info' => $e->getMessage()]);
            }
        }else{
            return json(['code' => 1, 'message' => '请选择文件', 'info' => '请选择文件']);
        }
    }
    
    /**
     * @Description: (上传头像并裁剪[200x200])
     * @param object $avatar_file 上传文件
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function cropper()
    {
        if ($this->up_type != 'avatar') {   //kindeditor允许的文件目录名
            $res = ['state' => 200, 'message' => '不允许目录','result' => ''];
            return json_encode($res);
        }
        $file = request()->file('avatar_file');
        $res = [
            'state'  => 200,
            'message' => '',
            'result' => ''
        ];
        if ($file){
            try {
                validate(['file' => [
                    'fileSize' => intval($this->config['upload_size'] * 1048576),
                    'fileExt' => $this->config['image_format'],
                ]])->check(['file' => $file]);
                $data = input('post.');
                $id = $data['id'];   //用户ID
                $avatar_data = json_decode(htmlspecialchars_decode($data['avatar_data']), true);
                
                $start_x = $avatar_data['x'];       //起始x
                $start_y = $avatar_data['y'];       //起始y
                $end_x   = $avatar_data['width'];   //结束x
                $end_y   = $avatar_data['height'];  //结束y
                $rotate  = $avatar_data['rotate'];  //旋转角度[有正负]
                
                $model = '\app\common\model\\'.$data['model'];
                $adminModel = new $model;
                $data = $adminModel->where('id', $id)->find();
                $oldAvatar = $data['avatar'];   //旧头像
                
                $image = \think\Image::open( $file );   //打开上传的图片
                $name_arr = explode('.', $_FILES['avatar_file']['name']);
                $extension = end($name_arr); //后缀
                $name = DIRECTORY_SEPARATOR.date('Ymd', time()).DIRECTORY_SEPARATOR.date('YmdHis', time())."_".rand(100000, 999999).'.'.$extension;
                $path = $this->file_move_path.$name;
                $back = str_replace('\\', '/', $this->file_back_path.$name);
                //生成目录
                if ( !file_exists($this->file_move_path.DIRECTORY_SEPARATOR.date('Ymd', time())) ){
                    mkdir($this->file_move_path.DIRECTORY_SEPARATOR.date('Ymd', time()), 0777, true);
                }
                $image->rotate($rotate)->crop($end_x, $end_y, $start_x, $start_y, 200, 200)->save($path, null, 100);
                if (file_exists($path)){    //检测图片是否保存成功
                    $data = ['avatar' => $back];
                    $where = ['id' => $id];
                    $result = $adminModel->where($where)->update($data);
                    if ($result){   //保存成功再删除旧头像
                        if ($oldAvatar != config('custom.default_avatar') && file_exists($this->public_path.$oldAvatar)){      //删除之前头像
                            unlink($this->public_path.$oldAvatar);
                        }
                        $res['message'] = 'success';
                        $res['result'] = $back;
                    }else{
                        $res['message'] = '头像数据保存失败';
                    }
                }else{
                    $res['message'] = '图片保存失败，请检查目录是否生成';
                }
            } catch (\think\exception\ValidateException $e) {
                $res['message'] = $e->getMessage();
            }
        }else{
            $res['message'] = '请选择图片';
        }
        return json($res);
    }
    
    /**
     * @Description: (ajaxManager文件管理方法)
     * @param string format 格式
     * @param string tag 标签
     * @param string back 回调按钮标识
     * @return string
     * @author 子青时节 <654108442@qq.com>
     */
    public function ajaxManager()
    {
        $format = input('param.format');
        $tag = input('param.tag');
        $uploadFileModel = new UploadFile();
        $page_param = page_param();
        $page_param['modal_ajax'] = true;
        $page_param['list_rows'] = 10;
        $dataList = $uploadFileModel->where([['format', '=', $format]])->order('sorts desc,id desc')->paginate($page_param, request()->isMobile());
        
        View::assign('dataList', $dataList);
        View::assign('back', input('param.back'));
        View::assign('tag', $tag);
        return View::fetch('upload_file/ajaxManager');
    }
    
    /**
     * @Description: (froala编辑器文件管理方法)
     * @param string format 格式
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function froalaManager()
    {
        $format = input('param.format');
        $uploadFileModel = new UploadFile();
        $dataList = $uploadFileModel->field('id,tag,url')->where([['format', '=', $format]])->order('sorts desc,id desc')->select();
        return json($dataList);
    }
    
    /**
     * @Description: (froala编辑器文件删除)
     * @param string data-id id
     * @param string data-url url
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function froalaDelete()
    {
        $id = input('param.data-id');
        $url = input('param.data-url');
        $uploadFileModel = new UploadFile();
        $data = $uploadFileModel->where([['id', '=', $id], ['url', '=', $url]])->find();
        if(!empty($data)){
            $data->delete();
            if (file_exists($this->public_path.$data['url'])) {
                unlink($this->public_path.$data['url']);
            }
            return ajax_return(0, lang('action_success'));
        }else{
            return ajax_return(1, lang('action_fail'));
        }
    }
}
