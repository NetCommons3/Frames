<?php
/**
 * FramesGetTest TestCase
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//@codeCoverageIgnoreStart;
App::uses('NetCommonsGetTest', 'NetCommons.TestSuite');
App::uses('Current', 'NetCommons.Utility');
//@codeCoverageIgnoreEnd;

/**
 * FramesGetTest TestCase
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Frames\TestSuite
 * @codeCoverageIgnore
 */
class FramesGetTest extends NetCommonsGetTest {

/**
 * Fixtures
 *
 * @var array
 */
	private $__fixtures = array(
		'plugin.pages.box4pages',
		'plugin.pages.boxes_page_container4pages',
		'plugin.frames.frame4frames',
		'plugin.frames.frames_language4frames',
		'plugin.pages.pages_language4pages',
		'plugin.pages.page_container4pages',
		'plugin.pages.page4pages',
		'plugin.pages.plugin4pages',
		'plugin.pages.plugins_room4pages',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'frames';

/**
 * Fixtures load
 *
 * @param string $name The name parameter on PHPUnit_Framework_TestCase::__construct()
 * @param array  $data The data parameter on PHPUnit_Framework_TestCase::__construct()
 * @param string $dataName The dataName parameter on PHPUnit_Framework_TestCase::__construct()
 * @return void
 */
	public function __construct($name = null, array $data = array(), $dataName = '') {
		if (! isset($this->fixtures)) {
			$this->fixtures = array();
		}
		$this->fixtures = array_merge($this->__fixtures, $this->fixtures);
		parent::__construct($name, $data, $dataName);
	}

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		Current::write('Language.id', '2');
	}

}