<?php
/** **************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /^/lib/View.php
 *                core View processor
 *                manages which templates will be used
 *                manages which variables will be in scope
 ****************************************************************************/

class View {
	protected $templates = array(
		'header'   => 'header.php',
		'footer'   => 'footer.php',
		'template' => '404.php',
		);
	protected $includePath = null;

	public $vals = array(
		'_meta'   => array(),
		'_script' => array(),
		'_link'   => array(),
		);

	/**
	 * View Constructor
	 *
	 * @param array $cfg Configuration array
	 */
	function __construct($cfg) {
		//Check for and validate location of Controllers
		if (isset(G::$G['includePath'])) {
			foreach (explode(';', G::$G['includePath']) as $v) {
				$s = realpath(SITE.$v.'/templates');
				if (file_exists($s)) {
					$this->includePath[] = $s.'/';
				}
			}
		}
		if (0 == count($this->includePath)) {
			$this->includePath[] = SITE.CORE.'/templates/';
		}

		if (isset($cfg['_header'])) {
			$this->setTemplate('header', $cfg['header']);
			unset($cfg['_header']);
		}
		if (isset($cfg['_footer'])) {
			$this->setTemplate('footer', $cfg['footer']);
			unset($cfg['_footer']);
		}
		if (isset($cfg['_template'])) {
			$this->setTemplate('template', $cfg['template']);
			unset($cfg['_template']);
		}
		if (isset($cfg['_meta']) && is_array($cfg['_meta']) && 0 < count($cfg['_meta'])) {
			foreach ($cfg['_meta'] as $name => $content) {
				$this->_meta($name, $content);
			}
			unset($cfg['_meta']);
		}
		if (isset($cfg['_script']) && is_array($cfg['_script']) && 0 < count($cfg['_script'])) {
			foreach ($cfg['_script'] as $src) {
				$this->_script($src);
			}
			unset($cfg['_script']);
		}
		if (isset($cfg['_link']) && is_array($cfg['_link']) && 0 < count($cfg['_link'])) {
			foreach ($cfg['_link'] as $a) {
				$this->_link(
					isset($a['rel']  )?$a['rel']  :'',
					isset($a['type'] )?$a['type'] :'',
					isset($a['href'] )?$a['href'] :'',
					isset($a['title'])?$a['title']:''
				);
			}
			unset($cfg['_link']);
		}
		$this->vals = $this->vals + $cfg;
	}

	/**
	 * add values for a META tag to be written to document <HEAD>
	 *
	 * @param string $name    META name=
	 * @param string $content META content=
	 *
	 * @return void
	 */
	public function _meta($name = null, $content = null) {
		if (null === $name) {
			return $this->vals['_meta'];
		}
		$this->vals['_meta'][$name] = $content;
	}

	/**
	 * add values for a SCRIPT tag to be written to document <HEAD>
	 *
	 * @param string $src Javascript Source URL
	 *
	 * @return void
	 */
	public function _script($src = null) {
		if (null === $src) {
			return $this->vals['_script'];
		}
		$this->vals['_script'][] = $src;
	}

	/**
	 * add values for a LINK tag to be written to document <HEAD>
	 *
	 * @param string $rel   LINK rel=
	 * @param string $type  LINK type=
	 * @param string $href  LINK href=
	 * @param string $title LINK title=
	 *
	 * @return void
	 */
	public function _link($rel = null, $type = '', $href = '', $title = '') {
		if (null === $rel) {
			return $this->vals['_link'];
		}
		$this->vals['_link'][] = array('rel' => $rel, 'type' => $type, 'href' => $href, 'title' => $title);
	}

	/**
	 * Add values for a css STYLE tag to be written to document <HEAD>
	 * Merely wrap _link()
	 *
	 * @param string $src CSS Source URL
	 *
	 * @return void
	 */
	public function _style($src = null) {
		if (null === $src) {
			return false;
		}
		$this->_link('stylesheet', 'text/css', $src);
	}

	/**
	 * Test whether a file exists in a path sanity checking with realpath
	 *
	 * @param string $path filesystem path to check
	 * @param string $file filename to check for
	 *
	 * @return string|bool corrected filename relative to path if found,
	 *                     false if not found
	 */
	public function in_realpath($path, $file) {
		if ('' == $file) {
			return '';
		}
		//Get the realpath of the file, then verify it exists in passed path
		$s = realpath($path.'/'.$file);
		if (false !== strpos($s, $path) && file_exists($s)) {
			return substr($s, strlen($path));
		}
		return false;
	}

