-- ----------------------------
-- Table structure for `may_admin`
-- ----------------------------
DROP TABLE IF EXISTS `may_admin`;
CREATE TABLE `may_admin` (
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
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员';

-- ----------------------------
-- Records of may_admin
-- ----------------------------
INSERT INTO `may_admin` VALUES ('42', 'zqsj', '76629f37081d0f343ab6e01248ac6693', 'www.sxxblog.com', '654108442@qq.com', '', '1', '', '/static/global/common/img/avatar.png', '0', '127.0.0.1', '1699178086', '127.0.0.1', '1', '1699178086', '1699178086');

-- ----------------------------
-- Table structure for `may_auth_group`
-- ----------------------------
DROP TABLE IF EXISTS `may_auth_group`;
CREATE TABLE `may_auth_group` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限组';

-- ----------------------------
-- Records of may_auth_group
-- ----------------------------
INSERT INTO `may_auth_group` VALUES ('1', 'admin', '49', '超级管理员', '1', '1,2,12,17,18,19,20,21,22,13,14,15,16,23,24,25,26,3,4,8,9,10,11,27,28,29,30,31', '该角色不可删除，需要分配全部权限节点', '1699178086', '1699178086');
INSERT INTO `may_auth_group` VALUES ('2', 'admin', '48', '普通管理员', '1', '', '需要分配相应权限进行操作', '1699178086', '1699178086');

-- ----------------------------
-- Table structure for `may_auth_group_access`
-- ----------------------------
DROP TABLE IF EXISTS `may_auth_group_access`;
CREATE TABLE `may_auth_group_access` (
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户权限组';

-- ----------------------------
-- Records of may_auth_group_access
-- ----------------------------
INSERT INTO `may_auth_group_access` VALUES ('4', 'admin', '42', '1', '1699178086', '1699178086');

-- ----------------------------
-- Table structure for `may_auth_rule`
-- ----------------------------
DROP TABLE IF EXISTS `may_auth_rule`;
CREATE TABLE `may_auth_rule` (
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
INSERT INTO `may_auth_rule` VALUES ('1', '0', 'admin', '1', 'Index/index', '后台首页', '1', '1', '1', null, 'fa-solid fa-home', '999', '1699178086', '1746774507');
INSERT INTO `may_auth_rule` VALUES ('2', '1', 'admin', '2', 'Index/cleanCache', '清除缓存', '1', '1', '0', null, '', '48', '1699178086', '1721356174');
INSERT INTO `may_auth_rule` VALUES ('3', '0', 'admin', '1', 'leftSystem', '系统管理', '1', '1', '1', null, 'fa-solid fa-gear', '100', '1699178086', '1746774497');
INSERT INTO `may_auth_rule` VALUES ('4', '3', 'admin', '2', 'Config/index', '系统配置字段', '1', '1', '1', null, 'fa-solid fa-gears', '47', '1699178086', '1746774553');
INSERT INTO `may_auth_rule` VALUES ('8', '4', 'admin', '3', 'Config/create', '新增', '1', '1', '0', null, '', '49', '1699178086', '1746774674');
INSERT INTO `may_auth_rule` VALUES ('9', '4', 'admin', '3', 'Config/edit', '编辑', '1', '1', '0', null, '', '48', '1699178086', '1746774673');
INSERT INTO `may_auth_rule` VALUES ('10', '4', 'admin', '3', 'Config/delete', '删除', '1', '1', '0', null, '', '47', '1699178086', '1746774671');
INSERT INTO `may_auth_rule` VALUES ('11', '4', 'admin', '3', 'Config/save', '保存', '1', '1', '0', null, '', '46', '1699178086', '1746774669');
INSERT INTO `may_auth_rule` VALUES ('12', '0', 'admin', '1', 'leftAdmin', '管理员管理', '1', '1', '1', null, 'fa-solid fa-users', '300', '1699178086', '1727623868');
INSERT INTO `may_auth_rule` VALUES ('13', '12', 'admin', '2', 'AuthRule/index', '节点列表', '1', '1', '1', null, 'fa-solid fa-pen-ruler', '48', '1699178086', '1727624017');
INSERT INTO `may_auth_rule` VALUES ('14', '13', 'admin', '3', 'AuthRule/create', '新增', '1', '1', '0', null, '', '49', '1699178086', '1746774655');
INSERT INTO `may_auth_rule` VALUES ('15', '13', 'admin', '3', 'AuthRule/edit', '编辑', '1', '1', '0', null, '', '48', '1699178086', '1699178454');
INSERT INTO `may_auth_rule` VALUES ('16', '13', 'admin', '3', 'AuthRule/delete', '删除', '1', '1', '0', null, '', '47', '1699178086', '1746774653');
INSERT INTO `may_auth_rule` VALUES ('17', '12', 'admin', '2', 'Admin/index', '管理员列表', '1', '1', '1', null, 'fa-solid fa-user-tie', '49', '1699178086', '1746774533');
INSERT INTO `may_auth_rule` VALUES ('18', '17', 'admin', '3', 'Admin/create', '新增', '1', '1', '0', null, '', '49', '1699178086', '1746774643');
INSERT INTO `may_auth_rule` VALUES ('19', '17', 'admin', '3', 'Admin/edit', '编辑', '1', '1', '0', null, '', '48', '1699178086', '1746774641');
INSERT INTO `may_auth_rule` VALUES ('20', '17', 'admin', '3', 'Admin/delete', '删除', '1', '1', '0', null, '', '47', '1699178086', '1699178444');
INSERT INTO `may_auth_rule` VALUES ('21', '17', 'admin', '3', 'Admin/authGroup', '授权角色', '1', '1', '0', null, '', '46', '1699178086', '1746774638');
INSERT INTO `may_auth_rule` VALUES ('22', '1', 'admin', '2', 'Admin/editSelf', '个人资料', '1', '1', '0', null, '', '49', '1699178086', '1746774636');
INSERT INTO `may_auth_rule` VALUES ('23', '12', 'admin', '2', 'AuthGroup/index', '角色列表', '1', '1', '1', null, 'fa-solid fa-address-card', '47', '1699178086', '1746774604');
INSERT INTO `may_auth_rule` VALUES ('24', '23', 'admin', '3', 'AuthGroup/create', '新增', '1', '1', '0', null, '', '49', '1699178086', '1746774663');
INSERT INTO `may_auth_rule` VALUES ('25', '23', 'admin', '3', 'AuthGroup/edit', '编辑', '1', '1', '0', null, '', '48', '1699178086', '1699178469');
INSERT INTO `may_auth_rule` VALUES ('26', '23', 'admin', '3', 'AuthGroup/delete', '删除', '1', '1', '0', null, '', '47', '1699178086', '1746774661');
INSERT INTO `may_auth_rule` VALUES ('27', '3', 'admin', '2', 'Config/sysMenu', '系统配置', '1', '1', '1', null, 'fa-solid fa-gear', '48', '1699178086', '1731396901');
INSERT INTO `may_auth_rule` VALUES ('28', '27', 'admin', '3', 'Config/sys', '后台配置', '1', '1', '0', null, '', '49', '1699178086', '1746774569');
INSERT INTO `may_auth_rule` VALUES ('29', '27', 'admin', '3', 'Config/up', '上传配置', '1', '1', '0', null, '', '48', '1699178086', '1746774571');
INSERT INTO `may_auth_rule` VALUES ('30', '3', 'admin', '2', 'UploadFile/index', '附件列表', '1', '1', '1', null, 'fa-regular fa-file', '49', '1728830498', '1746774549');
INSERT INTO `may_auth_rule` VALUES ('31', '30', 'admin', '3', 'UploadFile/delete', '删除', '1', '1', '0', null, null, '49', '1728830758', '1746775263');

-- ----------------------------
-- Table structure for `may_config`
-- ----------------------------
DROP TABLE IF EXISTS `may_config`;
CREATE TABLE `may_config` (
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
INSERT INTO `may_config` VALUES ('1', 'upload_path', 'uploads', 'up', '文件上传目录', '文件上传根目录存放文件名', '49', '1', 'Input', '', '1699178086', '1746775511');
INSERT INTO `may_config` VALUES ('2', 'upload_size', '2', 'up', '上传文件大小', '单位【MB】，最大上传限制1MB则填写数字：1', '48', '1', 'Input', '', '1699178086', '1746775509');
INSERT INTO `may_config` VALUES ('3', 'image_format', 'jpg,jpeg,png', 'up', '上传图片格式', '上传图片后缀限制格式', '47', '1', 'Input', '', '1699178086', '1746775506');
INSERT INTO `may_config` VALUES ('4', 'file_format', 'doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z', 'up', '上传文件格式', '上传文件后缀限制格式', '46', '1', 'Input', '', '1699178086', '1746775504');
INSERT INTO `may_config` VALUES ('5', 'flash_format', 'swf,flv', 'up', '上传Flash格式', '上传Flash后缀限制格式', '45', '1', 'Input', '', '1699178086', '1746775502');
INSERT INTO `may_config` VALUES ('6', 'media_format', 'swf,flv,mp3,mp4,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb', 'up', '上传视音频格式', '上传视音频后缀限制格式', '44', '1', 'Input', '', '1699178086', '1730355378');
INSERT INTO `may_config` VALUES ('7', 'isprint', '0', 'up', '是否开启图片水印', '是否开启图片水印', '43', '1', 'Radio', 'whether', '1699178086', '1746775498');
INSERT INTO `may_config` VALUES ('8', 'print_image', '', 'up', '水印图片', '可为上传的图片添加水印【开启了图片水印功能，请必须上传水印图片】', '42', '1', 'Image', '', '1699178086', '1746775496');
INSERT INTO `may_config` VALUES ('9', 'print_position', '9', 'up', '水印图片位置', '水印图片位置', '41', '1', 'Select', 'print_position', '1699178086', '1746775495');
INSERT INTO `may_config` VALUES ('10', 'print_blur', '100', 'up', '水印图片透明度', '水印图片透明度，取值范围【0-100】', '40', '1', 'Input', '', '1699178086', '1746775493');
INSERT INTO `may_config` VALUES ('11', 'file_url', '', 'up', '图片上传域名地址', '图片路径保存数据库是否带域名，不建议填写，除非很清楚怎么使用', '39', '1', 'Input', '', '1699178086', '1746775491');
INSERT INTO `may_config` VALUES ('20', 'login_title', 'MayAdmin', 'sys', '登录显示标题', '登录显示标题', '49', '1', 'Input', '', '1699178086', '1766127543');
INSERT INTO `may_config` VALUES ('21', 'top_big_logo', 'MayAdmin', 'sys', '系统顶部LOGO', '左侧菜单展开时系统顶部LOGO', '46', '1', 'Input', '', '1699178086', '1766127553');
INSERT INTO `may_config` VALUES ('22', 'top_small_logo', 'May', 'sys', '系统顶部小LOGO', '左侧菜单缩进时系统顶部小LOGO', '45', '1', 'Input', '', '1699178086', '1766127558');
INSERT INTO `may_config` VALUES ('23', 'copyright', 'Copyright © 2017-2025 &lt;a href=&quot;#&quot; &gt;QQ群：184278846&lt;/a&gt;', 'sys', '系统版权', '系统版权', '44', '1', 'Input', '', '1699178086', '1766127560');
INSERT INTO `may_config` VALUES ('24', 'version', 'MayAdmin 1.0.0', 'sys', '系统版本号', '系统版本号', '43', '1', 'Input', '', '1699178086', '1766127562');
INSERT INTO `may_config` VALUES ('28', 'login_image', 'MayAdmin后台管理', 'sys', '登录图片LOGO', '登录图片LOGO', '48', '1', 'Input', '', '1699178086', '1766127547');
INSERT INTO `may_config` VALUES ('29', 'login_bg', '/static/global/common/img/avatar.png', 'sys', '登录背景图', '登录背景图', '47', '1', 'Image', '', '1699178086', '1766127550');

-- ----------------------------
-- Table structure for `may_upload_file`
-- ----------------------------
DROP TABLE IF EXISTS `may_upload_file`;
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
