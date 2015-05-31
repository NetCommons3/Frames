<?php
/**
 * ModelWithAfterFrameSave Model of test_app
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         Frame 0.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 **/

/**
 * Class FrameTestController
 *
 * @since         Frame 0.1
 */
class FrameTestController extends Model {

/**
 * Mame of the Controller
 *
 * @var string
 */
	public $name = 'FrameTest';

/**
 * Uses no Models
 *
 * @var array
 */
	public $uses = array();

/**
 * Uses only Frame Toolbar Component
 *
 * @var array
 */
	public $components = array('Frame.Toolbar');

/**
 * Return Request Action Value
 *
 * @return string
 */
	public function request_action_return() {
		$this->autoRender = false;
		return 'I am some value from requestAction.';
	}

/**
 * Render Request Action
 */
	public function request_action_render() {
		$this->set('test', 'I have been rendered.');
	}
}