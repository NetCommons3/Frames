<?php
/**
 * Frame Model
 *
 * @property Plugin $Plugin
 * @property Block $Block
 * @property Box $Box
 * @property Frame $ParentFrame
 * @property Frame $ChildFrame
 * @property Language $Language
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('FramesAppModel', 'Frames.Model');
App::uses('Current', 'NetCommons.Utility');

/**
 * Summary for Frame Model
 *
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @package NetCommons\Frames\Model
 */
class Frame extends FramesAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Box' => array(
			'className' => 'Boxes.Box',
			'foreignKey' => 'box_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		//Pluginは、beforeFindでbindする
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Room' => array(
			'className' => 'Rooms.Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * Called before each find operation. Return false if you want to halt the find
 * call, otherwise return the (modified) query data.
 *
 * @param array $query Data used to execute this query, i.e. conditions, order, etc.
 * @return mixed true if the operation should continue, false if it should abort; or, modified
 *  $query to continue with new $query
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforefind
 */
	public function beforeFind($query) {
		if (Hash::get($query, 'recursive') > -1) {
			$this->bindModel(array(
				'belongsTo' => array(
					'Plugin' => array(
						'className' => 'PluginManager.Plugin',
						'foreignKey' => false,
						'conditions' => array(
							'Plugin.key' . ' = ' . $this->alias . '.plugin_key',
							'Plugin.language_id' => Current::read('Language.id', '0'),
						),
						'fields' => '',
						'order' => ''
					),
				)
			), true);

			$belongsTo = $this->bindModelFrameLang();
			$this->bindModel($belongsTo, true);

			$belongsTo = $this->Block->bindModelBlockLang();
			$this->bindModel($belongsTo, true);
			$this->BlocksLanguage->useDbConfig = $this->useDbConfig;
		}
		return $query;
	}

/**
 * Frame言語テーブルのバインド条件を戻す
 *
 * @return array
 */
	public function bindModelFrameLang() {
		$this->loadModels([
			'Language' => 'M17n.Language',
		]);

		if ($this->Language->isMultipleLang()) {
			$belongsTo = array(
				'belongsTo' => array(
					'FramePublicLanguage' => array(
						'className' => 'Frames.FramePublicLanguage',
						'foreignKey' => false,
						'conditions' => array(
							'FramePublicLanguage.frame_id = Frame.id',
							'FramePublicLanguage.language_id' => ['0', Current::read('Language.id', '0')],
							'FramePublicLanguage.is_public' => true,
						),
						'fields' => array(
							'id', 'language_id', 'frame_id', 'is_public'
						),
						'order' => ''
					),
					'FramesLanguage' => array(
						'className' => 'Frames.FramesLanguage',
						'foreignKey' => false,
						'conditions' => array(
							'FramesLanguage.frame_id = Frame.id',
							'OR' => array(
								'FramesLanguage.is_translation' => false,
								'FramesLanguage.language_id' => Current::read('Language.id', '0'),
							)
						),
						'fields' => array(
							'id', 'language_id', 'frame_id', 'name', 'is_origin', 'is_translation', 'is_original_copy'
						),
						'order' => ''
					),
				)
			);
		} else {
			$belongsTo = array(
				'belongsTo' => array(
					'FramePublicLanguage' => array(
						'className' => 'Frames.FramePublicLanguage',
						'foreignKey' => false,
						'conditions' => array(
							'FramePublicLanguage.frame_id = Frame.id',
							'FramePublicLanguage.language_id' => ['0', Current::read('Language.id', '0')],
							'FramePublicLanguage.is_public' => true,
						),
						'fields' => array(
							'id', 'language_id', 'frame_id', 'is_public'
						),
						'order' => ''
					),
					'FramesLanguage' => array(
						'className' => 'Frames.FramesLanguage',
						'foreignKey' => false,
						'conditions' => array(
							'FramesLanguage.frame_id = Frame.id',
							'FramesLanguage.language_id' => Current::read('Language.id', '0'),
						),
						'fields' => array(
							'id', 'language_id', 'frame_id', 'name', 'is_origin', 'is_translation', 'is_original_copy'
						),
						'order' => ''
					),
				)
			);
		}

		return $belongsTo;
	}

/**
 * BoxによるFrameデータ取得
 *
 * @param int $boxId boxes.id
 * @return array
 */
	public function getFrameByBox($boxId) {
		$query = array(
			'recursive' => 0,
			'conditions' => array(
				//'language_id' => Current::read('Language.id'),
				'Frame.is_deleted' => false,
				'Frame.box_id' => $boxId,
			),
			'order' => array(
				'Frame.weight'
			),
		);

		$result = $this->find('all', $query);
		$frames = array();
		foreach ($result as $i => $frame) {
			$frames[$i] = Hash::merge($frame['FramesLanguage'], $frame['Frame']);
		}
		return $frames;
	}

