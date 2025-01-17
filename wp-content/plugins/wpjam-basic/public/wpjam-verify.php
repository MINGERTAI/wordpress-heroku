<?php
class WPJAM_Verify{
	public static function on_admin_init(){
		$menu_filter	= (is_multisite() && is_network_admin()) ? 'wpjam_network_pages' : 'wpjam_pages';

		if(get_transient('wpjam_basic_verify')){
			add_filter($menu_filter, [self::class, 'filter_menu_pages']);
		}else{
			$verified	= self::verify();

			if($verified){
				if(isset($_GET['unbind_wpjam_user'])){
					self::unbind_user();
				}
			}else{
				add_filter($menu_filter, [self::class, 'filter_menu_pages']);

				wpjam_add_menu_page('wpjam-verify', [
					'parent'		=> 'wpjam-basic',
					'order'			=> 3,
					'menu_title'	=> '扩展管理',
					'page_title'	=> '验证 WPJAM',
					'function'		=> 'form',
					'form_name'		=> 'verify_wpjam',
					'load_callback'	=> [self::class, 'page_action']
				]);
			}
		}
	}

	public static function filter_menu_pages($menu_pages){
		$subs	= $menu_pages['wpjam-basic']['subs'];

		if(isset($subs['wpjam-verify'])){
			$menu_pages['wpjam-basic']['subs']	= wp_array_slice_assoc($subs, ['wpjam-basic', 'wpjam-verify']);
		}else{
			$menu_pages['wpjam-basic']['subs']	= wpjam_array_except($subs, ['wpjam-about']);
		}

		return $menu_pages;
	}

	public static function verify(){
		$weixin_user	= self::get_weixin_user();

		if(empty($weixin_user) || empty($weixin_user['subscribe'])){
			return false;
		}elseif(time() - $weixin_user['last_update'] < DAY_IN_SECONDS){
			return true;
		}

		$openid		= $weixin_user['openid'];
		$hash		= $weixin_user['hash']	?? '';
		$user_id	= get_current_user_id();

		if(get_transient('fetching_wpjam_weixin_user_'.$openid)){
			return false;
		}

		set_transient('fetching_wpjam_weixin_user_'.$openid, 1, 10);
		
		if($hash){
			$response	= wpjam_remote_request('http://wpjam.wpweixin.com/api/weixin/verify.json', [
				'method'	=> 'POST',
				'body'		=> ['openid'=>$openid, 'hash'=>$hash]
			]);
		}else{
			$response	= wpjam_remote_request('http://jam.wpweixin.com/api/topic/user/get.json?openid='.$openid);
		}

		if(is_wp_error($response) && $response->get_error_code() != 'invalid_openid'){
			$failed_times	= get_user_meta($user_id, 'wpjam_weixin_user_failed_times') ?: 0;
			$failed_times ++;

			if($failed_times >= 3){	// 重复三次
				delete_user_meta($user_id, 'wpjam_weixin_user_failed_times');
				delete_user_meta($user_id, 'wpjam_weixin_user');
			}else{
				update_user_meta($user_id, 'wpjam_weixin_user_failed_times', $failed_times);
			}

			return false;
		}

		if($hash){
			$weixin_user	= $response;
		}else{
			$weixin_user	= $response['user'];
		}

		if(empty($weixin_user) || !$weixin_user['subscribe']){
			delete_user_meta($user_id, 'wpjam_weixin_user');
			delete_user_meta($user_id, 'wpjam_weixin_user_failed_times');
			return false;
		}

		$weixin_user['last_update']	= time();

		update_user_meta($user_id, 'wpjam_weixin_user', $weixin_user);
		delete_user_meta($user_id, 'wpjam_weixin_user_failed_times');

		return true;
	}

	public static function verify_domain($id=0){
		return get_transient('wpjam_basic_verify');
	}

	public static function get_weixin_user(){
		return get_user_meta(get_current_user_id(), 'wpjam_weixin_user', true);
	}

	public static function get_openid(){
		$weixin_user	= self::get_weixin_user();

		if($weixin_user && isset($weixin_user['openid'])){
			return $weixin_user['openid'];
		}else{
			return '';
		}
	}

	public static function get_qrcode($key=''){
		$key	= $key?:md5(home_url().'_'.get_current_user_id());

		return wpjam_remote_request('http://jam.wpweixin.com/api/weixin/qrcode/create.json?key='.$key);
	}

	public static function bind_user($data){
		// $weixin_user	= wpjam_remote_request('http://jam.wpweixin.com/api/weixin/qrcode/verify.json', [
		// 	'method'	=> 'POST',
		// 	'body'		=> $data
		// ]);

		$weixin_user	= wpjam_remote_request('https://wpjam.wpweixin.com/api/weixin/verify.json', [
			'method'	=> 'POST',
			'body'		=> $data
		]);

		if(is_wp_error($weixin_user)){
			return $weixin_user;
		}

		$weixin_user['last_update']	= time();

		update_user_meta(get_current_user_id(), 'wpjam_weixin_user', $weixin_user);

		return $weixin_user;
	}

	public static function unbind_user(){
		delete_user_meta(get_current_user_id(), 'wpjam_weixin_user');

		wp_redirect(admin_url('admin.php?page=wpjam-verify'));
	}

	public static function ajax_verify(){
		$data	= wpjam_get_parameter('data',	['method'=>'POST', 'sanitize_callback'=>'wp_parse_args']);
		$result = self::bind_user($data);

		if(is_wp_error($result)){
			return $result;
		}

		$page	= current_user_can('manage_options') ? 'wpjam-extends' : 'wpjam-basic-topics';

		return ['url'=>admin_url('admin.php?page='.$page)];
	}

	public static function page_action(){
		// $response	= self::get_qrcode();

		// if(is_wp_error($response)){
		// 	wp_die($response);
		// }else{
			// $qrcode = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$response['ticket'];

			wpjam_register_page_action('verify_wpjam', [
				'submit_text'	=> '验证',
				'callback'		=> ['WPJAM_Verify', 'ajax_verify'],
				'response'		=> 'redirect',
				'fields'		=> [
					'qr_set'	=> ['title'=>'1. 二维码',	'type'=>'fieldset',	'fields'=>[
						'qrcode_view'	=> ['type'=>'view',	'value'=>'使用微信扫描下面的二维码：'],
						'qrcode2'		=> ['type'=>'view',	'value'=>'<img src="https://open.weixin.qq.com/qr/code?username=wpjamcom" style="max-width:250px;" />']
					]],
					'keyword'	=> ['title'=>'2. 关键字',	'type'=>'view',	'value'=>'回复关键字「<strong>验证码</strong>」。'],
					'code_set'	=> ['title'=>'3. 验证码',	'type'=>'fieldset',	'fields'=>[
						'code_view'		=> ['type'=>'view',	'value'=>'将获取验证码输入提交即可！'],
						'code'			=> ['type'=>'number',	'class'=>'all-options',	'description'=>'验证码5分钟内有效！'],
					]],
					'notes'		=> ['title'=>'4. 注意事项',	'type'=>'view',	'value'=>'验证码5分钟内有效！<br /><br />如果验证不通过，请使用 Chrome 浏览器验证，并在验证之前清理浏览器缓存。'],
					// 'scene'		=> ['title'=>'scene',	'type'=>'hidden',	'value'=>$response['scene']]
				]
			]);

			wp_add_inline_style('list-tables', "\n".'.form-table th{width: 100px;}');
		// }
	}
}

add_action('wpjam_loaded',	function(){
	add_action('wpjam_admin_init',	['WPJAM_Verify', 'on_admin_init']);
});