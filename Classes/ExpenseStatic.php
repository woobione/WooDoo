<?php

class WooDoo {
	
	/**
	 * One singleton to rule the world
	 * @return WooDoo
	 */
    public static function Instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
	protected static $instance = null;
    protected function __construct() {}
    protected function __clone() {}
	
	/**
	 * Actual class variables
	 */
	private $contentPath;
	private $bundleConfig = array();
	
	/**
	 * Set content directory
	 * @param string $contentPath
	 */
	public function Configure($contentPath) {
		$this->contentPath = $contentPath;
	}
	
	/**
	 * Get safe param from url
	 * @param string $paramName
	 * @return string
	 */
	public function GetParam($paramName) {
		return preg_replace('/[^a-zA-Z0-9-]/', '', str_replace('.', '-', filter_input(INPUT_GET, $paramName, FILTER_SANITIZE_STRING)));
	}
	
	/**
	 * Get bundle params
	 */
	private function GetParams() {
		return [
			'version' => $this->GetParam('v'),
			'bundle' => $this->GetParam('b'),
			'type' => (strtolower($this->GetParam('t')) == 'css' ? 'Styles' : 'Scripts')
		];
	}
	
	/**
	 * Load bundle
	 */
	public function LoadBundle($params) {
		$bundleName = $params['bundle'];
		$bundlesPath = MAIN_DIR . 'Bundles' . DIRECTORY_SEPARATOR;
		$bundleParentPath = $bundlesPath . $params['type'] . DIRECTORY_SEPARATOR;
		$bundleCachePath = $bundlesPath . 'Cache' . DIRECTORY_SEPARATOR;
		$bundlePath = $bundleParentPath . $bundleName . DIRECTORY_SEPARATOR;
		$bundleConfigPath = $bundlePath . 'BundleConfig.php';
		
		if (!empty($bundleName) && is_dir($bundlePath) && file_exists($bundleConfigPath)) {
			$this->bundleConfig = array_merge($params, ['bundle_path' => $bundlePath, 'bundle_parent_path' => $bundleParentPath, 'bundle_config_path' => $bundleConfigPath, 'bundle_cache_path' => $bundleCachePath], require_once $bundleConfigPath);
		} else {
			die("Bundle '$bundleName' does not exist!");
		}
	}
	
	/**
	 * Serve bundle
	 */
	public function ServeBundle() {
		// Find correct bundle
		$bundleFile = $this->bundleConfig['bundle_path'] . $this->bundleConfig['version'] . '.php';
		if (empty($this->bundleConfig['version']))
			$bundleFile = $this->bundleConfig['bundle_path'] . $this->bundleConfig['default_version'] . '.php';
		
		if (!file_exists($bundleFile)) {
			header("HTTP/1.1 404 Not Found", true, 404);
			die('The version of this bundle is not valid');
		}
		
		// Load bundle content
		$bundleContent = '';
		foreach (require_once $bundleFile as $bundlePart) {
			$bundlePartFile = $this->contentPath . $this->bundleConfig['type'] . DIRECTORY_SEPARATOR . $bundlePart;
			$bundlePartContent = file_get_contents($bundlePartFile);
			if (!$bundlePartContent) {
				die("Bundle part '$bundlePart' does not exist");
			}
			$bundleContent .= $bundlePartContent;
		}
		$checksum = crc32($bundleContent);
		
		// Find cache if exists
		$bundleCacheFile = $this->bundleConfig['bundle_cache_path'];
		$bundleCacheFile .= preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(array('.php', strtolower($this->bundleConfig['bundle_parent_path'])), '', strtolower($bundleFile)));
		$bundleCacheFile .= "." . $this->bundleConfig['type'] . ".$checksum";
		$bundleCacheFileExists = file_exists($bundleCacheFile);
		
		switch ($this->bundleConfig['type']) {
			case 'Scripts':
				if (!$bundleCacheFileExists) {
					$content = \JShrink\Minifier::minify($bundleContent, ['flaggedComments' => false]);
					file_put_contents($bundleCacheFile, $content);
				}
				$this->ServeContent($bundleCacheFile, 'application/javascript', $checksum);
				break;
			case 'Styles':
				if (!$bundleCacheFileExists) {
					$minifier = new CssMin\CSSmin();
					$content = $minifier->run($bundleContent);
					file_put_contents($bundleCacheFile, $content);
				}
				$this->ServeContent($bundleCacheFile, 'text/css', $checksum);
				break;
		}
	}
	
	/**
	 * Serve content
	 * @param string $file Filename
	 * @param string $contentType Content Type header
	 * @param mixed $etag ETag header
	 */
	public function ServeContent($file, $contentType, $etag) {
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
			header("HTTP/1.1 304 Not Modified", true, 304);
			die();
		}
		header("ETag: $etag");
		header("Content-Type: $contentType");
		readfile($file);
		die();
	}
	
	/**
	 * Entry point
	 */
	public function HandleRequest() {
		//$this->UseHTTPS();
		$this->LoadBundle($this->GetParams());
		$this->ServeBundle();
	}
	
	/**
	 * Prevent anything other than HTTPS
	 */
	public function UseHTTPS() {
		if (empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') {
			header('HTTP/1.1 403 Forbidden');
			die('HTTP not allowed. Use HTTPS instead');
		}
	}
	
}