/**
 * getMaxWeight
 *
 * @param int $boxId boxes.id
 * @return int $weight link_orders.weight
 */
	public function getMaxWeight($boxId) {
		$order = $this->find('first', array(
			'recursive' => -1,
			'fields' => array('weight'),
			'conditions' => array(
				'language_id' => Current::read('Language.id'),
				'box_id' => $boxId
			),
			'order' => array('weight' => 'DESC')
		));

		if (isset($order[$this->alias]['weight'])) {
			$weight = (int)$order[$this->alias]['weight'];
		} else {
			$weight = 0;
		}
		return $weight;
	}

/**
 * Save frame to master data source
 * Is it better to use before after method?
 * If so, is it okay to use beforeValidate?
 *
 * @param array $data request data
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function saveFrame($data) {
		$plugin = Inflector::camelize($data[$this->alias]['plugin_key']);
		$model = Inflector::singularize($plugin);
		$classExists = ClassRegistry::init($plugin . '.' . $model, true);
		$models = array(
			'FramePublicLanguage' => 'Frames.FramePublicLanguage',
			'FramesLanguage' => 'Frames.FramesLanguage',
		);
		if ($classExists) {
			$models[$model] = $plugin . '.' . $model;
		}
		$this->loadModels($models);

		//トランザクションBegin
		$this->begin();

		try {
			if (Hash::get($data, 'Frame.is_deleted')) {
				//論理削除の場合、カウントDown
				$this->__updateWeight($data, -1);
				$data['Frame']['weight'] = null;
			} elseif (! Hash::get($data, 'Frame.id')) {
				//カウントUp
				$data['Frame']['weight'] = 1;
				$this->__updateWeight($data, 1);
			}

			$frame = $this->save($data);
			if (! $frame) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			if (! $this->FramePublicLanguage->savePublicLang($frame)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			if ($this->{$model} instanceof Model && method_exists($this->{$model}, 'afterFrameSave')) {
				$this->{$model}->afterFrameSave($frame);
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollbaxk
			$this->rollback($ex);
		}

		return $frame;
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
 * @see Model::save()
 * @throws InternalErrorException
 */
	public function afterSave($created, $options = array()) {
		if (isset($this->data['FramesLanguage'])) {
			$this->loadModels(array(
				'FramesLanguage' => 'Frames.FramesLanguage'
			));
			$data = $this->FramesLanguage->create(
				Hash::merge(
					array('frame_id' => $this->data['Frame']['id']),
					$this->data['FramesLanguage']
				)
			);
			$result = $this->FramesLanguage->save($data);
			if (! $result) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$this->data['FramesLanguage'] = $result['FramesLanguage'];
		}

		parent::afterSave($created, $options);
	}

/**
 * Save frame to master data source
 * Is it better to use before after method?
 * If so, is it okay to use beforeValidate?
 *
 * @param array $data request data
 * @param array $order Param is 'up' or 'down'
 * @throws InternalErrorException
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	public function saveWeight($data, $order) {
		//トランザクションBegin
		$this->begin();

		try {
			if ($order === 'up') {
				$data['Frame']['weight']--;
				$this->__updateWeight($data, 1, '=');
			} else {
				$data['Frame']['weight']++;
				$this->__updateWeight($data, -1, '=');
			}

			$this->id = (int)$data['Frame']['id'];
			if (! $this->saveField('weight', $data['Frame']['weight'], ['callbacks' => false])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollbaxk
			$this->rollback($ex);
		}

		return true;
	}

/**
 * Save frame to master data source
 * Is it better to use before after method?
 * If so, is it okay to use beforeValidate?
 *
 * @param array $data request data
 * @param int $sequence Count sequence
 * @param string $sign Sign
 * @throws InternalErrorException
 * @return mixed On success void if it not throw exception on failure
 */
	private function __updateWeight($data, $sequence, $sign = null) {
		if (! isset($sign)) {
			if ($sequence > 0) {
				$sign = '>=';
			} else {
				$sign = '>';
			}
		}

		$update = array(
			'Frame.weight' => 'Frame.weight + (' . $sequence . ')'
		);
		$conditions = array(
			'Frame.weight ' . $sign . ' ' => $data['Frame']['weight'],
			'Frame.box_id' => $data['Frame']['box_id'],
			'Frame.is_deleted' => false,
		);
		if (! $this->updateAll($update, $conditions)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}

}
