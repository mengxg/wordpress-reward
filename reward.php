<?php
/**
 * @package Reward(打赏插件)
 * @version 1.0
 */
/*
Plugin Name: 打赏插件
Plugin URI: https://github.com/mengxg/wordpress-reward
Description: 如果读者觉得这篇文章对你有帮助，启发了你的思路，可以自动在文章末尾添加支付宝、微信扫码进行打赏赞助！
Author: mengxg
Version: 1.0
Author URI: http://www.mengxg.top
*/

class Reward
{
    public function __Construct()
    {
        add_filter('the_content', array($this,'add_pay'));
        add_action('admin_menu', array($this,'reward_add_pages'));
        add_filter('plugin_action_links', array($this,'wechat_reward_plugin_setting'), 10, 2);
    }

    /**
     * 加载js和css
     */
    public function load()
    {
        //在jqeury之后加载js文件
        wp_register_script('reward', plugins_url( '/static/reward.js', __FILE__ ));
        wp_enqueue_script('reward');

        wp_register_style('reward', plugins_url( '/static/reward.css', __FILE__ ));

        //确保在底部加载css样式，覆盖主题的样式
        add_action('wp_footer',array($this,'add_css'));
    }

    public function add_css()
    {
        wp_enqueue_style( 'reward');
    }

    //在文章末尾添加打赏图标
    public function add_pay($content)
    {
		$qr_wechat=get_option('reward_wechat_qrpic');
		$qr_alipay=get_option('reward_alipay_qrpic');
        $qr_wechat = $qr_wechat ? $qr_wechat : plugins_url( '/static/wechat.jpg', __FILE__ );
		$qr_alipay = $qr_alipay ? $qr_alipay : plugins_url( '/static/alipay.jpg', __FILE__ );
        $content_pay = <<<PAY
        <div class="reward">
			<div class="reward-button">赏 
				<span class="reward-code"> 
					<span class="alipay-code"> 
					<img class="alipay-img wdp-appear" src="{$qr_alipay}"><b>支付宝打赏</b> 
					</span> 
					<span class="wechat-code"> 
					<img class="wechat-img wdp-appear" src="{$qr_wechat}"><b>微信打赏</b> 
					</span> 
				</span>
			</div>
			<p>如果文章对你有帮助，欢迎点击上方按钮打赏作者</p>
		</div>
PAY;
        $this->load();
        $content .= $content_pay;
        return $content;
    }

    //设置link
    public function wechat_reward_plugin_setting( $links, $file )
    {
        if($file == 'reward/reward.php'){
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=upload_reward' ) . '">' . __('Settings') . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }

    //打赏二维码设置菜单
    function reward_add_pages() {
        add_options_page( '打赏二维码', '打赏二维码', 'manage_options', 'upload_reward', array($this,'upload_reward'));
    }

    //管理页面
    public function upload_reward()
    {
        if(isset($_POST['submit']) && $_SERVER['REQUEST_METHOD']=='POST'){
            update_option('reward_wechat_qrpic',$_POST['wechat_pic'] ? $_POST['wechat_pic'] : '');
			update_option('reward_alipay_qrpic',$_POST['alipay_pic'] ? $_POST['alipay_pic'] : '');
            $this->upload_success();
        }
        $wechat_pic = get_option('wechat-reward-QR-pic');
		$alipay_pic = get_option('reward_alipay_qrpic');
		if(function_exists( 'wp_enqueue_media' )){
			wp_enqueue_media();
		}else{
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}
?>
    <div class="wrap">
        <h2>设置打赏二维码</h2>
        <p>
            请先通过手机微信、支付宝获取收款二维码，最好对二维码进行截取，二维码尺寸大小最好:200px*200px
        </p>
		<p>
            怎么获取微信、支付宝的收款二维码？这个就不用我来教您了，自己研究吧。哈哈~~</p>
			<p>如果有什么问题可以联系mxgsa@vip.qq.com!
        </p>
        <form action="<?= admin_url( 'options-general.php?page=upload_reward' ) ?>" name="settings-pay" method="post">
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="wechat">微信支付二维码URL</label></th>
                    <td><input type="text" class="regular-text code" value="<?= $wechat_pic ?>" id="wechat" name="wechat_pic">
					<input id="upload_image_wechat" type="button" style="width:auto;" value="上传" /></td>
                </tr>
				<tr>
                    <th><label for="alipay">支付宝支付二维码URL</label></th>
                    <td><input type="text" class="regular-text code" value="<?= $alipay_pic ?>" id="alipay" name="alipay_pic">
					<input id="upload_image_alipay" type="button" style="width:auto;" value="上传" /></td>
                </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" value="保存更改" class="button button-primary" id="submit" name="submit"></p>
        </form>
		<script>
		jQuery(document).ready(function() {
			jQuery('#upload_image_wechat').click(function(e) {
				e.preventDefault();
				var custom_uploader = wp.media({
					title: '媒体库',
					button: {
						text: '选择该图片'
					},
					multiple: false  // Set this to true to allow multiple files to be selected
				})
				.on('select', function() {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					jQuery('#wechat').val(attachment.url);					
				})
				.open();
			});
			jQuery('#upload_image_alipay').click(function(e) {
				e.preventDefault();
				var custom_uploader = wp.media({
					title: '媒体库',
					button: {
						text: '选择该图片'
					},
					multiple: false  // Set this to true to allow multiple files to be selected
				})
				.on('select', function() {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					jQuery('#alipay').val(attachment.url);					
				})
				.open();
			});
		});
		</script>
    </div>
<?php
    }

    //保存成功提示
    public function upload_success()
    {
        echo '<div class="updated "><p>更新成功！打开一篇文章页看看效果吧~~</p></div>';
    }
}

new Reward;

//打赏挂件
class Reward_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'REWARDWIDGET', // Base ID
            '文章打赏插件', // Name
            array( 'description' => '给博客文章增加微信支付宝打赏插件' )
        );
    }

    //前台显示
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }        
        echo $args['after_widget'];
    }

    //后台小工具设置
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    //更新设置
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }
}

//注册打赏挂件
function register_Reward_widget() {
    register_widget( 'Reward_Widget' );
}
add_action( 'widgets_init', 'register_WR_widget' );