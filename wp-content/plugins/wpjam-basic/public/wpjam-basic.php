<?php
class WPJAM_Basic{
	use WPJAM_Setting_Trait;

	private function __construct(){
		$this->init('wpjam-basic', true);
	}

	public function get_setting($name, $default=null){
		$value	= $this->settings[$name] ?? $default;

		if($name == 'no_category_base'){
			if(!$value || $GLOBALS['wp_rewrite']->use_verbose_page_rules){
				return false;
			}
			
			return $this->get_setting($name.'_for', 'category');
		}

		return $value;
	}

	public function get_sections(){
		if($GLOBALS['plugin_page'] == 'wpjam-basic'){
			$disabled_fields	= [
				'disable_revision'			=>['title'=>'屏蔽文章修订',		'short'=>'S9PBDUtk0jax7eL5kDFiQg',	'description'=>'屏蔽文章修订功能，精简文章表数据。'],
				'disable_trackbacks'		=>['title'=>'屏蔽Trackbacks',	'short'=>'FZ7zOYOTnqo65U-lx6QpYw',	'description'=>'彻底关闭Trackbacks，防止垃圾留言。'],
				'disable_emoji'				=>['title'=>'屏蔽Emoji图片',		'short'=>'BMYGDB7GfK5rb4PlwD5xIg',	'description'=>'屏蔽Emoji图片转换功能，直接使用Emoji。'],
				'disable_texturize'			=>['title'=>'屏蔽字符转码',		'short'=>'9sSXaK5r5XO7xB-3yjV1zQ',	'description'=>'屏蔽字符换成格式化的HTML实体功能。'],
				'disable_feed'				=>['title'=>'屏蔽站点Feed',		'short'=>'YgJT8Mlhv08p9lvVLC5L1Q',	'description'=>'屏蔽站点Feed，防止文章被快速被采集。'],
				'disable_admin_email_check'	=>['title'=>'屏蔽邮箱验证',		'short'=>'GUPxPQQo3Qa2AMuKuM7CzQ',	'description'=>'屏蔽站点管理员邮箱定期验证功能。'],
				'disable_auto_update'		=>['title'=>'屏蔽自动更新',		'short'=>'bxVdrLhGo075s4TKmi6p3A',	'description'=>'关闭自动更新功能，通过手动或SSH方式更新。'],
				'disable_privacy'			=>['title'=>'屏蔽后台隐私',		'short'=>'aXx1ggscmzvxAgo4tM9bWQ',	'description'=>'移除为欧洲通用数据保护条例而生成的隐私页面。'],
				'disable_autoembed'			=>['title'=>'屏蔽Auto Embeds',	'short'=>'cg5cVPGj2Hwq0Jxn7DJw6Q',	'description'=>'禁用Auto Embeds功能，加快页面解析速度。'],
				'disable_post_embed'		=>['title'=>'屏蔽文章Embed',		'short'=>'Flm2ggE3VE-qcoZnY4Nycg',	'description'=>'屏蔽嵌入其他WordPress文章的Embed功能。'],
				'disable_block_editor'		=>['title'=>'屏蔽古腾堡编辑器',	'short'=>'LsVZX7p64-fcICwwA6IEQw',	'description'=>'屏蔽Gutenberg编辑器，换回经典编辑器。'],
				'disable_xml_rpc'			=>['title'=>'屏蔽XML-RPC',		'short'=>'hYAe_a497ZkkwlkM-cFsOg',	'description'=>'关闭XML-RPC功能，只在后台发布文章。']
			];

			if($GLOBALS['wp_rewrite']->use_verbose_page_rules){
				$no_category_base_field	= ['type'=>'view',		'value'=>'站点当前的固定链接设置使得不能去掉分类目录链接中的 category，请先修改固定链接设置。'];
			}else{
				$no_category_base_field	= ['type'=>'fieldset',	'group'=>true,	'fields'=>[
					'no_category_base'		=>['short'=>'AD0w1d8NLibtc6CFYsLiJg',	'description'=>'去掉分类目录链接中的 category。'],
				]];

				$hierarchical_taxonomies	= get_taxonomies(['public'=>true,'hierarchical'=>true], 'objects');

				if(count($hierarchical_taxonomies) > 1){
					$no_category_base_field['fields']['no_category_base_for']	= ['title'=>'分类模式：',	'show_if'=>['key'=>'no_category_base','value'=>1],	'options'=>array_column($hierarchical_taxonomies, 'label', 'name')];
				}else{
					$no_category_base_field['fields']['no_category_base_for']	= ['type'=>'hidden',	'value'=>array_key_first($hierarchical_taxonomies)];
				}
			}

			$x_frame_options	= [''=>'所有网页', 'SAMEORIGIN'=>'只允许同域名网页', 'DENY'=>'不允许任何网页'];

			$enhance_fields		= [
				'google_fonts_set'		=>['title'=>'Google字体加速','type'=>'fieldset',	'short'=>'Sz0QlZ-kW0C70NkbpoDpag',	'fields'=>WPJAM_Google_Font::get_fields()],
				'gravatar_set'			=>['title'=>'Gravatar加速',	'type'=>'fieldset',	'short'=>'eyHr2r-vrqSqADwMkPh95Q',	'fields'=>WPJAM_Gravatar::get_fields()],
				'x-frame-options'		=>['title'=>'Frame嵌入',		'short'=>'P__N3Srj-4WvZdkLORlBRg',	'options'=>$x_frame_options],
				'no_category_base_set'	=>['title'=>'分类链接简化']+$no_category_base_field,
				'timestamp_file_name'	=>['title'=>'图片时间戳',		'short'=>'yodHw7W-qlnkbYqBPbHTEA',	'description'=>'给上传的图片加上时间戳，防止大量的SQL查询。'],
				'frontend_set'			=>['title'=>'前台页面优化',	'type'=>'fieldset',	'fields'=>[
					'remove_head_links'			=>['short'=>'7W119jeuzNNehWzokGEb3A',	'description'=>'移除页面头部版本号和服务发现标签代码。'],
					'remove_admin_bar'			=>['short'=>'_YpFZ4gttJObcD7-CbWNhg',	'description'=>'移除工具栏和后台个人资料中工具栏相关的选项。'],
					'remove_capital_P_dangit'	=>['short'=>'0tVQbq9cWvFjpcDYwSVY_w',	'description'=>'移除 WordPress 大小写修正，自己决定怎么写。'],
				]],
				'backend_set'			=>['title'=>'后台页面优化',	'type'=>'fieldset',	'fields'=>[
					'remove_help_tabs'			=>['short'=>'p6HWVHIos2h5h6M78Lbclw',	'description'=>'移除后台界面右上角的帮助。'],
					'remove_screen_options'		=>['short'=>'p6HWVHIos2h5h6M78Lbclw',	'description'=>'移除后台界面右上角的选项。'],
				]],
				'optimized_by_wpjam'	=>['title'=>'WPJAM Basic',	'description'=>'在网站底部显示：Optimized by WPJAM Basic。']
			];

			return [
				'disabled'	=>['title'=>'功能屏蔽',	'fields'=>$this->parse_fields($disabled_fields)],
				'enhance'	=>['title'=>'增强优化',	'fields'=>$this->parse_fields($enhance_fields)],
			];
		}elseif($GLOBALS['plugin_page'] = 'wpjam-posts'){
			$excerpt_show_if	= ['key'=>'excerpt_optimization', 'value'=>1];
			$excerpt_options	= [0=>'WordPress 默认方式截取', 1=>'按照中文最优方式截取', 2=>'直接不显示摘要'];
			$posts_fields		= [
				'post_list_fieldset'	=> ['title'=>'后台列表',	'type'=>'fieldset',	'fields'=>[
					'post_list_ajax'			=> ['value'=>1,	'description'=>'支持全面的 <strong>AJAX操作</strong>'],
					'post_list_set_thumbnail'	=> ['value'=>1,	'description'=>'显示和设置<strong>文章缩略图</strong>'],
					'post_list_update_views'	=> ['value'=>1,	'description'=>'显示和修改<strong>文章浏览数</strong>'],
					'post_list_sort_selector'	=> ['value'=>1,	'description'=>'显示<strong>排序下拉选择框</strong>'],
					'post_list_author_filter'	=> ['value'=>1,	'description'=>'支持<strong>通过作者进行过滤</strong>'],
					'upload_external_images'	=> ['value'=>0,	'description'=>'支持<strong>上传外部图片</strong>'],
				]],
				'excerpt_fieldset'		=> ['title'=>'文章摘要',	'type'=>'fieldset',	'fields'=>[
					'excerpt_optimization'	=> ['title'=>'未设文章摘要：',	'options'=>$excerpt_options],
					'excerpt_length'		=> ['title'=>'文章摘要长度：',	'type'=>'number',	'show_if'=>$excerpt_show_if,	'value'=>200],
					'excerpt_cn_view2'		=> ['title'=>'中文截取算法：',	'type'=>'view',		'show_if'=>$excerpt_show_if,	'short'=>'QB6zUXA_QI1lseAfNV29Lg',	'value'=>'<strong>中文算2个字节，英文算1个字节</strong>']
				]],
				'remove_post_tag'		=> ['title'=>'移除标签',	'value'=>0,	'description'=>'移除默认文章类型的标签功能支持'],
				'404_optimization'		=> ['title'=>'404 跳转',	'value'=>0,	'description'=>'增强404页面跳转到文章页面能力']
			];

			return ['posts'	=>['title'=>'文章设置',	'fields'=>$this->parse_fields($posts_fields)]];
		}
	}

