-- ----------------------------
-- Table structure for `may_admin`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `may_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `username` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',
  `name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '昵称',
  `email` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '手机',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别',
  `qq` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'QQ',
  `avatar` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `logins` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `reg_ip` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1' COMMENT '注册IP',
  `last_time` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '127.0.0.1' COMMENT '最后登录IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `name` (`name`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员';

-- ----------------------------
-- Records of may_admin
-- ----------------------------
INSERT INTO `may_admin` SELECT NULL,'zqsj','76629f37081d0f343ab6e01248ac6693','www.sxxblog.com','654108442@qq.com','','1','','/static/global/common/img/avatar.png','0','127.0.0.1','1699178086','127.0.0.1','1','1699178086','1699178086' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_admin` WHERE username='zqsj');


-- ----------------------------
-- Table structure for `may_auth_rule`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `may_auth_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` bigint(20) unsigned NOT NULL COMMENT '父id',
  `module` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin' COMMENT '权限节点所属模块',
  `level` tinyint(1) NOT NULL COMMENT '1-项目;2-模块;3-操作',
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则唯一标识',
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则中文名称',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `ismenu` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否导航',
  `condition` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `icon` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '节点图标',
  `sorts` bigint(20) DEFAULT '50' COMMENT '排序',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE,
  KEY `module` (`module`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限节点';

-- ----------------------------
-- Records of may_auth_rule
-- ----------------------------
INSERT INTO `may_auth_rule` SELECT NULL,'0','admin','1','Index/index','后台首页','1','1','1',null,'fa-solid fa-home','999','1699178086','1746774507' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Index/index');
INSERT INTO `may_auth_rule` SELECT NULL,'0','admin','1','leftSystem','系统管理','1','1','1',null,'fa-solid fa-gear','100','1699178086','1746774497' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='leftSystem');
INSERT INTO `may_auth_rule` SELECT NULL,'0','admin','1','leftAdmin','管理员管理','1','1','1',null,'fa-solid fa-users','300','1699178086','1727623868' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='leftAdmin');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Index/index'),'admin','2','Index/cleanCache','清除缓存','1','1','0',null,'','48','1699178086','1721356174' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Index/cleanCache');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Index/index'),'admin','2','Admin/editSelf','个人资料','1','1','0',null,'','49','1699178086','1746774636' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Admin/editSelf');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='leftSystem'),'admin','2','Config/index','系统配置字段','1','1','1',null,'fa-solid fa-gears','47','1699178086','1746774553' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/index');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='leftSystem'),'admin','2','Config/sysMenu','系统配置','1','1','1',null,'fa-solid fa-gear','48','1699178086','1731396901' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/sysMenu');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='leftSystem'),'admin','2','UploadFile/index','附件列表','1','1','1',null,'fa-regular fa-file','49','1728830498','1746774549' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='UploadFile/index');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='leftAdmin'),'admin','2','AuthRule/index','节点列表','1','1','1',null,'fa-solid fa-pen-ruler','48','1699178086','1727624017' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthRule/index');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='leftAdmin'),'admin','2','Admin/index','管理员列表','1','1','1',null,'fa-solid fa-user-tie','49','1699178086','1746774533' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Admin/index');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='leftAdmin'),'admin','2','AuthGroup/index','角色列表','1','1','1',null,'fa-solid fa-address-card','47','1699178086','1746774604' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthGroup/index');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Config/index'),'admin','3','Config/create','新增','1','1','0',null,'','49','1699178086','1746774674' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/create');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Config/index'),'admin','3','Config/edit','编辑','1','1','0',null,'','48','1699178086','1746774673' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/edit');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Config/index'),'admin','3','Config/delete','删除','1','1','0',null,'','47','1699178086','1746774671' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/delete');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Config/index'),'admin','3','Config/save','保存','1','1','0',null,'','46','1699178086','1746774669' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/save');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='AuthRule/index'),'admin','3','AuthRule/create','新增','1','1','0',null,'','49','1699178086','1746774655' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthRule/create');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='AuthRule/index'),'admin','3','AuthRule/edit','编辑','1','1','0',null,'','48','1699178086','1699178454' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthRule/edit');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='AuthRule/index'),'admin','3','AuthRule/delete','删除','1','1','0',null,'','47','1699178086','1746774653' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthRule/delete');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Admin/index'),'admin','3','Admin/create','新增','1','1','0',null,'','49','1699178086','1746774643' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Admin/create');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Admin/index'),'admin','3','Admin/edit','编辑','1','1','0',null,'','48','1699178086','1746774641' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Admin/edit');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Admin/index'),'admin','3','Admin/delete','删除','1','1','0',null,'','47','1699178086','1699178444' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Admin/delete');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Admin/index'),'admin','3','Admin/authGroup','授权角色','1','1','0',null,'','46','1699178086','1746774638' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Admin/authGroup');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='AuthGroup/index'),'admin','3','AuthGroup/create','新增','1','1','0',null,'','49','1699178086','1746774663' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthGroup/create');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='AuthGroup/index'),'admin','3','AuthGroup/edit','编辑','1','1','0',null,'','48','1699178086','1699178469' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthGroup/edit');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='AuthGroup/index'),'admin','3','AuthGroup/delete','删除','1','1','0',null,'','47','1699178086','1746774661' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='AuthGroup/delete');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Config/sysMenu'),'admin','3','Config/sys','后台配置','1','1','0',null,'','49','1699178086','1746774569' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/sys');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='Config/sysMenu'),'admin','3','Config/up','上传配置','1','1','0',null,'','48','1699178086','1746774571' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='Config/up');
INSERT INTO `may_auth_rule` SELECT NULL,(SELECT id FROM `may_auth_rule` WHERE name='UploadFile/index'),'admin','3','UploadFile/delete','删除','1','1','0',null,null,'49','1728830758','1746775263' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_rule` WHERE module='admin' AND name='UploadFile/delete');

