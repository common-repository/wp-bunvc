<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.coffee-break-designs.com/production/wp-bunvc/
 * @since      1.0.0
 *
 * @package    Wp_bunvc
 * @subpackage Wp_bunvc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_bunvc
 * @subpackage Wp_bunvc/admin
 * @author     coffee break designs <wada@coffee-break-designs.com>
 */
class Wp_bunvc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * オプション
	 */
	private $wp_bunvc_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', array( $this, 'wp_bunvc_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'wp_bunvc_page_init' ) );

	}

	/**
	 * オプションページ メニュー登録
	 * @since    1.0.0
	 */
	public function wp_bunvc_add_plugin_page() {
		add_options_page(
			'WP BunVC', // page_title
			'WP BunVC', // menu_title
			'manage_options', // capability
			'wp-bunvc', // menu_slug
			array( $this, 'wp_bunvc_create_admin_page' ) // function
		);
	}

	/**
	 * オプションページレイアウト
	 * @since    1.0.0
	 */
	public function wp_bunvc_create_admin_page() {
		$this->wp_bunvc_options = get_option( 'wp_bunvc_options' ); ?>
		<div class="wrap">
			<h2>WP BunVC</h2>
			<p>WP BunVC Option</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp_bunvc_option_group' );
				do_settings_sections( 'wp-bunvc-admin' );
				submit_button();
				?>
			</form>
			<a href="https://www.coffee-break-designs.com/production/wp-bunvc/" target="_blank" rel="nofollow noopener noreferrer"><?php _e("How to use", "wp-bunvc") ?></a>
		</div>
	<?php
	}
	/**
	 * オプションページ 登録
	 * @since    1.0.0
	 */
	public function wp_bunvc_page_init() {
		register_setting(
				'wp_bunvc_option_group', // option_group
				'wp_bunvc_options', // option_name
				array( $this, 'wp_bunvc_sanitize' ) // sanitize_callback
			);

		add_settings_section(
				'wp_bunvc_setting_section', // id
				__('Settings', "wp-bunvc"), // title
				array( $this, 'wp_bunvc_section_info' ), // callback
				'wp-bunvc-admin' // page
			);

		add_settings_field(
				'deve_link', // id
				__('Would you like to display the developer support link?', "wp-bunvc"), // title
				array( $this, 'deve_link_callback' ), // callback
				'wp-bunvc-admin', // page
				'wp_bunvc_setting_section' // section
			);

		add_settings_field(
			'default_button_text', // id
			__("Default character string of button's character", "wp-bunvc"), // title
			array( $this, 'default_button_text_callback' ), // callback
			'wp-bunvc-admin', // page
			'wp_bunvc_setting_section' // section
		);
	}

	/**
	 * バリデーション
	 * @param  Array $input 入力
	 * @return Array        バリデーションされた値
	 */
	public function wp_bunvc_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['deve_link'] ) ) {
			$sanitary_values['deve_link'] = $input['deve_link'];
		}
		if ( isset( $input['default_button_text'] ) ) {
			$sanitary_values['default_button_text'] = sanitize_text_field( $input['default_button_text'] );
		}

		return $sanitary_values;
	}

	/**
	 * インフォ
	 */
	public function wp_bunvc_section_info() {
	}

	/**
	 * 開発者のリンクの出力
	 * @return echo
	 */
	public function deve_link_callback() {
		?>
		<a href="<?php echo(plugin_dir_url( __FILE__ ) . 'img/donate_link.png') ?>" target="_blank">
		<img src="<?php echo(plugin_dir_url( __FILE__ ) . 'img/donate_link.png') ?>" width="250"></a>

		<fieldset>
			<?php
				$checked = ( isset( $this->wp_bunvc_options['deve_link'] ) && $this->wp_bunvc_options['deve_link'] === '1' || !isset($this->wp_bunvc_options['deve_link'])) ? 'checked' : '' ;
			?>
			<label for="deve_link-0">
				<input type="radio" name="wp_bunvc_options[deve_link]" id="deve_link-0" value="1" <?php echo $checked; ?>> <?php _e("Yes"); ?>
			</label>
			<br>
			<?php
				$checked = ( isset( $this->wp_bunvc_options['deve_link'] ) && $this->wp_bunvc_options['deve_link'] === '0' ) ? 'checked' : '' ; ?>
			<label for="deve_link-1">
				<input type="radio" name="wp_bunvc_options[deve_link]" id="deve_link-1" value="0" <?php echo $checked; ?>> <?php _e("No"); ?></label>
		</fieldset>

		<?php
	}
	public function default_button_text_callback() {
		$text = isset( $this->wp_bunvc_options['default_button_text'] ) ? esc_attr( $this->wp_bunvc_options['default_button_text']) : '';
		$text = ($text == '')? "Donate": $text;
		printf(
			'<input class="regular-text" type="text" name="wp_bunvc_options[default_button_text]" id="default_button_text" value="%s">', $text
		);
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_bunvc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_bunvc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/Wp_bunvc-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_bunvc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_bunvc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/Wp_bunvc-admin.js', array( 'jquery' ), $this->version, false );

	}

}