	public function parse_fields($fields){
		foreach($fields as &$field){
			if(empty($field['type']) && !isset($field['options'])){
				$field['type']	= 'checkbox';
			}

			$field['type']	= $field['type'] ?? '';

			if($field['type'] == 'fieldset'){
				$field['fields']	= $this->parse_fields($field['fields']);
			}

			if($short = wpjam_array_pull($field, 'short')){
				$link	= 'https://mp.weixin.qq.com/s/'.$short;
				$key	= $field['type'] == 'view' ? 'value' : 'description';

				if(isset($field[$key])){
					$field[$key]	= '<a target="_blank" href="'.$link.'">'.$field[$key].'</a>';
				}else{
					$field['title']	.= '<a target="_blank" href="'.$link.'" class="dashicons-before dashicons-editor-help"></a>';
				}
			}
		}

		return $fields;
	}

	public function sanitize_option($value){
		flush_rewrite_rules();

		return $value;
	}

	public static function get_defaults(){
		return [
			'disable_revision'			=> 1,
			'disable_trackbacks'		=> 1,
			'disable_emoji'				=> 1,
			'disable_texturize'			=> 1,
			'disable_privacy'			=> 1,
			'remove_head_links'			=> 1,
			'remove_capital_P_dangit'	=> 1
		];
	}
}

