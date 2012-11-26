<?php
if (!class_exists('misamee_themed_form_widget')) {
	require_once 'misamee_themed_form_widget.php';
}

class misamee_themed_form
{
	//Class Functions
	public static function init()
	{
		$class = __CLASS__;
		new $class;
	}

	/**
	 * PHP 5 Constructor
	 */
	public function __construct()
	{
		add_filter("gform_shortcode_theme", array(&$this, "misamee_themed_form_theme"), 10, 3);
		add_action('widgets_init', array(&$this, "misamee_themed_form_register_widget"));
	}

	function misamee_themed_form_register_widget()
	{
		register_widget('misamee_themed_form_widget');
	}

	function misamee_themed_form_theme($string, $attributes, $content)
	{
		extract(shortcode_atts(array(
			'title' => true,
			'description' => true,
			'id' => 0,
			'name' => '',
			'field_values' => "",
			'ajax' => false,
			'tabindex' => 1,
			'action' => 'form',
			'themename' => '',
			'cssclass' => ''
		), $attributes));

		/** @var $themename string */
		/** @var $selectedTheme string */
		$selectedTheme = $themename;

		/** @var $id int */
		$theme = $this->misamee_themed_form_setTemplate($selectedTheme, $id);

		$additionalClasses = ($theme['name'] != '') ? " class=\"themed_form $theme[name]\"" : "";

		//$newString = str_replace('action="prettify"', 'action="form"', $string);

		$attributes['action'] = 'form';

		$formString = RGForms::parse_shortcode($attributes, $content = null);
		//$formString = do_shortcode($newString);

		if ($additionalClasses != '') {
			return "<div$additionalClasses>$formString</div>";
		}
		return $formString;
	}

	private function misamee_themed_form_setTemplate($themeName, $formId)
	{
		$theme = $this->misamee_themed_form_getThemeByName($themeName);
		//echo "<pre>";
		if (strtolower($theme['name']) != "none") {
			if ($theme['name'] == 'default') {
				wp_enqueue_style('misamee-themed-form-css', $theme['url'] . 'css/misamee.themed.form.css');
				wp_enqueue_script('tooltipsy', $theme['url'] . 'js/tooltipsy.source.js', array('jquery'));
				wp_enqueue_script('misamee-themed-form-js', $theme['url'] . 'js/misamee.themed.form.js', array('jquery'));
			} elseif (is_dir($theme['dir'])) {
				//get all files in specified directory
				$files = glob($theme['dir'] . "/*.*");

				foreach ($files as $file) {
					$fileData = pathinfo($file);
					switch ($fileData['extension']) {
						case 'css':
							wp_enqueue_style($fileData['filename'], $theme['url'] . $fileData['basename']);
							//echo "wp_enqueue_style('$fileData[filename]', '$theme[url]$fileData[basename]')\n";
							break;
						case 'js':
							wp_enqueue_script($fileData['filename'], $theme['url'] . $fileData['basename']);
							//echo "wp_enqueue_script('$fileData[filename]', '$theme[url]$fileData[basename]')\n";
							break;
						case 'php':
							include($theme['dir'] . '/' . $fileData['basename']);
							//echo "include($theme[dir]/$fileData[basename])\n";
							break;
					}
				}
			}
			add_action("gform_field_css_class", array(&$this, "add_custom_class"), 10, 3);
		}
		return $theme;
	}

	public function add_custom_class($classes, $field, $form)
	{
		$classes .= " misamee_themed_$field[type]";
		return $classes;
	}

	private function misamee_themed_form_getThemeByName($themeName)
	{
		switch (strtolower($themeName)) {
			case 'none':
				$theme = 'None';
				$themeDir = '';
				break;
			case 'default':
				$theme = $themeName;
				$themeDir = misamee_gf_themes::getPluginPath();
				$themeUrl = misamee_gf_themes::getPluginUrl();
				break;
			default:
				$themes = $this->misamee_themed_form_themes();
//            echo '<pre>' . $themeName;
//            print_r($themes);
//            echo '</pre>';
				if (array_key_exists($themeName, $themes)) {
					$theme = $themeName;
					$themeDir = WP_CONTENT_DIR . $themes[$theme]['dir'];
					$themeUrl = $themes[$theme]['url'];
				} elseif ($themeName == 'Default') {
					$theme = $themeName;
					$themeDir = misamee_gf_themes::getPluginPath();
					$themeUrl = misamee_gf_themes::getPluginUrl();
				}
		}

		/** @var $theme string */
		/** @var $themeDir string */
		/** @var $themeUrl string */
		return array(
			'name' => $theme,
			'dir' => str_replace("\\", "/", $themeDir),
			'url' => $themeUrl
		);
	}

	public static function misamee_themed_form_themes()
	{
		$themes = array();
		if (is_dir(WP_CONTENT_DIR . '/mgft-themes')) {
			//get all files in specified directory
			$files = glob(WP_CONTENT_DIR . '/mgft-themes/*', GLOB_ONLYDIR);
			ksort($files, SORT_STRING);
//            echo '<pre>';
//            echo '{' . misamee_gf_themes::getPluginPath() . 'themes/*,' . WP_CONTENT_DIR . '/mgft-themes/*}' . "\n";
//            print_r($files);
//            echo '</pre>';
			$themes['none'] = array(
				'dir' => '',
				'url' => ''
			);
			$themes['default'] = array(
				'dir' => '',
				'url' => ''
			);

			if (is_array($files) && count($files) > 0) {
				foreach ($files as $file) {
					if (is_dir($file)) {
						$themePath = substr($file, strlen(WP_CONTENT_DIR));
						$folderArray = explode('/', $themePath);
						//print_r($folderArray);
						$themeName = $folderArray[count($folderArray) - 1];
						$themeUrl = WP_CONTENT_URL . '/mgft-themes/' . $themeName . "/";
						$themes[$themeName] = array(
							'dir' => $themePath,
							'url' => $themeUrl
						);
					}
				}
			}
		}
		if (is_dir(misamee_gf_themes::getPluginPath() . 'themes')) {
			//get all files in specified directory
			$files = glob(misamee_gf_themes::getPluginPath() . 'themes/*', GLOB_ONLYDIR);
			ksort($files, SORT_STRING);
//            echo '<pre>';
//            echo '{' . misamee_gf_themes::getPluginPath() . 'themes/*,' . WP_CONTENT_DIR . '/mgft-themes/*}' . "\n";
//            print_r($files);
//            echo '</pre>';
			$themes['none'] = array(
				'dir' => '',
				'url' => ''
			);
			$themes['default'] = array(
				'dir' => '',
				'url' => ''
			);

			if (is_array($files) && count($files) > 0) {
				foreach ($files as $file) {
					if (is_dir($file)) {
						$themePath = substr($file, strlen(WP_CONTENT_DIR));
						$folderArray = explode('/', $themePath);
						//print_r($folderArray);
						$themeName = $folderArray[count($folderArray) - 1];
						$themeUrl = misamee_gf_themes::getPluginUrl() . 'themes/' . $themeName . "/";
						$themes[$themeName] = array(
							'dir' => $themePath,
							'url' => $themeUrl
						);
					}
				}
			}
		}
		return $themes;
	}

}
