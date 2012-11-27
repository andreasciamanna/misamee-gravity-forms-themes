<?php
if (!class_exists('misamee_themed_form_widget')) {
	require_once 'misamee_themed_form_widget.php';
}

class theme_data
{
	public $type;
	public $key;
	public $file;
	public $themeUrl;
	public $deps;
}

class misamee_themed_form
{
	//Class Functions
	public static function init()
	{
		$class = __CLASS__;
		new $class;
	}

	public $theme;

	/**
	 * PHP 5 Constructor
	 */
	public function __construct()
	{
		$this->theme = array();

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

		/** @var $id int */
		$themeName = $this->misamee_themed_form_setTemplate($themename, $id);

		$additionalClasses = ($themeName != '') ? " class=\"themed_form " . $themeName . "\"" : "";

		if(count($this->theme)!=0) {
			add_action('wp_footer', array(&$this, 'enqueue_scripts'));
			add_action('wp_footer', array(&$this, 'enqueue_styles'));
			add_action('wp_footer', array(&$this, 'enqueue_php'));
		}
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
		
		//echo '<pre>misamee_themed_form_setTemplate</pre>';
		$themeName = strtolower($themeName);
		if ($themeName != "none") {
			if ($themeName == 'default') {
				$themeData = new theme_data();
				$themeData->type = 'style';
				$themeData->key = 'misamee-themed-form-css';
				$themeData->file = $theme['url'] . 'css/misamee.themed.form.css';
				$this->theme[$themeName][] = $themeData;

				$themeData = new theme_data();
				$themeData->type = 'script';
				$themeData->key = 'tooltipsy';
				$themeData->file = $theme['url'] . 'js/tooltipsy.source.js';
				$themeData->deps = array('jquery');
				$this->theme[$themeName][] = $themeData;

				$themeData = new theme_data();
				$themeData->type = 'script';
				$themeData->key = 'misamee-themed-form-js';
				$themeData->file = $theme['url'] . 'js/misamee.themed.form.js';
				$themeData->deps = array('jquery');
				$this->theme[$themeName][] = $themeData;
			} elseif (is_dir($theme['dir'])) {
				//get all files in specified directory
				$files = glob($theme['dir'] . "/*.*");

				foreach ($files as $file) {
					$fileData = pathinfo($file);
					switch ($fileData['extension']) {
						case 'css':
							$themeData = new theme_data();
							$themeData->type = 'style';
							$themeData->key = $fileData['filename'];
							$themeData->file = $theme['url'] . $fileData['basename'];
							$themeData->themeUrl = $theme['url'];
							$this->theme[$themeName][] = $themeData;
							break;
						case 'js':
							$themeData = new theme_data();
							$themeData->type = 'script';
							$themeData->key = $fileData['filename'];
							$themeData->file = $theme['url'] . $fileData['basename'];
							$themeData->themeUrl = $theme['url'];
							$this->theme[$themeName][] = $themeData;
							break;
						case 'php':
							$themeData = new theme_data();
							$themeData->type = 'php';
							$themeData->key = $fileData['filename'];
							$themeData->file = $theme['dir'] . '/' . $fileData['basename'];
							$themeData->themeUrl = $theme['url'];
							$this->theme[$themeName][] = $themeData;
							break;
					}
				}
			}
			add_action("gform_field_css_class", array(&$this, "add_custom_class"), 10, 3);
		}

		//echo '<pre>' . print_r($this->theme, true) . '</pre>';
		return $themeName;
	}

	function enqueue_scripts()
	{
		$type = 'script';
		foreach ($this->theme as $theme) {
			if (count($theme) > 0) {
				foreach ($theme as $themeData) {
					if ($themeData->type == $type) {
						wp_enqueue_script($themeData->key, $themeData->file, $themeData->deps, false, true);
					}
				}
			}
		}
	}

	function enqueue_styles()
	{
		$type = 'style';
		foreach ($this->theme as $theme) {
			if (count($theme) > 0) {
				foreach ($theme as $themeData) {
					if ($themeData->type == $type) {
						wp_enqueue_style($themeData->key, $themeData->file, $themeData->deps);
					}
				}
			}
		}
	}

	function enqueue_php()
	{
		$type = 'php';
		foreach ($this->theme as $themeName=>$theme) {
			if (count($theme) > 0) {
				foreach ($theme as $themeData) {
					if ($themeData->type == $type) {
						include($themeData->file);
					}
				}
			}
		}
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
