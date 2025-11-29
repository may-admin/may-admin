<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\model\UploadFile;
use think\facade\View;

class Uploads extends BaseController
{
    //默认配置
    protected $config = [
        'upload_path' => 'uploads',         //上传目录
        'upload_size' => '2',               //上传文件大小限制【单位:MB】
        'image_format' => 'jpg,jpeg,png',   //图片上传格式限制
        'file_format' => 'doc,docx,xls,xlsx,ppt,zip,rar',   //文件上传格式限制
        'flash_format' => 'swf,flv',        //视频上传格式限制
        'media_format' => 'swf,flv,mp3,mp4,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb',    //音频上传格式限制
        'isprint' => '0',                   //是否添加水印
        'print_image' => '',                //水印图片地址
        'print_position' => '9',            //水印位置
        'print_blur' => '100',              //水印透明度
        'file_url' => '',                   //上传图片主机URL地址
    ];
    
    public $allow_type = ['image', 'flash', 'media', 'file'];   //允许目录
    
    public $public_path;    //public目录[/www/wwwroot/web/public/]
    public $up_type;        //上传类型
    public $file_dir;       //文件服务器目录[/www/wwwroot/web/public/uploads/]
    public $file_link;      //文件链接访问目录[/uploads/]
    
    public $order;          //文件排序
    
