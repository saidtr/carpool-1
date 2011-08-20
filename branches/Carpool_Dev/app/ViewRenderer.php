<?php  

/**
 * 
 * Rendering engine that allows running PHP scripts with their
 * own variable scope. Mainly used for re-usable view scripts
 * 
 * @author itay
 *
 */
class ViewRenderer {
	
	private $path;
	
	public function __construct($path) {
		$this->path = $path;
	}
	
	/**
	 * Assign a variable or array of variables
	 * @param mixed $var Variable name, or array in the form name -> value
	 * @param mixed $value Value. Ignored if the first variable is array
	 */
	public function assign($var, $value = null) {
		if (is_string($var)) {
			$this->$var = $value;
		} elseif (is_array($var)) {
			foreach ($var as $key => $val) {
                $this->$key = $val;
            }
		}
	}
	
	public function doRender() {
		include $this->path;
	}
	
	public function doRenderToString() {
		ob_start();
		include $this->path;
		return ob_get_clean();
	}
	
	public function __set($name, $value) {
		$this->assign($name, $value);
	}
	
	/**
	 * Render the view script and outputs it
	 * 
	 * @param string $path Path for the script to render
	 * @param array $params Array with parameters to pass
	 */
	public static function render($path, $params = null) {
		$view = new ViewRenderer($path);
		if ($params)
			$view->assign($params);
		$view->doRender();
	}

	/**
	 * Render the view script and output it to string. Less
	 * effecient than ::render - use only when required
	 * 
	 * @param string $path Path for the script to render
	 * @param array $params Array with parameters to pass
	 * @return string String containing the result
	 */
	public static function renderToString($path, $params = null) {
		debug(__METHOD__ . "($path, $params)");
		$view = new ViewRenderer($path);
		if ($params)
			$view->assign($params);
		return $view->doRenderToString();
	}
	
}