class WPJAM_Extend{
	public static function load(){
		$extends	= wpjam_get_option('wpjam-extends');
		$extends	= array_filter($extends);

		if(is_multisite()){
			$sitewide_extends	= wpjam_get_site_option('wpjam-extends');
			$sitewide_extends	= array_filter($sitewide_extends);

			$extends	= array_merge($extends, $sitewide_extends);
		}

		foreach($extends as $extend_file => $dummy){
			if(is_file(WPJAM_BASIC_PLUGIN_DIR.'extends/'.$extend_file)){
				include WPJAM_BASIC_PLUGIN_DIR.'extends/'.$extend_file;
			}
		}
	}

	public static function get_fields(){
		$fields		= [];
		$headers	= ['Name'=>'Name',	'URI'=>'URI',	'Version'=>'Version',	'Description'=>'Description'];
		$extends 	= wpjam_get_option('wpjam-extends');
		$extends	= $extends ? array_filter($extends) : [];
		$extend_dir = WPJAM_BASIC_PLUGIN_DIR.'extends';

		if($extend_handle = opendir($extend_dir)){
			while(($extend_file = readdir($extend_handle)) !== false){
				if($extend_file != '.' && $extend_file != '..' && is_file($extend_dir.'/'.$extend_file) && pathinfo($extend_file, PATHINFO_EXTENSION) == 'php'){
					$data	= get_file_data($extend_dir.'/'.$extend_file, $headers);

					if($data['Name']){
						$title	= '<a href="'.$data['URI'].'" target="_blank">'.$data['Name'].'</a>';
						$value	= isset($extends[$extend_file]) ? 1 : 0;

						$fields[$extend_file] = ['title'=>$title, 'type'=>'checkbox',	'value'=>$value, 'description'=>$data['Description']];
					}
				}
			}

			closedir($extend_handle);
		}

		$fields	= wp_list_sort($fields, 'value', 'DESC', true);

		if(is_multisite() && !is_network_admin()){
			$sitewide_extends	= wpjam_get_site_option('wpjam-extends');
			$sitewide_extends	= array_filter($sitewide_extends);

			foreach($sitewide_extends as $extend_file => $value){
				unset($fields[$extend_file]);
			}
		}

		return $fields;
	}
}