    public function initialize()
    {
        $this->public_path = public_path();
        
        if (config('dbconfig.up')) {
            $this->config = array_merge($this->config, config('dbconfig.up'));  //扩展配置文件
        }
        $this->up_type = input('param.dir');    //上传文件类型
        $this->file_dir = $this->public_path.$this->config['upload_path'].DIRECTORY_SEPARATOR;
        $this->file_link = DIRECTORY_SEPARATOR.$this->config['upload_path'].DIRECTORY_SEPARATOR;
        
        $this->order = empty(input('get.order')) ? 'name' : strtolower(input('get.order'));
        if (!file_exists($this->file_dir)) {
            mkdir($this->file_dir, 0755, true);
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
        if (!in_array($this->up_type, $this->allow_type)) {
            return json(['code' => 1, 'message' => '不允许目录']);
        }
        $file = request()->file('imgFile');
        if ($file){
            try {
                validate(['file' => [
                    'fileSize' => intval($this->config['upload_size'] * 1048576),
                    'fileExt' => $this->config[$this->up_type.'_format'],
                ]])->check(['file' => $file]);
                $savename = \think\facade\Filesystem::putFile($this->up_type, $file);
                $file_path = $this->file_link.$savename;
                $file_path = $this->config['file_url'].str_replace('\\', '/', $file_path);
                if(config('dbconfig.up.isprint') == '1' && !empty(config('dbconfig.up.print_image')) && $this->up_type == 'image'){
                    $image = \think\Image::open($this->file_dir.$savename);
                    $image->water($this->public_path.config('dbconfig.up.print_image'), config('dbconfig.up.print_position'), config('dbconfig.up.print_blur'))->save($this->file_dir.$savename);
                }
                $data = [];
                $data['format'] = $this->up_type;
                $where[] = ['format', '=', $data['format']];
                $data['name'] = $file->getOriginalName();
                $where[] = ['name', '=', $data['name']];
                $data['tag'] = !empty(input('param.tag')) ? input('param.tag') : 'editor';
                $name_arr = explode('/', $savename);
                $data['dir'] = $name_arr[0];
                $data['date'] = $name_arr[1];
                $data['url'] = $file_path;
                if($this->up_type == 'image'){
                    $file_info = getimagesize($file);
                    $data['width'] = $file_info[0];
                    $where[] = ['width', '=', $data['width']];
                    $data['height'] = $file_info[1];
                    $where[] = ['height', '=', $data['height']];
                }
                $data['filesize'] = filesize($file);
                $where[] = ['filesize', '=', $data['filesize']];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $this->public_path.$file_path);
                finfo_close($finfo);
                $data['mime'] =  explode('/', $mimeType)[1];
                $where[] = ['mime', '=', $data['mime']];
                $data['sorts'] = 50;
                
                $uploadFileModel = new UploadFile();
                $file_data = $uploadFileModel->where($where)->find();
                if(!empty($file_data)){
                    unlink($this->file_dir.$savename);
                    return json(['code' => 0, 'link' => $file_data['url'], 'message' => '上传成功']);
                }else{
                    $uploadFileModel->save($data);
                    return json(['code' => 0, 'link' => $file_path, 'message' => '上传成功']);
                }
            } catch (\think\exception\ValidateException $e) {
                return json(['code' => 1, 'message' => $e->getMessage()]);
            }
        }else{
            return json(['code' => 1, 'message' => '请选择文件']);
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
        if ($this->up_type != 'avatar') {
            return json(['state' => 200, 'message' => '不允许目录','result' => '']);
        }
        $file = request()->file('avatar_file');
        $res = ['state'  => 200, 'message' => '', 'result' => ''];
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
                $name = 'avatar'.DIRECTORY_SEPARATOR.date('Ymd', time()).DIRECTORY_SEPARATOR.date('YmdHis', time())."_".rand(100000, 999999).'.'.$extension;
                $path = $this->file_dir.$name;
                $back = str_replace('\\', '/', $this->file_link.$name);
                //生成目录
                if ( !file_exists($this->file_dir.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR.date('Ymd', time())) ){
                    mkdir($this->file_dir.DIRECTORY_SEPARATOR.'avatar'.DIRECTORY_SEPARATOR.date('Ymd', time()), 0777, true);
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
    public function froalaeditorManager()
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
    public function froalaeditorDelete()
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
    
    /**
     * @Description: (kind编辑器文件管理方法)
     * @param string format 格式
     * @return @json
     * @author 子青时节 <654108442@qq.com>
     */
    public function kindeditorManager()
    {
        $ext_arr = explode(',', $this->config['image_format']);
        if (!in_array($this->up_type, array('', 'image', 'flash', 'media', 'file'))) {   //kindeditor允许的文件目录名
            exit("Invalid Directory name.");
        }
        if ($this->up_type !== '') {
            $this->file_dir .= $this->up_type.'/';
            $this->file_link .= $this->up_type . '/';
            if (!file_exists($this->file_dir)) {
                mkdir($this->file_dir);
            }
        }
        //根据path参数，设置各路径和URL
        if (empty(input('get.path'))) {
            $current_path = realpath($this->file_dir).'/';
            $current_url = $this->file_link;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($this->file_dir).'/' . input('get.path');
            $current_url = $this->file_link . input('get.path');
            $current_dir_path = input('get.path');
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        
        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            exit('Access is not allowed.');
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            exit('Parameter is not valid.');
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            exit('Directory does not exist.');
        }
        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename[0] == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        
        $file_list = $this->_order_func($file_list, $this->order);
        
        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;
        
        //输出JSON字符串
        return json($result);
    }
    
    /**
     * @Description: (kind编辑器文件排序)
     * @param Array $file_list      排序数组
     * @param String $sort_key      以什么字段排序
     * @param string $sort          排序方式【正序|倒序】SORT_DESC|SORT_DESC
     * @return array
     */
    public function _order_func(&$file_list, $sort_key, $sort = SORT_ASC)
    {
        if ($sort_key == 'type'){
            $sort_key = 'filetype';
        }else if ($sort_key == 'size'){
            $sort_key = 'filesize';
        }else{   //name
            $sort_key = 'filename';
        }
        if(is_array($file_list)){
            foreach ($file_list as $key => $row_array){
                $num[$key] = $row_array[$sort_key];
            }
        }else{
            return false;
        }
        //对多个数组或多维数组进行排序
        array_multisort($num, $sort, $file_list);
        return $file_list;
    }
}
