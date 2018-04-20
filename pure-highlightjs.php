<?php
/*
Plugin Name: Pure Highlightjs
Plugin URI: https://github.com/sunriseydy/Pure-Highlightjs
Plugin Update URL: https://github.com/sunriseydy/Pure-Highlightjs/releases
Description: 一个可以在编辑器中可视化选择代码语言和插入代码的 WordPress 插件，可以显示行号、鼠标悬浮高亮和被标记行高亮
Author: sunriseydy
Version: 2.0
Author URI: https://blog.sunriseydy.top/
License: MIT
*/

if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define( 'PURE_HIGHLIGHTJS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PURE_HIGHLIGHTJS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PURE_HIGHLIGHTJS_DEFAULT_THEME', 'default' );

register_activation_hook( __FILE__, 'pure_highlightjs_activation' );
register_deactivation_hook( __FILE__, 'pure_highlightjs_deactivation' );

function pure_highlightjs_activation() {
}

function pure_highlightjs_deactivation() {
    delete_option('pure-highlightjs-theme');
	delete_option('line_color_setting');
}

add_action( 'admin_init', 'pure_highlightjs_admin_init' );

function pure_highlightjs_admin_init() {
    static $inited = false;

    if ( $inited ) {
        return;
    }

    register_setting( 'pure-highlightjs-group', 'pure-highlightjs-theme' );
	
	register_setting( 'pure-highlightjs-group', 'line_color_setting' );
	
    load_plugin_textdomain( 'pure-highlightjs', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );

    pure_highlightjs_update_option();

    $inited = true;
}

add_filter( 'plugin_action_links', 'pure_highlightjs_action_links', 10, 2 );

function pure_highlightjs_action_links( $links, $file ) {
    if ( $file == plugin_basename( __FILE__ ) ) {
        $links[] = '<a href="' . esc_url( pure_highlightjs_get_page_url() ) . '">' . esc_html__( 'Settings' , 'pure-highlightjs') . '</a>';
    }

    return $links;
}

add_action( 'wp_enqueue_scripts', 'pure_highlightjs_assets' );

function pure_highlightjs_assets() {
    wp_enqueue_style( 'pure-highlightjs-style', PURE_HIGHLIGHTJS_PLUGIN_URL . 'highlight/styles/' . pure_highlightjs_option('pure-highlightjs-theme', PURE_HIGHLIGHTJS_DEFAULT_THEME) . '.css', array(), '0.9.2' );
    wp_enqueue_style( 'pure-highlightjs-css', PURE_HIGHLIGHTJS_PLUGIN_URL . 'assets/pure-highlight.css', array(), '0.1.0' );
    wp_enqueue_script( 'pure-highlightjs-pack', PURE_HIGHLIGHTJS_PLUGIN_URL . 'highlight/highlight.pack.js', array(), '9.12.0', true );
//添加行号
	wp_enqueue_script( 'line-number-js', PURE_HIGHLIGHTJS_PLUGIN_URL . 'assets/line-number.js', array(), '0.1.8', true );
	wp_enqueue_style( 'line-number-css', PURE_HIGHLIGHTJS_PLUGIN_URL . 'assets/line-number.css', array(), '0.1.0' );

}

add_action( 'admin_enqueue_scripts', 'pure_highlightjs_admin_assets' );

function pure_highlightjs_admin_assets() {
    global $hook_suffix;

    if ( in_array( $hook_suffix, array(
            'index.php', # dashboard
            'post.php',
            'post-new.php',
            'settings_page_pure-highlightjs-config',
        ) ) ) {
        wp_enqueue_script( 'pure-highlightjs', PURE_HIGHLIGHTJS_PLUGIN_URL . 'assets/pure-highlight.js', array(), '0.1.0', true );
        wp_enqueue_script( 'pure-highlightjs-pack', PURE_HIGHLIGHTJS_PLUGIN_URL . 'highlight/highlight.pack.js', array(), '9.12.0', true );

        wp_localize_script( 'pure-highlightjs', 'PureHighlightjsTrans', array(
            'title' => __( "Code Insert", 'pure-highlightjs' ),
            'language' => __( "Language", 'pure-highlightjs' ),
            'code' => __( "Source Code", 'pure-highlightjs' ),
        ));
    }
}

