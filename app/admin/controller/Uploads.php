<?php
namespace app\admin\controller;

use app\BaseController;

class Uploads extends BaseController
{
    //默认配置
    protected $config = [
        'upload_path' => 'uploads',  //上传目录
        'upload_size' => '2',        //上传文件大小限制【单位:MB】
        'image_format' => 'jpg,jpeg,png',     //图片上传格式限制
        'file_format' => 'doc,docx,xls,xlsx,ppt,zip,rar',     //文件上传格式限制
        'flash_format' => 'swf,flv',     //视频上传格式限制
        'media_format' => 'swf,flv,mp3,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb',     //音频上传格式限制
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
        $this->up_type = input('get.dir');   //上传文件类型
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
     * kindeditor文件上传方法
     */
    public function upload()
    {
        if (!in_array($this->up_type, array('image', 'flash', 'media', 'file'))) {   //kindeditor允许的文件目录名
            return json(['error' => 1, 'message' => '不允许目录', 'info' => '不允许目录']);
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
                return json(['error' => 0, 'url' => $file_path, 'info' => '上传成功']);
            } catch (\think\exception\ValidateException $e) {
                return json(['error' => 1, 'message' => $e->getMessage(), 'info' => $e->getMessage()]);
            }
        }else{
            return json(['error' => 1, 'message' => '请选择文件', 'info' => '请选择文件']);
        }
    }
    
    /**
     * @Description: todo(上传头像并裁剪[ 200x200 ])
     * @author 苏晓信 <654108442@qq.com>
     * @date 2018年10月9日
     * @throws
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
     * @Description: todo(kindeditor文件管理方法)
     * @return @json
     * @author 苏晓信 <654108442@qq.com>
     * @date 2018年10月9日
     * @throws
     */
    public function manager()
    {
        $ext_arr = explode(',', $this->config['image_format']);
        if (!in_array($this->up_type, array('', 'image', 'flash', 'media', 'file'))) {   //kindeditor允许的文件目录名
            exit("Invalid Directory name.");
        }
        if ($this->up_type !== '') {
            $this->root_path .= $this->up_type.'/';
            $this->root_url .= $this->up_type . '/';
            if (!file_exists($this->root_path)) {
                mkdir($this->root_path);
            }
        }
        //根据path参数，设置各路径和URL
        if (empty(input('get.path'))) {
            $current_path = realpath($this->root_path).'/';
            $current_url = $this->root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($this->root_path).'/' . input('get.path');
            $current_url = $this->root_url . input('get.path');
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
     * 文件排序
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
    
    /**
     * kindeditor 文件和文件夹删除
     * @return @json
     */
    public function delete()
    {
        $data = input('post.');
        $res['msg'] = '删除失败';
        $res['code'] = 400;
        $res['data'] = [];
        if ($data['dir'] == 'dir'){
            deldir($this->public_path.$data['del_url'], 'y');   //删除目录
            if (!file_exists($this->public_path.$data['del_url'])){   //检测目录是否还存在
                $res['msg'] = '目录删除成功';
                $res['code'] = 200;
            }else {
                $res['msg'] = '目录删除失败';
                $res['code'] = 400;
            }
        }else if($data['dir'] == 'file'){
            unlink($this->public_path.$data['del_url']);   //删除文件
            if (!file_exists($this->public_path.$data['del_url'])){   //检测目录是否还存在
                $res['msg'] = '文件删除成功';
                $res['code'] = 200;
            }else {
                $res['msg'] = '文件删除失败';
                $res['code'] = 400;
            }
        }else{
        }
        return json($res);
    }
}
