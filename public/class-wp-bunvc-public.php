<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.coffee-break-designs.com/production/wp-bunvc/
 * @since      1.0.0
 *
 * @package    Wp_bunvc
 * @subpackage Wp_bunvc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_bunvc
 * @subpackage Wp_bunvc/public
 * @author     coffee break designs <wada@coffee-break-designs.com>
 */
class Wp_bunvc_Public {
	const BUNVC_EXCHANGE_CACHE = "wp-bunvc-exchange-cache";

	const BUNVC_CACHE_TIME = 60;
	private $plugin_name;
	private $version;
	private $config;
	/**
	 * オプション
	 */
	private $wp_bunvc_options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $config ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->config = $config;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if(wp_script_is('vue')){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bunvc-autorun.js', array( 'vue' ), $this->version, true );
		}
		else {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bunvc-autorun-allpack.js', NULL , $this->version, true );
		}
	}
	/**
	 * ショートコードの出力
	 * @since    1.0.0
	 * @param  object $atts パラメータ
	 * @return string       HTML
	 */
	public function shortcode_bun($atts){
		$this->wp_bunvc_options = get_option( 'wp_bunvc_options' );

		$ajaxurl = admin_url( 'admin-ajax.php');
		extract(shortcode_atts(array(
			'text' => "",
			'btc_address' => "",
			'eth_address' => "",
			'bch_address' => "",
			'xem_address' => "",
			'mona_address' => "",
			'default_coin' => "Bitcoin",
			'default_base' => "Yen",
			'default_base_amount' => "1000",
		), $atts));
		$html = "<div class='bunvc'><bunvc-button ";
		$html .= " wordpress_ajaxurl='$ajaxurl'";
		if($text != ""){ $html .= " text='$text'"; }
		else {
			if(isset($this->wp_bunvc_options['default_button_text']) && $this->wp_bunvc_options['default_button_text'] != ""){
				$_text = $this->wp_bunvc_options['default_button_text'];
				$html .= " text='$_text'";
			}
		}
		if($btc_address != ""){ $html .= " btc_address='$btc_address'"; }
		if($eth_address != ""){ $html .= " eth_address='$eth_address'"; }
		if($bch_address != ""){ $html .= " bch_address='$bch_address'"; }
		if($xem_address != ""){ $html .= " xem_address='$xem_address'"; }
		if($mona_address != ""){ $html .= " mona_address='$mona_address'"; }

		if($default_coin != "Bitcoin"){ $html .= " default_coin='$default_coin'"; }
		if($default_base != "Yen"){ $html .= " default_base='$default_base'"; }
		if($default_base_amount != "1000"){ $html .= " default_base_amount='$default_base_amount'"; }
		// 開発者のリンク
		$deve_link = ( isset( $this->wp_bunvc_options['deve_link'] ) && $this->wp_bunvc_options['deve_link'] === '1' || !isset($this->wp_bunvc_options['deve_link'])) ? 'true' : 'false' ;
		$html .= " :deve_link='$deve_link'";
		$html .= " /></div>";

		return $html;
	}

	/**
	 * 取引所のTickerからrateを取得
	 * @since    1.0.0
	 * @param  object $atts パラメータ
	 * @return json       JSON
	 */
	public function bunvc_exchange($atts){
		session_write_close();
		header('X-FRAME-OPTIONS: SAMEORIGIN');

		// 返り値
		$return_obj = array();
		$errors = array();
		$timezone = new DateTimeZone(get_option('timezone_string'));
		// パラメータの取得
		foreach (
			array(
				'name',
				'pair',
				) as $v) {
			$$v = (string)filter_input(INPUT_POST, $v, FILTER_DEFAULT, FILTER_FLAG_STRIP_LOW);
		}
		$rate = 0;

		if (array_search($name, $this->config['exchanges']) === false) {
			echo json_encode( array(
				'errors' => array(
					'message' => "error"
				),
			) );
			die();
		}

		$exchange_cache = $this->get_exchange_cache($name);
		// キャッシュがある場合、時間を比較する
		$time_start = microtime(true);
		$active_cash = false;
		if(
			isset($exchange_cache[$pair])
			&& isset($exchange_cache[$pair]['datetime'])
			){
			$date_time = new DateTime($exchange_cache[$pair]['datetime'], $timezone);
			$current_time = new DateTime('', $timezone);
			$diff = $date_time->diff($current_time);
			$s = $current_time->getTimestamp() - $date_time->getTimestamp();
			// 設定時間より短かったら、キャッシュ使う
			if(self::BUNVC_CACHE_TIME > (int) $s ){
				$active_cash = true;
				$rate = $exchange_cache[$pair]['rate'];
			}
		}

		// キャッシュが無い若しくは古い場合、取得しにいく
		if($active_cash == false){
			switch ($name) {
				case 'coincheck':
					$pair_i = array_search($pair, array("btc_jpy", "eth_jpy", "etc_jpy", "dao_jpy", "lsk_jpy", "fct_jpy", "xmr_jpy", "rep_jpy", "xrp_jpy", "zec_jpy", "xem_jpy", "ltc_jpy", "dash_jpy", "bch_jpy", "eth_btc", "etc_btc", "lsk_btc", "fct_btc", "xmr_btc", "rep_btc", "xrp_btc", "zec_btc", "xem_btc", "ltc_btc", "dash_btc", "bch_btc") );
					if($pair_i === false) die();

					$json_string = mb_convert_encoding( file_get_contents("https://coincheck.com/api/rate/$pair"), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
					$arr = json_decode($json_string, true);
					$rate = (float) $arr['rate'];
					break;
				case 'bitflyer':
					$pair_i = array_search($pair, array("btc_jpy", "eth_btc", "bch_btc") );
					if($pair_i === false) die();

					$pair_upper = strtoupper($pair);

					$json_string = mb_convert_encoding( file_get_contents("https://api.bitflyer.jp/v1/ticker/?product_code=$pair_upper"), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
					$arr = json_decode($json_string, true);
					$rate = (float) $arr['best_ask'];
					break;

				case 'bitbank':
					$pair_i = array_search($pair, array("btc_jpy", "xrp_jpy", "ltc_btc", "eth_btc", "mona_jpy", "mona_btc", "bch_jpy", "bch_btc") );
					if($pair_i === false) die();

					// BCHの呼び方が違う
					$_pair = str_replace("bch", "bcc", $pair);

					$json_string = mb_convert_encoding( file_get_contents("https://public.bitbank.cc/$_pair/ticker"), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
					$arr = json_decode($json_string, true);
					$rate = (float) $arr['data']['buy'];
					break;
				case 'zaif':
					$pair_i = array_search($pair, array("btc_jpy", "xem_btc", "bch_jpy", "fscc_btc", "sjcx_jpy", "bitcrystals_jpy", "xcp_jpy", "zaif_jpy", "mona_jpy", "jpyz_jpy", "eth_btc", "bch_btc", "cicc_jpy", "pepecash_jpy", "pepecash_btc", "xem_jpy", "ncxc_btc", "eth_jpy", "mona_btc", "sjcx_btc", "xcp_btc", "fscc_jpy", "zaif_btc", "cicc_btc", "ncxc_jpy", "bitcrystals_btc") );
					if($pair_i === false) die();

					$json_string = mb_convert_encoding( file_get_contents("https://api.zaif.jp/api/1/ticker/$pair"), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
					$arr = json_decode($json_string, true);
					$rate = (float) $arr['ask'];
					break;

				case 'kraken':
					$pair_i = array_search($pair, array("btc_jpy", "eth_jpy") );
					if($pair_i === false) die();

					// 呼び方が違う
					$_pair = str_replace("jpy", "ZJPY", $pair);
					$_pair = str_replace("btc", "XXBT", $_pair);
					$_pair = str_replace("eth", "XETH", $_pair);
					$_pair = str_replace("_", "", $_pair);

					$json_string = mb_convert_encoding( file_get_contents("https://api.kraken.com/0/public/Ticker?pair=$_pair"), 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
					$arr = json_decode($json_string, true);
					$rate = (float) $arr['result'][$_pair]["a"][0];
					// $rate = $arr['result'];
					break;

				default:
					array_push($errors, "そんな取引所は取り扱ってない");
					break;
			}
			// 成功ならキャッシュする
			if(count($errors) == 0 && $rate > 0){
				$_df = new DateTime('', $timezone);
				$_obj = array(
					'name' => $name,
					'pair' => $pair,
					'rate' => $rate,
					'datetime' => $_df->format('Y-m-d H:i:s')
				);
				$this->update_exchange_chache($exchange_cache, $_obj);
			}
		}
		echo json_encode( array(
			'name' => $name,
			'pair' => $pair,
			'rate' => $rate,
			'errors' => $errors,
			'cash' => $active_cash,
			// 'time' => (microtime(true) - $time_start)
		) );
		// error_log("class-wp-bunvc-public:". (microtime(true) - $time_start) . "s");
		die();
	}
	// キャッシュ
	private function init_exchange_cache() {
		return array();
	}
	private function get_exchange_cache_name($name) {
		return self::BUNVC_EXCHANGE_CACHE . "_" . $name;
	}
	private function get_exchange_cache($name) {
		$exchange_cache = get_option( $this->get_exchange_cache_name($name), '' );
		if ( empty( $exchange_cache ) ) {
			$exchange_cache = $this->init_exchange_cache();
			add_option( $this->get_exchange_cache_name($name), $exchange_cache, '', 'no' );
		}
		return $exchange_cache;
	}
	private function update_exchange_chache( $exchange_cache, $add_obj ){
		$exchange_cache[ $add_obj["pair"] ] = $add_obj;
		update_option( $this->get_exchange_cache_name($add_obj["name"]), $exchange_cache );
	}
	private function delete_exchange_chache($name) {
		delete_option( $this->get_exchange_cache_name($name) );
	}
	public function delete_exchange_chache_all(){
		foreach ($this->config['exchanges'] as $key => $value) {
			$this->delete_exchange_chache($value);
		}
	}

}

