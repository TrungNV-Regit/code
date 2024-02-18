<?php
require_once __DIR__ .  '/../../model/t_lesson_model.php';
require_once __DIR__ .  '/../../logic/common/common_logic.php';


class lesson_logic {
	private $t_lesson_model;
	private $common_logic;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		$this->t_lesson_model= new t_lesson_model();
		$this->common_logic = new common_logic ();
	}

	/**
	 * 初期HTML生成
	 */
	public function create_data_list($params, $search_select = null, $where = array()){

		$this->common_logic->create_table_dump("t_lesson");


		$json = file_get_contents(__DIR__ . '/../../common/lesson_category.json');
		$lesson_type_b = json_decode($json, true);
		if($where['type'] == '1'){
			$lesson_type = $lesson_type_b['yousei'];
		}elseif($where['type'] == '2'){
			$lesson_type = $lesson_type_b['lesson'];
		}
		$lesson_w = array();
		foreach ($lesson_type as $value) {
			$lesson_w[] = $value['id'];
		}


		$sqlAdd = $this->common_logic->create_where($search_select);
		if($where['type'] != null && $where['type'] != ''){
			$ad = ($sqlAdd['where']!= null && $sqlAdd['where']!= '')?" AND ": " WHERE ";
			$sqlAdd['where'] .= $ad ." FIND_IN_SET(`lesson_type`, ?) ";
			array_push($sqlAdd['whereParam'], implode(",",$lesson_w));
		}


		if($where['w_lesson_type'] != null && $where['w_lesson_type'] != ''){
			$ad = ($sqlAdd['where']!= null && $sqlAdd['where']!= '')?" AND ": " WHERE ";
			$sqlAdd['where'] .= $ad ." `lesson_type` = ?  ";
			array_push($sqlAdd['whereParam'], $where['w_lesson_type']);
		}

		if($where['w_pref'] != null && $where['w_pref'] != ''){
			$ad = ($sqlAdd['where']!= null && $sqlAdd['where']!= '')?" AND ": " WHERE ";
			$sqlAdd['where'] .= $ad ." `pref` COLLATE utf8_unicode_ci LIKE ?  ";
			array_push($sqlAdd['whereParam'], "%".urldecode($where['w_pref'])."%");
		}

		$page_title = 'サンプル';

		//総件数取得
		$result_cnt = $this->t_lesson_model->get_lesson_list_cnt($sqlAdd);

		$all_cnt = $result_cnt[0]['cnt'];
		$pager_cnt = ceil($all_cnt / $params[2]);
		$offset = ($params[1] - 1) * $params[2];
		$sqlAdd['order'] = 'ORDER BY `is_active` DESC, `t_lesson`.`del_flg` ASC, `t_lesson`.`create_at` DESC';

		$result_lesson = $this->t_lesson_model->get_lesson_list($offset, $params[2],$sqlAdd);

		$return_html = "";
		$back_color = 1;
		$cnt = $offset;

		$sortData = array();
		$indexPush = 0;

		for ($i = 0; $i < count((array)$result_lesson); $i++) {
			$row = $result_lesson[$i];
			$sum = $this->common_logic->select_logic("select SUM(`reserve_num`) as `reserve_num_sum` from t_reservation where lesson_id = ? and lesson_type = 2 and del_flg = 0 and cancel_flg = 0", array($row['lesson_id']));
			$zan = (int)$row['teiin'] - (int)$sum[0]['reserve_num_sum'];
			$active = $zan > 0 && $row['public_flg'] == 0 && $row['del_flg'] == 0 && date($row['lesson_date_s']) <= date("Y-m-d") && date($row['lesson_date_e']) >= date("Y-m-d");
			if ($active) {
				array_splice($sortData, $indexPush, 0, array($row));
				$indexPush++;
			} else {
				array_push($sortData, $row);
			}
		}
		$result_lesson = $sortData;

		for($i = 0; $i < count ( (array)$result_lesson ); $i ++) {
			$row = $result_lesson [$i];

			$cnt ++;
			$edit_html = '&nbsp;';

			$lesson_id = $this->common_logic->zero_padding ( $row ['lesson_id'] );

			//各データをhtmlに変換

			//画像表示処理
			$img_tag_html = '<img src="../assets/admin/img/nophoto.png" style="height:50px">';
			$nmage_list = array ();
			if (strpos ( $row ['image'], ',' ) !== false && ($row ['image'] != null && $row ['image'] != '')) {
				// 'abcd'のなかに'bc'が含まれている場合
				$img_tag_html = '';
				$nmage_list = explode ( ',', $row ['image'] );

				for($n = 0; $n < count ( $nmage_list ); $n ++) {
					$img_tag_html .= '<img src="../upload_files/lesson/' . $nmage_list [$n] . '" style="height:50px">';
				}
			} else if ($row ['image'] != null && $row ['image'] != '') {
				$img_tag_html = '<img src="../upload_files/lesson/' . $row ['image'] . '" style="height:50px">';
			}

			//動画
			if ($row['movie'] != null && $row['movie'] != ""){
				$movie = '<a  href="#modal" class="check_movie" lesson_id="'. $row['lesson_id'] .'">有り</a>';
			}else{
				$movie = '無し';
			}


			//削除フラグ
			$del_color = "";
			$edit_html_a = "<a herf='javascript:void(0);' class='edit clr1' name='edit_" . $row ['lesson_id'] . "' value='" . $row ['lesson_id'] . "'><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></a><br>";
			$del_html = "有効";
			if ($row ['del_flg'] == 1) {
				$del_color = "color:#d3d3d3";
				$del_html = "削除";
				$edit_html_a .= "<a herf='javascript:void(0);' class='recovery clr2' name='recovery_" . $row ['lesson_id'] . "' value='" . $row ['lesson_id'] . "' ><i class=\"fa fa-undo\" aria-hidden=\"true\"></i></a><br>";
			} else {
				$edit_html_a .= "<a herf='javascript:void(0);' class='del clr2' name='del_" . $row ['lesson_id'] . "' value='" . $row ['lesson_id'] . "'><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a><br>";
			}
			$edit_html_a .= "<a herf='javascript:void(0);' class='copy clr3' name='del_" . $row ['lesson_id'] . "' value='" . $row ['lesson_id'] . "'><i class=\"zmdi zmdi-copy\"></i></a><br>";

			if ($back_color == 2) {
				$back_color_html = "style='background: #f7f7f9; " . $del_color . "'";
				$back_color_bottom_html = "style='background: #f7f7f9; border-bottom:solid 2px #d0d0d0;'";
			} else {
				$back_color_html = "style='background: #ffffff; " . $del_color . "'";
				$back_color_bottom_html = "style='background: #ffffff; border-bottom:solid 2px #d0d0d0;'";
			}

			$edit_html_b = '';
			$public_html = "公開";
			if ($row ['public_flg'] == 1) {
				$public_html = "非公開";
				$edit_html_b .= "<a herf='javascript:void(0);' class='release btn btn-default waves-effect w-md btn-xs' name='release_" . $row ['lesson_id'] . "' value='" . $row ['lesson_id'] . "'>非公開</a>";
			} else {
				$edit_html_b .= "<a herf='javascript:void(0);' class='private btn btn-custom waves-effect w-md btn-xs ' name='private_" . $row ['lesson_id'] . "' value='" . $row ['lesson_id'] . "'>公開</a>";
			}

			$create_at = $row['create_at'];
			$diff = strtotime(date('YmdHis')) - strtotime($create_at);
			if($diff < 60){
				$time = $diff;
				$create_at = $time . '秒前';
			}elseif($diff < 60 * 60){
				$time = round($diff / 60);
				$create_at = $time . '分前';
			}elseif($diff < 60 * 60 * 24){
				$time = round($diff / 3600);
				$create_at = $time . '時間前';
			}

			$update_at = $row['update_at'];
			$diff = strtotime(date('YmdHis')) - strtotime($update_at);
			if($diff < 60){
				$time = $diff;
				$update_at = $time . '秒前';
			}elseif($diff < 60 * 60){
				$time = round($diff / 60);
				$update_at = $time . '分前';
			}elseif($diff < 60 * 60 * 24){
				$time = round($diff / 3600);
				$update_at = $time . '時間前';
			}


			foreach ($lesson_type as $ltd) {
				if($ltd['id'] == $row['lesson_type']){
					$lt = $ltd['name'];
					break;
				}
			}


			$sum = $this->common_logic->select_logic("select SUM(`reserve_num`) as `reserve_num_sum` from t_reservation where lesson_id = ? and lesson_type = 2 and del_flg = 0 and cancel_flg = 0", array($row['lesson_id']));
			$zan = (int)$row['teiin'] - (int)$sum[0]['reserve_num_sum'];
			$ln = '<a href="./reservation.php?lid='.$row['lesson_id'].'&ty=2" target="_blank" >予約者一覧</a>';


			$pub = '<span style="color: red;">終了</span>';
			if($zan > 0 && $row['public_flg'] == 0 && $row['del_flg'] == 0 && date($row['lesson_date_s']) <= date("Y-m-d") && date($row['lesson_date_e']) >= date("Y-m-d") )$pub = '公開中';
			
			
			$op = '; opacity:0.4;';
			if($zan > 0 && $row['public_flg'] == 0 && $row['del_flg'] == 0 && date($row['lesson_date_s']) <= date("Y-m-d") && date($row['lesson_date_e']) >= date("Y-m-d") ){
				$op = '; opacity:1;';
			}
			
			

			if ($back_color == 2) {
				$back_color_html = "style='background: #f7f7f9; " . $del_color . $op . "'";
			} else {
				$back_color_html = "style='background: #ffffff; " . $del_color . $op . "'";
			}

			$information = '
<div style="display: flex;"><div style="width:120px;">開催場所：</div><div>'.$row['place'].'</div></div>
<div style="display: flex;"><div style="width:120px;">日程　　：</div><div>'.str_replace(",", "<br>", $row['detail']).'</div></div>
<div style="display: flex;"><div style="width:120px;">公開期間：</div><div>'.date("Y年m月d日",strtotime($row['lesson_date_s'])).'～'.date("Y年m月d日",strtotime($row['lesson_date_e'])).'</div></div>
<div style="display: flex;"><div style="width:120px;">定員　　：</div><div>'.$row['teiin'].'名(残'.$zan.'名)</div></div>
<div style="display: flex;">'.$ln.'</div>
';

			if($row['child_type'] == '1'){
				$child_type = '子連れ可';
			}elseif($row['child_type'] == '0'){
				$child_type = '子連れ不可';
			}


			$return_html .= "
					<tr " . $back_color_html . ">
						<td class='count_no'>" . $cnt . "</td>
						<td>" . $row['lesson_id'] . "</td>
						<td>".$lt . "<br>".$child_type."<br>".$pub."</td>
						<td>" . $information . "</td>
						<td>" . $create_at . "</td>
						<td>" . $update_at . "</td>
						<td>
							$edit_html_a
						</td>
						<td>
							$edit_html_b
						</td>
					</tr>
";
			$back_color ++;

			if ($back_color >= 3) {
				$back_color = 1;
			}
		}
		// }

		//ページャー部分HTML生成
		$pager_html = '<li><a href="javascript:void(0)" class="page prev" num_link="true" disp_id="1">first</a></li>';
		$pager_html .= '<li><a href="javascript:void(0)" class="page prev" pager_type="prev">prev</a></li>';
		for ($i = 0; $i < $pager_cnt; $i++) {
			$disp_cnt = $i+1;

			if ($i == 0) {
				$pager_html .= '<li><a href="javascript:void(0)" class="page num_link" num_link="true" disp_id="'.$disp_cnt.'">'.$disp_cnt.'</a></li>';
			} else {
				$pager_html .= '<li><a href="javascript:void(0)" class="page num_link" num_link="true" disp_id="'.$disp_cnt.'">'.$disp_cnt.'</a></li>';
			}
		}
		$pager_html .= '<li><a href="javascript:void(0)" class="page next" pager_type="next">next</a></li>';
		$pager_html .= '<li><a href="javascript:void(0)" class="page next" num_link="true" disp_id="'.$pager_cnt.'">last</a></li>';

		return array (
				"entry_menu_list_html" => $admin_menu_list_html,
				"list_html" => $return_html,
				"pager_html" => $pager_html,
				'page_cnt' => $pager_cnt,
				'all_cnt' => $all_cnt,
				'disp_all' => $disp_all,
		);
	}


	/**
	 * 新規登録処理
	 */
	public function entry_new_data($params) {

		$result = $this->t_lesson_model->entry_lesson( $params );
		return true;
	}

	/**
	 * 取得処理
	 */
	public function get_detail($lesson_id ){
		$result = $this->t_lesson_model->get_lesson_detail ( $lesson_id );

		return  $result [0];
	}

	/**
	 * 編集更新処理
	 * @param unknown $post
	 */
	public function update_detail($params){

		$result = $this->t_lesson_model->update_lesson($params);
		return true;
	}

	/**
	 * 有効化処理
	 *
	 * @param unknown $id
	 */
	public function recoveryl_func($id) {
		$this->t_lesson_model->recoveryl_lesson ( $id );
	}


	/**
	 * 削除処理
	 *
	 * @param unknown $id
	 */
	public function del_func($id) {
		$this->t_lesson_model->del_lesson ( $id );
	}

	/**
	 * 非公開化処理
	 *
	 * @param unknown $id
	 */
	public function private_func($id) {
		$this->t_lesson_model->private_lesson ( $id );
	}


	/**
	 * 公開処理
	 *
	 * @param unknown $id
	 */
	public function release_func($id) {
		$this->t_lesson_model->release_lesson ( $id );
	}

}