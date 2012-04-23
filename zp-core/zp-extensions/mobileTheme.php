<?php
/**
 *
 * Mobile devices are detected with {@link http://jquerymobile.com/gbs/ jquery Mobile }
 * A particular theme may be designated for <i>phones</i> and for <i>tablets</i>. If the connecting
 * device is one of those, the theme will automatically switch to the designated mobile theme.
 *
 * Test mode allows you to run your standard desktop client but simulate being either a <i>phone</i> or
 * a <i>tablet</i>.
 *
 * You may place a call on <var>mobileTheme::controlLink();</var> in your theme(s) to allow the client viewer
 * to override the switch and view your standard gallery theme. If the same call is placed in your gallery
 * theme he will be able to switch back as well. <b>NOTE:</b> This link is present only when the browing client
 * is a mobile device!
 *
 * @package plugins
 */

$plugin_is_filter = 5|CLASS_PLUGIN;
$plugin_description = gettext('Select your theme based on the device connecting to your site');
$plugin_author = "Stephen Billard (sbillard)";

$option_interface = 'mobileTheme';

class mobileTheme {

	function __construct() {
	}

	function getOptionsSupported() {
		global $_zp_gallery;
		$themes = array();
		foreach ($_zp_gallery->getThemes() as $theme=>$details) {
			$themes[$details['name']] = $theme;
		}
		$options = array(gettext('Phone theme') => array('key' => 'mobileTheme_phone', 'type' => OPTION_TYPE_SELECTOR,
																				'selections'=>$themes,
																				'null_selection' => gettext('gallery theme'),
																				'desc' => gettext('Select the theme to be used when a phone device connects.')),
															gettext('Tablet theme') => array('key' => 'mobileTheme_tablet', 'type' => OPTION_TYPE_SELECTOR,
																				'selections'=>$themes,
																				'null_selection' => gettext('gallery theme'),
																				'desc' => gettext('Select the theme to be used when a tablet device connects.')),
															gettext('Test mode') => array('key' => 'mobileTheme_test', 'type' => OPTION_TYPE_SELECTOR,
																				'selections'=>array(gettext('Phone')=>'phone',gettext('Tablet')=>'tablet'),
																				'null_selection' => gettext('live'),
																				'desc' => gettext('Put the plugin in <em>test mode</em> and it will simulate the selected device. If <em>live</em> is selected operations are normal.'))
		);
		return $options;
	}

	function handleOption($option, $currentValue) {
	}

	static function theme($theme) {
		global $_zp_gallery;
		$detect = new mobile();
		if ($detect->isMobile()) {
			if ($detect->isTablet()) {
				$new = getOption('mobileTheme_tablet');
			} else {
				$new = getOption('mobileTheme_phone');
			}
		} else {
			$new = false;
		}
		if ($new) {
			if (array_key_exists($new, $_zp_gallery->getThemes())) {
				$theme = $new;
			}
		}
		return $theme;
	}

	static function controlLink() {
		$detect = new mobile();
		if ($detect->isMobile()) {
			if (zp_getCookie('mobileTheme_disable')) {
				$text = gettext('View the mobile gallery');
				$enable = 'on';
			} else {
				$enable = 'off';
				$text = gettext('View the normal gallery');
			}
		}
		?>
		<div class="mobileThemeControlLink">
			<a href="?mobileTheme=<?php echo $enable; ?>" rel="external">
				<?php echo $text; ?>
			</a>
		</div>
		<?php
	}

}

require_once(SERVERPATH.'/'.ZENFOLDER.'/'.PLUGIN_FOLDER.'/mobileTheme/Mobile_Detect.php');

class mobile extends Mobile_Detect {

	protected $devicelist;

	function __construct() {
		parent::__construct();
    $this->devicelist = array_merge(
																		$this->phoneDevices,
																		$this->tabletDevices
    																);
	}

	public function getOS()  {
		foreach($this->operatingSystems as $_key => $_regex) {
			if(preg_match('/'.$_regex.'/is', $this->userAgent)) {
				return $_key;
			}
		}
		return false;
	}

	public function getDevice()  {
		foreach($this->devicelist as $_key => $_regex) {
			if(preg_match('/'.$_regex.'/is', $this->userAgent)) {
				return $_key;
			}
		}
		return false;
	}

	function isMobile() {
		if (getOption('mobileTheme_test')) {
			return true;
		}
		return parent::isMobile();
	}

	function isTablet() {
		if (getOption('mobileTheme_test')=='tablet') {
			return true;
		}
		return parent::isTablet();
	}

}
if (isset($_GET['mobileTheme'])) {
	switch (sanitize($_GET['mobileTheme'])) {
		case 'on':
			zp_setCookie('mobileTheme_disable',0);
			break;
		case 'off':
			zp_setCookie('mobileTheme_disable',1);
			break;
	}
}

if (!zp_getCookie('mobileTheme_disable')) {
	zp_register_filter('setupTheme', 'mobileTheme::theme');
}

?>