	/**
	 * set view template for rendering request
	 *
	 * @param string $template template part, eg: 'header', 'footer',
	 *                         for main template use 'template'
	 * @param string $file     filename of template, relative to template path
	 *
	 * @return string set template, or prior set template on failure
	 */
	public function setTemplate($template, $file) {
		foreach ($this->includePath as $dir) {
			if (false !== $s=$this->in_realpath($dir, $file)) {
				$this->templates[$template] = $s;
				break;
			}
		}
		return $this->templates[$template];
	}

	/**
	 * get view template for rendering request
	 *
	 * @param string $template template part, eg: 'header', 'footer',
	 *                         for main template use 'template'
	 *
	 * @return string prior set template
	 */
	public function getTemplate($template) {
		return $this->templates[$template];
	}


	/**
	 * __set magic method called when trying to set a var which is not available
	 * If name is of a template this will passoff the set to setTemplate()
	 * All other names will be added to unrestricted vals array
	 *
	 *  @param string $name  property to set
	 *  @param mixed  $value value to use
	 *
	 *  @return void
	 */
	function __set($name, $value) {
		switch ($name) {
			case '_header': return $this->setTemplate('header', $value);
			case '_footer': return $this->setTemplate('footer', $value);
			case '_template': return $this->setTemplate('template', $value);
			default:
				$this->vals[$name] = $value;
		}
	}

	/**
	 * __get magic method called when trying to get a var which is not available
	 * If name is of a template this will passoff the get to getTemplate()
	 * All other names will be pulled from unrestricted vals array
	 *
	 * @param string $name property to set
	 *
	 * @return mixed found value
	 */
	function __get($name) {
		switch ($name) {
			case '_header': return $this->getTemplate('header');
			case '_footer': return $this->getTemplate('footer');
			case '_template': return $this->getTemplate('template');
			default:
				if (isset($this->vals[$name])) {
					return $this->vals[$name];
				}
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$name.' in '
							  .$trace[0]['file'].' on line '.$trace[0]['line'],
							  E_USER_NOTICE);
		}
	}

	/**
	 * __isset magic method restores the normal operation of isset()
	 *
	 * @param string $k property to test
	 *
	 * @return bool Return true if set, false otherwise
	 */
	public function __isset($k) {
		return array_key_exists($k, $this->vals);
	}

	/**
	 * __unset magic method restores the normal operation of unset()
	 *
	 * @param string $k property to unset
	 *
	 * @return void
	 */
	public function __unset($k) {
		unset($this->vals[$k]);
	}

	/**
	 * Render requested template by bringing $this->vals into scope and
	 * including template file
	 *
	 * @param string $_template Template to render
	 *
	 * @return bool true on success, false otherwise
	 */
	public function render($_template = 'template') {
		extract($this->vals);
		//To prevent applications from altering these vars, they are set last
		if (G::$S && G::$S->Login) {
			$_login_id  = G::$S->Login->login_id;
			$_loginname = G::$S->Login->loginname;
		} else {
			$_login_id  = 0;
			$_loginname = 'world';
		}

		//Find the requested template in the include path
		foreach ($this->includePath as $_v) {
			if (isset($this->templates[$_template]) &&
				file_exists($_v.$this->templates[$_template])
			) {
				include_once $_v.$this->templates[$_template];
				return true;
			}
		}

		//If we got here, we didn't find the template.
		return false;
	}
}

/**
 * Helper for brevity in templates - echo html escaped string
 *
 * @param string $s string to output
 *
 * @return void
 */
function html($s) {
	echo htmlspecialchars($s);
}

/**
 * Helper for brevity in templates render configured header template
 *
 * @return void
 */
function get_header() {
	G::$V->render('header');
}

/**
 * Helper for brevity in templates render configured footer template
 *
 * @return void
 */
function get_footer() {
	G::$V->render('footer');
}

/**
 * Helper for brevity in templates render configured main template
 *
 * @return void
 */
function get_template() {
	G::$V->render();
}