-- ----------------------------
-- Table structure for `may_auth_group`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `may_auth_group` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `module` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin' COMMENT '所属模块',
  `level` bigint(20) NOT NULL COMMENT '角色等级',
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户组中文名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：为1正常，为0禁用',
  `rules` text COLLATE utf8mb4_unicode_ci COMMENT '用户组拥有的规则id， 多个规则","隔开',
  `notation` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '组别描述',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限组';

-- ----------------------------
-- Records of may_auth_group
-- ----------------------------
SET @all_rule_super_admin_ids := (SELECT GROUP_CONCAT(`id` SEPARATOR ',') FROM `may_auth_rule`);
INSERT INTO `may_auth_group` SELECT NULL,'admin','49','超级管理员','1',@all_rule_super_admin_ids,'该角色不可删除，需要分配全部权限节点','1699178086','1699178086' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_group` WHERE `title`='超级管理员');
INSERT INTO `may_auth_group` SELECT NULL,'admin','48','普通管理员','1','','需要分配相应权限进行操作','1699178086','1699178086' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_group` WHERE title='普通管理员');

-- ----------------------------
-- Table structure for `may_auth_group_access`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `may_auth_group_access` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `module` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin' COMMENT '所属模块',
  `uid` bigint(20) unsigned NOT NULL COMMENT '用户id',
  `group_id` bigint(20) unsigned NOT NULL COMMENT '用户组id',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`) USING BTREE,
  KEY `group_id` (`group_id`) USING BTREE,
  KEY `module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户权限组';