class WPJAM_Gravatar{
	use WPJAM_Register_Trait;

	public static function get_fields(){
		$options	= wp_list_pluck(self::get_registereds(), 'title');
		$options	= [''=>'默认服务']+preg_filter('/$/', '加速服务', $options)+['custom'=>'自定义加速服务'];

		return [
			'gravatar'			=>['options'=>$options],
			'gravatar_custom'	=>['type'=>'text',	'show_if'=>['key'=>'gravatar','value'=>'custom'],	'placeholder'=>'请输入 Gravatar 加速服务地址']
		];
	}

	public static function filter_pre_data($args, $id_or_email){
		$email_hash	= $email = $user = false;
		
		if(is_object($id_or_email) && isset($id_or_email->comment_ID)){
			$id_or_email	= get_comment($id_or_email);
		}

		if(is_numeric($id_or_email)){
			$user	= get_user_by('id', absint($id_or_email));
		}elseif($id_or_email instanceof WP_User){
			$user	= $id_or_email;
		}elseif($id_or_email instanceof WP_Post){
			if($id_or_email->post_author){
				$user	= get_user_by('id', (int)$id_or_email->post_author);
			}
		}elseif($id_or_email instanceof WP_Comment){
			if($avatar = get_comment_meta($id_or_email->comment_ID, 'avatarurl', true)){
				return array_merge($args, [
					'url'			=> wpjam_get_thumbnail($avatar, [$args['width'], $args['height']]),
					'found_avatar'	=> true
				]);
			}

			if($id_or_email->user_id){
				$user	= get_user_by('id', (int)$id_or_email->user_id);
			}elseif($id_or_email->comment_author_email){
				$email	= $id_or_email->comment_author_email;
			}
		}elseif(is_string($id_or_email)){
			if(strpos($id_or_email, '@md5.gravatar.com')){
				list($email_hash)	= explode('@', $id_or_email);
			}else{
				$email	= $id_or_email;
			}
		}

		if($user){
			if($avatar = get_user_meta($user->ID, 'avatarurl', true)){
				return array_merge($args, [
					'url'			=> wpjam_get_thumbnail($avatar, [$args['width'], $args['height']]),
					'found_avatar'	=> true
				]);
			}

			$args	= apply_filters('wpjam_default_avatar_data', $args, $user->ID);

			if($args['found_avatar']){
				return $args;
			}

			$email = $user->user_email;
		}

		if(!$email_hash){
			$email_hash = $email ? md5(strtolower(trim($email))) : '';
		}

		$url	= 'https://cn.gravatar.com/avatar/';

		if($name = wpjam_basic_get_setting('gravatar')){
			if($name == 'custom'){
				if($custom = wpjam_basic_get_setting('gravatar_custom')){
					$url	= $custom;
				}
			}else{
				if($object = self::get($name)){
					$url	= $object->url;
				}
			}
		}

		$url_args	= array_filter([
			's'	=> $args['size'],
			'd'	=> $args['default'],
			'f'	=> $args['force_default'] ? 'y' : false,
			'r'	=> $args['rating'],
		]);

		$url	= set_url_scheme($url.$email_hash, $args['scheme']);
		$url	= add_query_arg(rawurlencode_deep($url_args), $url);

		$args['url']	= apply_filters('get_avatar_url', $url, $id_or_email, $args);

		if($email_hash){
			$args['found_avatar']	= true;
		}

		return $args;
	}
}

