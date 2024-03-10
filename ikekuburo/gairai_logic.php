<?php
require_once __DIR__ .  '/../../model/t_gairai_model.php';
require_once __DIR__ .  '/../../logic/common/common_logic.php';


class gairai_logic
{
	private $t_gairai_model;
	private $common_logic;
	private $fields_morning = ['am_week_1', 'am_week_2', 'am_week_week_2', 'am_week_3', 'am_week_4', 'am_week_5', 'am_week_6'];
	private $fields_afternoon = ['pm_week_1', 'pm_week_2', 'pm_week_week_2', 'pm_week_3', 'pm_week_4', 'pm_week_5', 'pm_week_6'];
	private $fields_evening = ['ev_week_1', 'ev_week_2', 'ev_week_week_2', 'ev_week_3', 'ev_week_4', 'ev_week_5', 'ev_week_6'];

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		$this->t_gairai_model = new t_gairai_model();
		$this->common_logic = new common_logic();
	}

	/**
	 * 初期HTML生成
	 */
	public function create_data_list($params, $search_select = null)
	{

		//$this->common_logic->create_table_dump('t_gairai');

		$sqlAdd = $this->common_logic->create_where($search_select);

		$page_title = 'サンプル';

		//総件数取得
		$result_cnt = $this->t_gairai_model->get_gairai_list_cnt($sqlAdd);

		$all_cnt = $result_cnt[0]['cnt'];
		$pager_cnt = ceil($all_cnt / $params[2]);
		$offset = ($params[1] - 1) * $params[2];


		$result_gairai = $this->t_gairai_model->get_gairai_list($offset, $params[2], $sqlAdd);

		$return_html = "";
		$back_color = 1;
		$cnt = $offset;
		if (!is_array($result_gairai)) $result_gairai = array();


		for ($i = 0; $i < count($result_gairai); $i++) {
			$row = $result_gairai[$i];
			//echo $row['img']; echo "<br>";
			$cnt++;
			$edit_html = '&nbsp;';

			$gairai_id = $this->common_logic->zero_padding($row['gairai_id']);

			//各データをhtmlに変換

			//echo $row['img'];
			//画像表示処理
			//$img_tag_html = '<img src="../assets/admin/img/nophoto.png" style="height:50px">';
			$img_tag_html = '<img src="../assets/admin/img/"' . $row['img'] . '" style="height:50px">';
			$nmage_list = array();
			if (strpos($row['img'], ',') !== false && ($row['img'] != null && $row['img'] != '')) {
				// 'abcd'のなかに'bc'が含まれている場合
				$img_tag_html = '';
				$nmage_list = explode(',', $row['img']);

				for ($n = 0; $n < count($nmage_list); $n++) {
					$img_tag_html .= '<img src="../upload_files/gairai/' . $nmage_list[$n] . '" style="height:50px">';
				}
			} else if ($row['img'] != null && $row['img'] != '') {
				$img_tag_html = '<img src="../upload_files/gairai/' . $row['img'] . '" style="height:50px">';
			}

			//動画
			if ($row['movie'] != null && $row['movie'] != "") {
				$movie = '<a  href="#modal" class="check_movie" gairai_id="' . $row['gairai_id'] . '">有り</a>';
			} else {
				$movie = '無し';
			}


			//削除フラグ
			$del_color = "";
			$edit_html_a = "<a href='javascript:void(0);' class='edit clr1' name='edit_" . $row['gairai_id'] . "' value='" . $row['gairai_id'] . "'><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></a><br>";
			$del_html = "有効";
			if ($row['del_flg'] == 1) {
				$del_color = "color:#d3d3d3";
				$del_html = "削除";
				$edit_html_a .= "<a href='javascript:void(0);' class='recovery clr2' name='recovery_" . $row['gairai_id'] . "' value='" . $row['gairai_id'] . "' ><i class=\"fa fa-undo\" aria-hidden=\"true\"></i></a><br>";
			} else {
				$edit_html_a .= "<a href='javascript:void(0);' class='del clr2' name='del_" . $row['gairai_id'] . "' value='" . $row['gairai_id'] . "'><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a><br>";
			}
			// $edit_html_a .= "<a href='javascript:void(0);' class='copy clr3' name='del_" . $row['gairai_id'] . "' value='" . $row['gairai_id'] . "'><i class=\"zmdi zmdi-copy\"></i></a><br>";

			if ($back_color == 2) {
				$back_color_html = "style='background: #f7f7f9; " . $del_color . "'";
				$back_color_bottom_html = "style='background: #f7f7f9; border-bottom:solid 2px #d0d0d0;'";
			} else {
				$back_color_html = "style='background: #ffffff; " . $del_color . "'";
				$back_color_bottom_html = "style='background: #ffffff; border-bottom:solid 2px #d0d0d0;'";
			}

			$edit_html_b = '';
			$public_html = "公開";
			if ($row['public_flg'] == 1) {
				$public_html = "非公開";
				$edit_html_b .= "<a herf='javascript:void(0);' class='release btn btn-default waves-effect w-md btn-xs' name='release_" . $row['gairai_id'] . "' value='" . $row['gairai_id'] . "'>非公開</a>";
			} else {
				$edit_html_b .= "<a herf='javascript:void(0);' class='private btn btn-custom waves-effect w-md btn-xs ' name='private_" . $row['gairai_id'] . "' value='" . $row['gairai_id'] . "'>公開</a>";
			}



			$create_at = $row['create_at'];

			$newvar_now = DateTime::createFromFormat('U', strtotime("now"));
			//$newvar_strdate = DateTime::createFromFormat('U', strtotime(date(YmdHis)));
			//$newvar_created_at = DateTime::createFromFormat('U', strtotime($create_at));
			//$newvar_time = DateTime::createFromFormat('U', time());
			//$newvardateYmd = date(YmdHis);
			//$newvar_diff = strtotime(date('YmdHis')) - strtotime($create_at);
			//$newvar_div60 = $newvar_diff / 60;


			$diff = strtotime(date('YmdHis')) - strtotime($create_at);
			//$diff = strtotime('now') - strtotime($create_at);
			if ($diff < 60) {
				$time = $diff;
				//$time = round($diff / 60);
				$create_at = $time . '秒前';
			} elseif ($diff < 60 * 60) {
				$time = round($diff / 60);
				$create_at = $time . '分前';
			} elseif ($diff < 60 * 60 * 24) {
				$time = round($diff / 3600);
				$create_at = $time . '時間前';
			}

			$update_at = $row['update_at'];
			$diff = strtotime(date('YmdHis')) - strtotime($update_at);
			if ($diff < 60) {
				$time = $diff;
				$update_at = $time . '秒前';
			} elseif ($diff < 60 * 60) {
				$time = round($diff / 60);
				$update_at = $time . '分前';
			} elseif ($diff < 60 * 60 * 24) {
				$time = round($diff / 3600);
				$update_at = $time . '時間前';
			}

			//if($row['img'] == null || $row['img'] == '') $row['img'] = "noimage.png";

			$thumbnail = '';
			if (strpos($row['img'], "http") !== false || strpos($row['img'], "https") !== false) {
				$thumbnail = '<img width="120px" alt="背景" src="' . $row['img'] . '">';
			} else {
				$thumbnail = '<img width="120px" alt="背景" src="../upload_files/gairai/' . $row['img'] . '">';
			}


			$htt_add = (strpos($_SERVER['HTTP_HOST'], "localhost") !== false || strpos($_SERVER['HTTP_HOST'], "2floor.xyz") !== false) ? "/total_living" : "";
			$htt = ($_SERVER['HTTPS'] !== null) ? "https://" : "http://";

			$url = $htt . $_SERVER['HTTP_HOST'] . $htt_add . "/blog_detail.php?id=" . $row['gairai_id'];
			$link = '<a href="' . $url . '" target="_blank">' . $url . '<a>';

			$cate_str = $this->common_logic->select_logic("select * from m_code where code_id = ? ", array($row['category']))[0]['description1'];

			$return_html .= "
					<tr " . $back_color_html . ">
						<td class='count_no'>" . $cnt . "</td>
						<td>" . $row['gairai_id'] . "</td>
						<td>" .  $row['category'] . "</td>
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
			$back_color++;

			if ($back_color >= 3) {
				$back_color = 1;
			}
		}
		// }

		//ページャー部分HTML生成
		$pager_html = '<li><a href="javascript:void(0)" class="page prev" pager_type="prev">prev</a></li>';
		for ($i = 0; $i < $pager_cnt; $i++) {
			$disp_cnt = $i + 1;

			if ($i == 0) {
				$pager_html .= '<li><a href="javascript:void(0)" class="page num_link" num_link="true" disp_id="' . $disp_cnt . '">' . $disp_cnt . '</a></li>';
			} else {
				$pager_html .= '<li><a href="javascript:void(0)" class="page num_link" num_link="true" disp_id="' . $disp_cnt . '">' . $disp_cnt . '</a></li>';
			}
		}
		$pager_html .= '<li><a href="javascript:void(0)" class="page next" pager_type="next">next</a></li>';

		return array(
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
	public function entry_new_data($params)
	{

		$result = $this->t_gairai_model->entry_gairai($params);
		return true;
	}

	/**
	 * 取得処理
	 */
	public function get_detail($gairai_id)
	{
		$result = $this->t_gairai_model->get_gairai_detail($gairai_id);

		return  $result[0];
	}

	/**
	 * 編集更新処理
	 * @param unknown $post
	 */
	public function update_detail($params)
	{
		//echo "<pre>" . print_r($params) . "</pre>";
		$result = $this->t_gairai_model->update_gairai($params);
		return true;
	}

	/**
	 * 有効化処理
	 *
	 * @param unknown $id
	 */
	public function recoveryl_func($id)
	{
		$this->t_gairai_model->recoveryl_gairai($id);
	}


	/**
	 * 削除処理
	 *
	 * @param unknown $id
	 */
	public function del_func($id)
	{
		$this->t_gairai_model->del_gairai($id);
	}

	/**
	 * 非公開化処理
	 *
	 * @param unknown $id
	 */
	public function private_func($id)
	{
		$this->t_gairai_model->private_gairai($id);
	}


	/**
	 * 公開処理
	 *
	 * @param unknown $id
	 */
	public function release_func($id)
	{
		$this->t_gairai_model->release_gairai($id);
	}

	/**
	 * 公開処理
	 *
	 * @param unknown $id
	 */
	public function copy_func($id)
	{
		$data = $this->t_gairai_model->get_gairai_detail($id);

		$param = array(
			"【複製】" . $data[0]['title'],
			$data[0]['img'],
			$data[0]['description'],
			$data[0]['disp_date'],
			$data[0]['category'],
			$data[0]['meta_title'],
			$data[0]['meta_keyword'],
			$data[0]['meta_description'],
			$data[0]['am_week_1'],
			$data[0]['am_week_2'],
			$data[0]['am_week_3'],
			$data[0]['am_week_4'],
			$data[0]['am_week_5'],
			$data[0]['am_week_6'],
			$data[0]['pm_week_1'],
			$data[0]['pm_week_2'],
			$data[0]['pm_week_3'],
			$data[0]['pm_week_4'],
			$data[0]['pm_week_5'],
			$data[0]['pm_week_6'],
			'0',
			"1",
		);
		$this->t_gairai_model->entry_gairai($param);
	}

	public function get_list_gairai()
	{
		$departments = array(
			'総合診療',
			'整形外科',
			'脳神経外科',
			'外科・消化器外科',
			'小児科・小児外科',
			'循環器内科',
			'呼吸器内科',
			'泌尿器科',
			'乳腺科',
			'婦人科',
			'糖尿病内科',
			'皮膚科',
			'腎臓内科',
			'禁煙外来',
		);
		$table_morning = '';
		$table_afternoon = '';
		$table_evening = '';
		$list_gairai = $this->common_logic->select_logic_no_param('select * from t_gairai where public_flg = 0 and del_flg = 0 order by gairai_id asc');

		for ($i = 0; $i < count($departments); $i++) {
			$department = $departments[$i];

			$data_department_morning = array_filter((array)$list_gairai, function ($item) use ($department) {
				return $item['category'] == $department && $this->check_fields_data($item, $this->fields_morning);
			});

			$data_department_afternoon = array_filter((array)$list_gairai, function ($item) use ($department) {
				return $item['category'] == $department && $this->check_fields_data($item, $this->fields_afternoon);
			});

			$data_department_evening = array_filter((array)$list_gairai, function ($item) use ($department) {
				return $item['category'] == $department && $this->check_fields_data($item, $this->fields_evening);
			});

			if ($data_department_morning) {
				$data = $this->handle_row_data($data_department_morning, $this->fields_morning);
				if ($i == 0) {
					$table_morning = '<tr><th ' . (count($data) > 1 ? 'rowspan="' . count($data) . '"' : '') . '>' . $department . '</th>';
				} else {
					$table_morning .= '<tr><th ' . (count($data) > 1 ? 'rowspan="' . count($data) . '"' : '') . '>' . $department . '</th>';
				}

				$table_morning .= $this->handle_display($data, 'am');
			}

			if ($data_department_afternoon) {
				$data = $this->handle_row_data($data_department_afternoon, $this->fields_afternoon);
				if ($i == 0) {
					$table_afternoon = '<tr><th ' . (count($data) > 1 ? 'rowspan="' . count($data) . '"' : '') . '>' . $department . '</th>';
				} else {
					$table_afternoon .= '<tr><th ' . (count($data) > 1 ? 'rowspan="' . count($data) . '"' : '') . '>' . $department . '</th>';
				}

				$table_afternoon .= $this->handle_display($data, 'pm');
			}

			if ($data_department_evening) {
				$data = $this->handle_row_data($data_department_evening, $this->fields_evening);
				if ($i == 0) {
					$table_evening = '<tr><th ' . (count($data) > 1 ? 'rowspan="' . count($data) . '"' : '') . '>' . $department . '</th>';
				} else {
					$table_evening .= '<tr><th ' . (count($data) > 1 ? 'rowspan="' . count($data) . '"' : '') . '>' . $department . '</th>';
				}

				$table_evening .= $this->handle_display($data, 'ev');
			}
		}

		return array(
			'table_morning' => htmlspecialchars($table_morning),
			'table_afternoon' => htmlspecialchars($table_afternoon),
			'table_evening' => htmlspecialchars($table_evening),
		);
	}

	public function handle_display($data, $type)
	{
		$result = '';
		$column = 6;
		for ($i = 0; $i < count($data); $i++) {
			for ($j = 1; $j <= $column; $j++) {
				$result .= '<td>' . htmlspecialchars_decode($data[$i][$type . '_week_' . $j]) . '</td>';
			}
			$result .= '</tr>';
		}
		return $result;
	}

	public function handle_row_data($data, $fields)
	{
		$result = array();
		foreach ($data as $value) {
			$data_fields = array_intersect_key($value, array_flip($fields));
			$subArrays = array();
			foreach ($data_fields as $key => $value) {
				$subArrays[$key] = explode(',', $value);
			}
			$maxCount = max(array_map('count', $subArrays));
			for ($i = 0; $i < $maxCount; $i++) {
				$newRow = array_map(function ($subArray) use ($i) {
					return isset($subArray[$i]) ? $subArray[$i] : '';
				}, $subArrays);
				array_push($result, $newRow);
			}
		}
		return $result;
	}

	public function check_fields_data($item, $fields)
	{
		foreach ($fields as $field) {
			if ($item[$field]) {
				return true;
			}
		}
		return false;
	}
}