// load tinyMCE pure-highlightjs plugin
add_filter('mce_external_plugins', 'pure_highlightjs_mce_plugin');

function pure_highlightjs_mce_plugin( $mce_plugins ) {
    $mce_plugins['purehighlightjs'] = PURE_HIGHLIGHTJS_PLUGIN_URL . 'tinymce/tinymce.js';
    return $mce_plugins;
}

add_filter( 'mce_css', 'pure_highlightjs_mce_css');

function pure_highlightjs_mce_css( $mce_css ) {
    if (! is_array($mce_css) ) {
        $mce_css = explode(',', $mce_css);
    }

    $mce_css[] = PURE_HIGHLIGHTJS_PLUGIN_URL . 'tinymce/tinymce.css';

    return implode( ',', $mce_css );
}

add_filter('mce_buttons', 'pure_highlightjs_mce_buttons', 101);

function pure_highlightjs_mce_buttons( $buttons ) {
    if (! in_array('PureHighlightjsInsert', $buttons) ){
        $buttons[] = 'PureHighlightjsInsert';
    }
    return $buttons;
}

add_action('admin_menu', 'pure_highlightjs_plugin_menu');

function pure_highlightjs_plugin_menu() {
    $hook = add_options_page( __('Pure highlightjs', 'pure-highlightjs'), __('Pure highlightjs', 'pure-highlightjs'), 'manage_options', 'pure-highlightjs-config', 'pure_highlightjs_settings_page' );
}

function pure_highlightjs_settings_page() {
    // include admin page
    include PURE_HIGHLIGHTJS_PLUGIN_DIR.'/views/settings.php';
}

function pure_highlightjs_get_style_list($theme = '') {
    $path = PURE_HIGHLIGHTJS_PLUGIN_DIR . 'highlight/styles';

    $themes = array();
    foreach (new DirectoryIterator($path) as $fileInfo) {
        if ($fileInfo->isDot() || ! $fileInfo->isFile()) {
            continue;
        }

        $filename = $fileInfo->getFilename();

        if ('.css' != substr($filename, -4)) {
            continue;
        }

        $themes[] = substr($filename, 0, - 4);;
    }

    sort($themes);

    return $themes;
}

//添加颜色选择器
add_action('admin_enqueue_scripts', 'line_color_setting_scripts');
function line_color_setting_scripts(){
	if( isset($_GET['page']) && $_GET['page'] == "pure-highlightjs-config" ){
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'line_color_setting', PURE_HIGHLIGHTJS_PLUGIN_URL . 'assets/line-color-setting.js', array( 'wp-color-picker' ), false, true );	
	}
}

function line_color_head_style(){?>
	<style type="text/css">
		.code-with-line-number li:hover{
			background-color: <?php echo line_color_get_setting('hover-color');?>!important
		}
		.code-with-line-number li.mark {
			background-color: <?php echo line_color_get_setting('mark-color');?>!important
		}
	</style>
<?php }
add_action( 'wp_head', 'line_color_head_style' );

/**
	 * 获取设置
	 * @return [array]
	 */
	function line_color_get_setting($key=NULL){
		$setting = get_option('line_color_setting');
		return $key ? $setting[$key] : $setting;
	}

	/**
	 * 删除设置
	 * @return [void]
	 */
	function line_color_delete_setting(){
		delete_option('line_color_setting');
	}

	/**
	 * [wpzan_setting_key description]
	 * @param  [type] $key [description]
	 * @return [type]      [description]
	 */
	function line_color_setting_key($key){
		if( $key ){
			return "line_color_setting[$key]";
		}

		return false;
	}

	/**
	 * 升级设置
	 * @param  [array] $setting
	 * @return [void]
	 */
	function line_color_update_setting($setting){
		update_option('line_color_setting', $setting);
	}	
	
function pure_highlightjs_get_page_url() {
    $args = array( 'page' => 'pure-highlightjs-config' );
    return add_query_arg( $args, admin_url( 'options-general.php') ); 
}

function pure_highlightjs_update_option() {
    if ( isset( $_POST['formaction'] ) && $_POST['formaction'] == 'update-pure-highlightjs' ) {
        // do nothing
    }
}

function pure_highlightjs_option($key, $default = null) {
    $option = get_option($key);

    return !empty($option) ? $option : $default;
}