-- ----------------------------
-- Records of may_auth_group_access
-- ----------------------------
INSERT INTO `may_auth_group_access` SELECT NULL,'admin',(SELECT id FROM `may_admin` WHERE `username`='zqsj'),(SELECT id FROM `may_auth_group` WHERE `title`='超级管理员'),'1699178086','1699178086' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_auth_group_access` WHERE uid=(SELECT id FROM `may_admin` WHERE `username`='zqsj') AND group_id=(SELECT id FROM `may_auth_group` WHERE `title`='超级管理员'));

-- ----------------------------
-- Table structure for `may_config`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `may_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `k` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '键',
  `v` text COLLATE utf8mb4_unicode_ci COMMENT '值',
  `type` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '类型',
  `infos` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `prompt` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '提示',
  `sorts` bigint(20) DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) NOT NULL COMMENT '是否显示',
  `texttype` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文本类型',
  `textvalue` text COLLATE utf8mb4_unicode_ci COMMENT '文本选项值',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`),
  KEY `k` (`k`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置字段';

-- ----------------------------
-- Records of may_config
-- ----------------------------
INSERT INTO `may_config` SELECT NULL,'upload_path','uploads','up','文件上传目录','文件上传根目录存放文件名','49','1','Input','','1699178086','1746775511' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='upload_path' AND type='up');
INSERT INTO `may_config` SELECT NULL,'upload_path','uploads','up','文件上传目录','文件上传根目录存放文件名','49','1','Input','','1699178086','1746775511' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='upload_path' AND type='up');
INSERT INTO `may_config` SELECT NULL,'upload_size','2','up','上传文件大小','单位【MB】，最大上传限制1MB则填写数字：1','48','1','Input','','1699178086','1746775509' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='upload_size' AND type='up');
INSERT INTO `may_config` SELECT NULL,'image_format','jpg,jpeg,png','up','上传图片格式','上传图片后缀限制格式','47','1','Input','','1699178086','1746775506' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='image_format' AND type='up');
INSERT INTO `may_config` SELECT NULL,'file_format','doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z','up','上传文件格式','上传文件后缀限制格式','46','1','Input','','1699178086','1746775504' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='file_format' AND type='up');
INSERT INTO `may_config` SELECT NULL,'flash_format','swf,flv','up','上传Flash格式','上传Flash后缀限制格式','45','1','Input','','1699178086','1746775502' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='flash_format' AND type='up');
INSERT INTO `may_config` SELECT NULL,'media_format','swf,flv,mp3,mp4,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb','up','上传视音频格式','上传视音频后缀限制格式','44','1','Input','','1699178086','1730355378' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='media_format' AND type='up');
INSERT INTO `may_config` SELECT NULL,'isprint','0','up','是否开启图片水印','是否开启图片水印','43','1','Radio','whether','1699178086','1746775498' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='isprint' AND type='up');
INSERT INTO `may_config` SELECT NULL,'print_image','','up','水印图片','可为上传的图片添加水印【开启了图片水印功能，请必须上传水印图片】','42','1','Image','','1699178086','1746775496' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='print_image' AND type='up');
INSERT INTO `may_config` SELECT NULL,'print_position','9','up','水印图片位置','水印图片位置','41','1','Select','print_position','1699178086','1746775495' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='print_position' AND type='up');
INSERT INTO `may_config` SELECT NULL,'print_blur','100','up','水印图片透明度','水印图片透明度，取值范围【0-100】','40','1','Input','','1699178086','1746775493' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='print_blur' AND type='up');
INSERT INTO `may_config` SELECT NULL,'file_url','','up','图片上传域名地址','图片路径保存数据库是否带域名，不建议填写，除非很清楚怎么使用','39','1','Input','','1699178086','1746775491' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='file_url' AND type='up');
INSERT INTO `may_config` SELECT NULL,'login_title','MayAdmin','sys','登录显示标题','登录显示标题','49','1','Input','','1699178086','1766127543' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='login_title' AND type='sys');
INSERT INTO `may_config` SELECT NULL,'top_big_logo','MayAdmin','sys','系统顶部LOGO','左侧菜单展开时系统顶部LOGO','46','1','Input','','1699178086','1766127553' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='top_big_logo' AND type='sys');
INSERT INTO `may_config` SELECT NULL,'top_small_logo','May','sys','系统顶部小LOGO','左侧菜单缩进时系统顶部小LOGO','45','1','Input','','1699178086','1766127558' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='top_small_logo' AND type='sys');
INSERT INTO `may_config` SELECT NULL,'copyright','Copyright © 2017-2025 &lt;a href=&quot;#&quot; &gt;QQ群：184278846&lt;/a&gt;','sys','系统版权','系统版权','44','1','Input','','1699178086','1766127560' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='copyright' AND type='sys');
INSERT INTO `may_config` SELECT NULL,'version','MayAdmin 1.0.0','sys','系统版本号','系统版本号','43','1','Input','','1699178086','1766127562' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='version' AND type='sys');
INSERT INTO `may_config` SELECT NULL,'login_image','MayAdmin后台管理','sys','登录图片LOGO','登录图片LOGO','48','1','Input','','1699178086','1766127547' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='login_image' AND type='sys');
INSERT INTO `may_config` SELECT NULL,'login_bg','/static/global/common/img/avatar.png','sys','登录背景图','登录背景图','47','1','Image','','1699178086','1766127550' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `may_config` WHERE k='login_bg' AND type='sys');

-- ----------------------------
-- Table structure for `may_upload_file`
-- ----------------------------
CREATE TABLE `may_upload_file` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `format` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT 'image' COMMENT '文件格式',
  `name` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '名称',
  `tag` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT 'thumb' COMMENT '标签',
  `dir` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT 'image' COMMENT '目录',
  `date` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '日期',
  `url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '链接',
  `width` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片宽',
  `height` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '图片高',
  `filesize` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件大小',
  `mime` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '文件类型',
  `sorts` bigint(20) NOT NULL COMMENT '排序',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL COMMENT '编辑时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='上传文件';
