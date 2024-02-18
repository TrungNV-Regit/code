<?php
require_once __DIR__ . "/../../logic/common/common_logic.php";

class media_logic{
	private $common_logic;
	private $path;
	private $uri;

	public function __construct(){
		$this->common_logic = new common_logic();
		$this->path = "/../..";
		$this->uri = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"];
		if($_SERVER["HTTP_HOST"] == 'localhost' || $_SERVER["HTTP_HOST"] == '2floor.xyz')$this->uri .= "/jaha_main";
	}


	/**
	 * ファイル情報初期HTML取得
	 */
	public function get_file($post){

		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);

		$file_ar = $this->getFileList($now_dir);
		$media_ar = $this->getFileMedia($post['my_id'], $post['dear_id']);

		$html = '';
		foreach ((array)$file_ar['dir_list'] as $value) {
			$file_url = str_replace($now_dir, $this->uri, $value);
			$file_name = str_replace($now_dir, "", $value);

			$med = $media_ar['dir'][$file_name];
			$med['name'] = preg_replace('/\//', '',$file_name);

			$html .= '
				<div class="fileBox dir" draggable="true" t="'.$file_name.'" n="'.$med['name'].'" src="'.$file_url.'" df="dir" mid="'.$med['media_id'].'">
					<div class="fileBoxIcons">
						<i class="fa fa-folder" draggable="false"></i>
					</div>
					<p>'.$med['name'].'</p>
				</div>';
		}
		foreach ((array)$file_ar['list'] as $value) {
			$file_url = str_replace(__DIR__ .$this->path, $this->uri, $value);
			$file_name = str_replace($now_dir, "", $value);

			$med = $media_ar['file'][$file_name];
			$med['name'] = preg_replace('/\//', '',$file_name);

			$html .= '
				<div class="fileBox file" draggable="true" t="'.$file_name.'" n="'.$med['name'].'" al="'.$med['alt'].'" s="'.$file_url.'" df="file" mid="'.$med['media_id'].'">
					<div class="fileBoxIcons">
						<img src="'.$file_url.'" draggable="false">
					</div>
					<p>'.$med['name'].'</p>
				</div>';
		}

		if($html == null || $html == ''){
			$html = '<div style="width: 100%; height: 300px; display:flex; justify-content:center; align-items:center;" class="initUp">まだファイルがアップロードされていません。</div>';
		}

		return array(
				"status" =>true,
				"html" =>$html,
				"pankuzu" =>$now_dir_str,
		);

	}



	/**
	 * ファイルアップロード処理
	 */
	public function fileUp($post, $files){
		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);
		$result = array();
		foreach ($files as $key => $file) {
			$result[$key] = $this->common_logic->front_unit_file_upload($file, $now_dir);
		}

		$html = '';
		if($post['dear_id'] == null || $post['dear_id'] == '')$post['dear_id'] = null;
		foreach ($result as $key => $uploaded) {
			$file_url =  $this->uri . $now_dir_rel . $uploaded['file_name'];
			$file_name = $uploaded['file_name'];

			$media_id = $this->common_logic->insert_logic("t_media", array(
					$post['my_id'],
					$post['df'], //$post['type'],
					$uploaded['file_name'],//$post['base'],
					$uploaded['file_name'],//$post['name'],
					$uploaded['file_name'],//$post['alt'],
					$post['dear_id'],
			), "media_id");

			$html .= '
				<div class="fileBox file" draggable="true" t="'.$file_name.'" n="'.$file_name.'" al="'.$file_name.'"  s="'.$file_url.'" df="file" mid="'.$media_id.'">
					<div class="fileBoxIcons">
						<img src="'.$file_url.'" draggable="false">
					</div>
					<p>'.$file_name.'</p>
				</div>';

		}

		return array(
				"status" =>true,
				"uploaded" =>$result,
				"html" =>$html,
		);

	}

	/**
	 * ファイル削除処理
	 */
	public function cont_del($post){
		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);
		$filepath = $now_dir.$post['target'];

		if($post['df'] == 'dir'){
			$file_ar = $this->getFileListAll($filepath);
			foreach ((array)$file_ar["list"] as $f){
				unlink($f);
			}
			foreach ((array)$file_ar["dir_list"] as $f){
				rmdir($f);
			}
			rmdir($filepath);

		}else{
			$filepath = $now_dir.$post['target'];
			unlink($filepath);
		}
		return array(
				"status" =>true,
		);
	}


	/**
	 * 新規ディレクトリ作成
	 */
	public function cont_newdir($post){
		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);
		$real_dir = ceil(microtime(true)) . "-" . $this->common_logic->getRandomString(20);
		$real_path = $now_dir.$real_dir;
		$dir_path = $now_dir.$post['dir_name'];

		mkdir($real_path);
		chmod($real_path, 0755);

		$file_url = $dir_path;
		$file_name = $post['dir_name'];

		if($post['dear_id'] == null || $post['dear_id'] == '')$post['dear_id'] = null;
		$media_id = $this->common_logic->insert_logic("t_media", array(
				$post['my_id'],
				$post['df'], //$post['type'],
				$real_dir,//$post['base'],
				$post['dir_name'],//$post['name'],
				null,//$post['alt'],
				$post['dear_id'],
		),"media_id");

		$html = '
				<div class="fileBox dir" draggable="true" t="'.$file_name.'" n="'.$file_name.'" src="'.$file_url.'" df="dir" mid="'.$media_id.'">
					<div class="fileBoxIcons">
						<i class="fa fa-folder" draggable="false"></i>
					</div>
					<p>'.$file_name.'</p>
				</div>';

		return array(
				"status" =>true,
				"html" =>$html,
		);
	}

	/**
	 * ディレクトリ名変更
	 */
	public function rename_dir($post){
		$this->common_logic->update_logic("t_media", " where media_id = ? ", array("name"), array($post['new_name'],$post['media_id']));
		return array(
				"status" =>true,
		);
	}

	/**
	 * 画像上書き
	 */
	public function override_image($post){
		$basefile_data = $this->common_logic->select_logic("select * from t_media where media_id = ? ", array($post['media_id']));

		$basefile_name = $basefile_data[0]['base'];
		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);