class WPJAM_Google_Font{
	use WPJAM_Register_Trait;

	public static function get_fields(){
		$options	= wp_list_pluck(self::get_registereds(), 'title');
		$options	= [''=>'默认服务']+preg_filter('/$/', '加速服务', $options)+['custom'=>'自定义加速服务'];
		$fields		= ['google_fonts'=>['options'=>$options]];

		foreach(self::get_search() as $key => $domain){
			$fields[$key]	= ['type'=>'text',	'show_if'=>['key'=>'google_fonts','value'=>'custom'],	'placeholder'=>'请输入'.$domain.'加速服务地址'];
		}

		return $fields;
	}

	public static function get_search(){
		return [
			'googleapis_fonts'			=> 'fonts.googleapis.com', 
			'googleapis_ajax'			=> 'ajax.googleapis.com',
			'googleusercontent_themes'	=> 'themes.googleusercontent.com',
			'gstatic_fonts'				=> 'fonts.gstatic.com'
		];
	}

	public static function filter_html($html){
		if($name = wpjam_basic_get_setting('google_fonts')){
			$search	= $replace = [];

			if($name == 'custom'){
				foreach(self::get_search() as $font_key => $domain){
					if($mirror = wpjam_basic_get_setting($font_key)){
						$search[]	= '//'.$domain;
						$replace[]	= str_replace(['http://','https://'], '//', $mirror);
					}
				}
			}elseif($object	= self::get($name)){
				$search		= preg_filter('/^/', '//', array_values(self::get_search()));
				$replace	= $object->replace;
			}

			$html	= $search ? str_replace($search, $replace, $html) : $html;
		}

		return $html;
	}
}

class WPJAM_Custom{
	use WPJAM_Setting_Trait;

	private function __construct(){
		$this->init('wpjam-custom', true);
	}

	public function get_setting($name, $default=null){
		if($name == 'wp_head'){
			$name	= 'head';
		}elseif($name == 'wp_footer'){
			$name	= 'footer';
		}

		return $this->settings[$name] ?? $default;;
	}

	public function get_sections(){
		return [
			'wpjam-custom'	=> ['title'=>'前台定制',	'fields'=>[
				'head'			=> ['title'=>'前台 Head 代码',	'type'=>'textarea',	'class'=>''],
				'footer'		=> ['title'=>'前台 Footer 代码',	'type'=>'textarea',	'class'=>''],
			]],
			'admin-custom'	=> ['title'=>'后台定制',	'fields'=>[
				'admin_logo'	=> ['title'=>'工具栏左上角 Logo',	'type'=>'img',	'item_type'=>'url',	'description'=>'建议大小：20x20。如果前台也显示工具栏，也会被修改。'],
				'admin_head'	=> ['title'=>'后台 Head 代码 ',	'type'=>'textarea',	'class'=>''],
				'admin_footer'	=> ['title'=>'后台 Footer 代码',	'type'=>'textarea',	'class'=>'']
			]],
			'login-custom'	=> ['title'=>'登录界面', 	'fields'=>[
				'login_head'				=> ['title'=>'登录界面 Head 代码',	'type'=>'textarea',	'class'=>''],
				'login_footer'				=> ['title'=>'登录界面 Footer 代码',	'type'=>'textarea',	'class'=>''],
				'login_redirect'			=> ['title'=>'登录之后跳转的页面',		'type'=>'text'],
				'disable_language_switcher'	=> ['title'=>'登录界面语言切换器',		'type'=>'checkbox',	'description'=>'屏蔽登录界面语言切换器'],
			]]
		];
	}
}

