<?php
class t_lesson_model {
	private $common_logic;

	/**
	 * コンストラクタ
	 */
	function __construct() {
		$this->common_logic = new common_logic ();
	}

	/**
	 * 一覧情報取得
	 */
	public function get_lesson_list($offset, $limit, $sqlAdd) {
		return $this->common_logic->select_logic(
		    "SELECT *,
			IF(
				`t_lesson`.`teiin` - (
					SELECT COUNT(*)
					FROM `t_reservation`
					WHERE `t_reservation`.`lesson_id` = `t_lesson`.`lesson_id`
						AND `t_reservation`.`lesson_type` = 2
						AND `t_reservation`.`del_flg` = 0
						AND `t_reservation`.`cancel_flg` = 0
				) > 0
				AND `t_lesson`.`public_flg` = 0
				AND `t_lesson`.`del_flg` = 0
				AND `t_lesson`.`lesson_date_s` <= NOW()
				AND `t_lesson`.`lesson_date_e` >= NOW(),
				1,
				0
			) AS is_active
			FROM `t_lesson` " . $sqlAdd['where'] . " " . $sqlAdd['order'] . " LIMIT " . $limit . " OFFSET " . $offset,
			$sqlAdd['whereParam']
		);
	}

	/**
	 * 総件数取得
	 */
	public function get_lesson_list_cnt($sqlAdd ) {
		return $this->common_logic->select_logic ( "SELECT COUNT(*) AS `cnt` FROM `t_lesson` " . $sqlAdd['where'] . " " . $sqlAdd['order'] . " " , $sqlAdd['whereParam'] );
	}

	/**
	 * 詳細取得
	 *
	 * @param unknown $admin_user_id
	 * @return Ambigous
	 */
	public function get_lesson_detail($lesson_id) {
		return $this->common_logic->select_logic ( 'select * from t_lesson where lesson_id = ?', array (
				$lesson_id
		) );
	}

	/**
	 * 最後に登録されたidを入手
	 */
	public function search_lesson(){
		return $this->common_logic->select_logic_no_param('select lesson_id from t_lesson order by create_at desc limit 1');
	}

	/**
	 * 新規登録
	 *
	 * @param unknown $params
	 */
	public function entry_lesson($params) {
		return $this->common_logic->insert_logic ( "t_lesson", $params );
	}

	/**
	 * 編集更新
	 */
	public function update_lesson($params) {
		$this->common_logic->update_logic ( "t_lesson", " where lesson_id = ?", array (
				'lesson_type',
				'child_type',
				'lesson_date_s',
				'lesson_date_e',
				'title',
				'pref',
				'addr',
				'place',
				'koushi',
				'coution',
				'detail',
				'description',
				'thimbnail',
				'teiin',
				'meta_title',
				'meta_keywords',
				'meta_description',
				'public_flg',
		), $params );

	}


	/**
	 * 削除(論理削除)
	 *
	 * @param unknown $id
	 */
	public function del_lesson($id) {
		return $this->common_logic->update_logic ( "t_lesson", " where lesson_id = ?", array (
				"del_flg"
		), array (
				'1',
				$id
		) );
	}
	/**
	 * 有効化
	 *
	 * @param unknown $id
	 */
	public function recoveryl_lesson($id) {
		return $this->common_logic->update_logic ( "t_lesson", " where lesson_id = ?", array (
				"del_flg"
		), array (
				'0',
				$id
		) );
	}

	/**
	 * 非公開化
	 *
	 * @param unknown $id
	 */
	public function private_lesson($id) {
		return $this->common_logic->update_logic ( "t_lesson", " where lesson_id = ?", array (
				"public_flg"
		), array (
				'1',
				$id
		) );
	}
	/**
	 * 公開
	 *
	 * @param unknown $id
	 */
	public function release_lesson($id) {
		return $this->common_logic->update_logic ( "t_lesson", " where lesson_id = ?", array (
				"public_flg"
		), array (
				'0',
				$id
		) );
	}
}