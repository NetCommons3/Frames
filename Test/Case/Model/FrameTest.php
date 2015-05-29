<?php
/**
 * Frame Test Case
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Frame', 'Frames.Model');

/**
 * Summary for Frame Test Case
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Frames\Test\Case\Model
 */
class FrameTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.blocks.block',
		'plugin.boxes.box',
		'plugin.frames.frame',
		'plugin.plugin_manager.plugin',
		'plugin.m17n.language',
		'plugin.pages.page',
		'plugin.users.user',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Frame = ClassRegistry::init('Frames.Frame');

		$framesPath = App::pluginPath('Frames');
		$noDir = (empty($framesPath) || !file_exists($framesPath));
		if ($noDir) {
			$this->markTestAsSkipped('Could not find Frames in plugin paths');
		}

		App::build(array(
			'Plugin' => array($framesPath . 'Test' . DS . 'test_app' . DS . 'Plugin' . DS)
		));
		CakePlugin::load('ModelWithAfterFrameSaveTestPlugin');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Frame);

		parent::tearDown();
	}

/**
 * testGetContainableQuery method
 *
 * @return void
 */
	public function testGetContainableQuery() {
		$containableQuery = $this->Frame->getContainableQuery();

		$this->assertCount(3, $containableQuery);

		$this->assertArrayHasKey('order', $containableQuery);
		$this->assertCount(1, $containableQuery['order']);
		$this->assertContains('Frame.weight', $containableQuery['order']);

		$this->assertArrayHasKey('Language', $containableQuery);
		$this->assertCount(1, $containableQuery['Language']);
		$this->assertArrayHasKey('conditions', $containableQuery['Language']);
		$this->assertCount(1, $containableQuery['Language']['conditions']);
		$this->assertArrayHasKey('Language.code', $containableQuery['Language']['conditions']);
		// It should test language code.
		$this->assertContains('ja', $containableQuery['Language']['conditions']);

		$this->assertContains('Plugin', $containableQuery);
	}

/**
 * testSaveFrame method
 *
 * @return void
 */
	public function testSaveFrame() {
		$expectCount = $this->Frame->find('count', array('recursive' => -1)) + 1;

		$data = array(
			'Frame' => array(
				'is_deleted' => false,
				'name' => '',
				'room_id' => null,
				'plugin_key' => 'frames',
				'box_id' => '1'
			)
		);

		$this->Frame->create();
		$this->Frame->saveFrame(array('Frame' => array('plugin_key' => 'model_with_after_frame_save_test_plugin')));

		$this->assertEquals($expectCount, $this->Frame->find('count', array('recursive' => -1)));
	}

/**
 * testSaveFrameError method
 *
 * @return void
 */
	public function testSaveFrameError() {
		$frameMock = $this->getMockForModel('Frames.Frame', array('save'));
		$frameMock->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$expectCount = $frameMock->find('count', array('recursive' => -1));

		$frameMock->create();
		$this->assertFalse($frameMock->saveFrame(array('Frame' => array('plugin_key' => 'model_with_after_frame_save_test_plugin'))));

		//$this->assertEquals('master', $this->Frame->useDbConfig);
		$this->assertEquals($expectCount, $frameMock->find('count', array('recursive' => -1)));
	}

/**
 * testDeleteFrame method
 *
 * @return void
 */
	public function testDeleteFrame() {
		$expectCount = $this->Frame->find('count', array('recursive' => -1)) - 1;

		$this->Frame->id = 10;
		$this->Frame->deleteFrame();

		//$this->assertEquals('master', $this->Frame->useDbConfig);
		$this->assertEquals($expectCount, $this->Frame->find('count', array('recursive' => -1)));
		$this->assertEmpty($this->Frame->findById('10'));
	}

/**
 * testDeleteFrameError method
 *
 * @return void
 */
	public function testDeleteFrameError() {
		$frameMock = $this->getMockForModel('Frames.Frame', array('delete'));
		$frameMock->expects($this->once())
			->method('delete')
			->will($this->returnValue(false));

		$expectCount = $frameMock->find('count', array('recursive' => -1));

		$frameMock->id = 10;
		$this->assertFalse($frameMock->deleteFrame());

		//$this->assertEquals('master', $this->Frame->useDbConfig);
		$this->assertEquals($expectCount, $frameMock->find('count', array('recursive' => -1)));
	}

}