// 		$this->createImageData($post['img'], $now_dir.$basefile_name);

		$this->common_logic->update_logic("t_media", " where media_id = ? ", array("name","alt"), array(
				$post['file_name'],
				$post['file_alt'],
				$post['media_id'],
		));


		return array(
				"status" =>true,
		);

	}

	/**
	 * ファイル移動処理
	 */
	public function move_file($post){

		$from_data = $this->common_logic->select_logic("select * from t_media where media_id = ? ", array($post['move_mid']));
		$from_file_name = $from_data[0]['base'];
		list($from_dir, $from_dir_str, $from_dir_rel) = $this->getNowDir(array(
				"dir"=> $post['dir'],
				"dear_id"=> $from_data[0]['dear_id']
		));
		$from_path = $from_dir;

		$to_data_dir = $this->common_logic->select_logic("select * from t_media where media_id = ? ", array($post['to_mid']));
		$to_dir_name = $to_data_dir[0]['base'];
		list($to_dir, $to_dir_str, $to_dir_rel) = $this->getNowDir(array(
				"dir"=> $post['dir'],
				"dear_id"=> $to_data_dir[0]['dear_id']
		));
		$to_path = $to_dir.$to_dir_name."/";


// 		$from_all = array();
// 		if($from_data[0]['type'] == 'dir'){
// 			$from_all_base = $this->getFileList($now_dir.$from_file_name);
// 			foreach ($from_all_base as $type => $df) {
// 				foreach ($df as $name) {
// 					$n_ar = explode("/", $name);
// 					array_push($from_all, array_pop($n_ar));
// 				}
// 			}
// 		}

		rename($from_path.$from_file_name, $to_path.$from_file_name);

		if($post['to_mid'] == null || $post['to_mid'] == '' || $post['to_mid'] == 'null') $post['to_mid'] = null;
		$this->common_logic->update_logic("t_media", " where media_id = ? ", array("dear_id"), array($post['to_mid'],$post['move_mid']));

		return array(
				"status" =>true,
		);


	}



	/**
	 * 画像リネーム処理
	 */
	public function rename_image($post){
		$basefile_data = $this->common_logic->select_logic("select * from t_media where media_id = ? ", array($post['media_id']));

		$basefile_name = $basefile_data[0]['base'];
		
		var_dump($basefile_name);
		exit();
		
		
		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);

		// 		$this->createImageData($post['img'], $now_dir.$basefile_name);

		$this->common_logic->update_logic("t_media", " where media_id = ? ", array("name","alt"), array(
				$post['file_name'],
				$post['file_alt'],
				$post['media_id'],
		));


		return array(
				"status" =>true,
		);

	}



	/**
	 * 画像名のみ変更
	 * @param unknown $post
	 * @return boolean[]
	 */
	public function edit_image_name($post){
		$basefile_data = $this->common_logic->select_logic("select * from t_media where media_id = ? ", array($post['media_id']));

		$basefile_name = $basefile_data[0]['base'];
		list($now_dir, $now_dir_str, $now_dir_rel) = $this->getNowDir($post);
echo '<pre>';
var_dump($post);
die();
		$oldName = $_SERVER['DOCUMENT_ROOT'].str_replace($this->uri,'',$post['path']);
		$newName = str_replace(basename($oldName), $post['file_name'], $oldName);
        rename($oldName , $newName);

		// 		$this->createImageData($post['img'], $now_dir.$basefile_name);

		$this->common_logic->update_logic("t_media", " where media_id = ? ", array("name","alt"), array(
				$post['file_name'],
				$post['file_alt'],
				$post['media_id'],
		));


		return array(
				"status" =>true,
		);

	}





	/**
	 * 現在のディレクトリ取得
	 */
	public function getNowDir($post){
		$now_dir_rel =  "/upload_files/media/" . $post['dir'];
		$now_dir = __DIR__ .$this->path;


		$dir_path = $this->createDirPath($post['dear_id']);

		$now_dir_rel .= $dir_path["now_dir_rel"];

		$now_dir .= $now_dir_rel;
		$now_dir_pankuzu = '';
		foreach ((array)$dir_path['dear_ar'] as $v) {
			$now_dir_pankuzu .= '<li><a class="dropUpDir" t="'.$v['base'].'" mid="'.$v['media_id'].'" href="media.php?dir='.$v['media_id'].'">'.$v['name'].'</a></li>';
		}

		return array($now_dir, $now_dir_pankuzu, $now_dir_rel);
	}


	public function createDirPath($dear_id){
			$c = 0;
			$dear_ar = array();
			$dear_ar_real = array();
			$dear_id = $dear_id;
			$flg = true;
			do{
				$dear_dir = $this->common_logic->select_logic("select `media_id`,`base`,`name`,`dear_id` from t_media where media_id = ? ", array($dear_id));
				if($dear_dir != null && $dear_dir != ''){
					array_unshift($dear_ar, array("name" => $dear_dir[0]["name"],"media_id" => $dear_dir[0]["media_id"],"base" => $dear_dir[0]["base"]));
					array_unshift($dear_ar_real, $dear_dir[0]["base"]);
				}
				if($dear_dir[0]["dear_id"] != null && $dear_dir[0]["dear_id"] != '' && $dear_dir[0]["dear_id"] != '0'){
					$dear_id = $dear_dir[0]["dear_id"];
				}else{
					$flg = false;
				}
				++$c;
			}while($c < 100 && $flg);

			$now_dir_rel = '';
			if($dear_ar_real[0] != null && $dear_ar_real[0] != '')$now_dir_rel .= implode("/", $dear_ar_real) . "/";

			return array(
					'now_dir_rel' => $now_dir_rel,
					'dear_ar' => $dear_ar,
			);

	}



	/**
	 * ファイル一覧取得（単階層）
	 */
	public function getFileList($dir) {
		$files = scandir($dir);
		rsort($files);
		$files = array_filter($files, function ($file) {
			return !in_array($file, array('.', '..'));
		});
		$list = array();
		$dir_list = array();
		foreach ($files as $file) {
			$fullpath = rtrim($dir, '/') . '/' . $file;
			if (is_file($fullpath)) {
				$list[] = $fullpath;
			}
			if (is_dir($fullpath)) {
				$dir_list[] = $fullpath;
			}
		}
		return array("list" => $list,"dir_list" => $dir_list);
	}
	/**
	 * ファイル一覧取得（多階層）
	 */
	public function getFileListAll($dir) {

		$files = scandir($dir);
		$files = array_filter($files, function ($file) {
			return !in_array($file, array('.', '..'));
		});
			$list = array();
			$dir_list = array();
			foreach ($files as $file) {
				$fullpath = rtrim($dir, '/') . '/' . $file;
				if (is_file($fullpath)) {
					$list[] = $fullpath;
				}
				if (is_dir($fullpath)) {
					$inner = $this->getFileListAll($fullpath);
					$dir_list = array_merge($dir_list, $inner['dir_list']);
					$dir_list[] = $fullpath;
					$list = array_merge($list, $inner['list']);
				}
			}
			return array("list" => $list,"dir_list" => $dir_list);
	}

	/**
	 * メディアテーブル情報取得
	 */
	public function getFileMedia($my_id, $dear_id = null ) {
		$whereParam = array($my_id);
		$where = '';
		if($dear_id != null){
			$where = " AND dear_id = ? ";
			array_push($whereParam, $dear_id);
		}
		$media_base = $this->common_logic->select_logic("select * from t_media where my_id = ? " . $where, $whereParam);

		$media_ar = array(
				'dir' => array(),
				'file' => array(),
		);
		foreach ((array)$media_base as $mb) {
			$media_ar[$mb['type']][$mb['base']] = $mb;
		}

		return $media_ar;
	}

	/**
	 * 画像生成
	 */
	public function createImageData($image_binary, $file_path){
		$canvas = preg_replace("/data:[^,]+,/i","",$image_binary);
		$canvas = base64_decode($canvas);
		$image = imagecreatefromstring($canvas);
		imagesavealpha($image, TRUE); // 透明色の有効


// 		$fn_a = explode(".", $file["name"]);
// 		$ext = array_pop($fn_a);
// 		$fileName = ceil(microtime(true)) . '-' .$this->getRandomString(5) . "." . $ext;
// 		if(){

// 		}


		imagepng($image ,$file_path);
	}


}