function wpjam_basic_get_setting($name, $default=null){
	return WPJAM_Basic::get_instance()->get_setting($name, $default);
}

function wpjam_basic_update_setting($name, $value){
	return WPJAM_Basic::get_instance()->update_setting($name, $value);
}

function wpjam_basic_delete_setting($name){
	return WPJAM_Basic::get_instance()->delete_setting($name);
}

function wpjam_basic_get_default_settings(){
	return WPJAM_Basic::get_defaults();
}

function wpjam_add_basic_sub_page($sub_slug, $args=[]){
	wpjam_add_menu_page($sub_slug, array_merge($args, ['parent'=>'wpjam-basic']));
}

function wpjam_custom_get_setting($name, $default=null){
	return WPJAM_Custom::get_instance()->get_setting($name, $default);
}

function wpjam_register_google_font_services($name, $args){
	return WPJAM_Google_Font::register($name, $args);
}

function wpjam_register_gravatar_services($name, $args){
	return WPJAM_Gravatar::register($name, $args);
}

add_action('plugins_loaded', function(){
	$extend_dir	= get_template_directory().'/extends';

	if(is_dir($extend_dir) && ($extend_handle = opendir($extend_dir))){
		while(($extend = readdir($extend_handle)) !== false){
			if($extend != '.' && $extend != '..' && is_dir($extend_dir.'/'.$extend) && is_file($extend_dir.'/'.$extend.'/'.$extend.'.php')){
				include $extend_dir.'/'.$extend.'/'.$extend.'.php';
			}
		}

		closedir($extend_handle);
	}
	
	$actives = get_option('wpjam-actives', null);

	if(is_array($actives)){
		foreach($actives as $active){
			if(is_array($active) && isset($active['hook'])){
				add_action($active['hook'], $active['callback']);
			}else{
				add_action('wp_loaded', $active);
			}
		}

		update_option('wpjam-actives', []);
	}elseif(is_null($actives)){
		update_option('wpjam-actives', []);
	}
}, 0);

add_action('wpjam_loaded',	function(){
	WPJAM_Extend::load();

	wpjam_register_gravatar_services('cravatar',	['title'=>'Cravatar',	'url'=>'https://cravatar.cn/avatar/']);
	wpjam_register_gravatar_services('geekzu',		['title'=>'极客族',		'url'=>'https://sdn.geekzu.org/avatar/']);
	wpjam_register_gravatar_services('loli',		['title'=>'loli',		'url'=>'https://gravatar.loli.net/avatar/']);
	wpjam_register_gravatar_services('loli',		['title'=>'loli',		'url'=>'https://gravatar.loli.net/avatar/']);
	wpjam_register_gravatar_services('sep_cc',		['title'=>'sep.cc',		'url'=>'https://cdn.sep.cc/avatar/']);

	wpjam_register_google_font_services('geekzu',	['title'=>'极客族',		'replace'=>[
		'//fonts.geekzu.org',
		'//gapis.geekzu.org/ajax',
		'//gapis.geekzu.org/g-themes',
		'//gapis.geekzu.org/g-fonts'
	]]);

	wpjam_register_google_font_services('loli',		['title'=>'loli',		'replace'=>[
		'//fonts.loli.net',
		'//ajax.loli.net',
		'//themes.loli.net',
		'//gstatic.loli.net'
	]]);

	wpjam_register_google_font_services('ustc',		['title'=>'中科大',		'replace'=>[
		'//fonts.lug.ustc.edu.cn',
		'//ajax.lug.ustc.edu.cn',
		'//google-themes.lug.ustc.edu.cn',
		'//fonts-gstatic.lug.ustc.edu.cn'
	]]);

	add_filter('pre_get_avatar_data',	['WPJAM_Gravatar', 'filter_pre_data'], 10, 2);
	add_filter('wpjam_html',			['WPJAM_Google_Font', 'filter_html'], 10, 2);
});