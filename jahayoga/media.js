var my_id = 1;
//var now_dir = "inst-"+my_id+"/";
var now_dir = "";
var dear_id = "";
var q = getUrlVars();
if(q["dir"] != undefined){
	dear_id = q["dir"];
}
var FileBrowserDialogue = {
	init : function() {
		// 独自のonLoad処理が必要な場合ここにコードを記載
	},
	mySubmit : function(URL,alt) {
		// 選択されたファイルパスをTinyMCEに渡します。
		parent.tinymce.activeEditor.windowManager.getParams().setUrl(URL, alt);

		// TinyMCEダイアログに対して画像サイズの項目設定および更新を指示します。
//		var t = parent.tinymce.activeEditor.windowManager.windows[0];
//		t.find('#src').fire('change');

		// ポップアップウィンドウを閉じます。
		parent.tinymce.activeEditor.windowManager.close();
	}
};



$(function() {

	call_ajax_init();
	var cropperInstance;
	var $dirHtmlArea = $('.dirHtmlArea');

	/**
	 * 初期処理
	 */
	function call_ajax_init() {

		var fd = new FormData();
		fd.append("method", "get_file");

		ajaxExec(fd,function(data){
			if (data.status) {
				$('.pankuzu').append(data.pankuzu);
				$dirHtmlArea.html(data.html + '<div class="backg"></div>').on({
					"contextmenu": function() {
						return false;
					}
				});
				clickBind();
				dragBind();
			}
		});
	}

	/**
	 * 各種クリックバインド
	 */
	function clickBind() {
		$('.backg').off(".dm").on({
			"contextmenu.dm": function(e) {
				menuShow($(this), e);
			},
		});
		$(".dir").off(".dm").on({
			"contextmenu.dm": function(e) {
				menuShow($(this), e);
			},
			"dblclick.dm": function(e) {
				var t, n, s, df, mid, alt;
				[t, n, s, df, mid, alt] = getPa($(this));
				var lo = "media.php?dir=" + mid;
				if(q["vi"] == "noleft")lo += "&vi=noleft";
				 location.href = lo;
			}
		});
		$(".file").off(".dm").on({
			"contextmenu.dm": function(e) {
				menuShow($(this), e);
			},
			"dblclick.dm": function(e) {
				var t, n, s, df, mid, alt;
				[t, n, s, df, mid, alt] = getPa($(this));

				if(q["vi"] == 'noleft'){
					FileBrowserDialogue.mySubmit(s,alt); // 選択されたファイルパスをTinyMCEに渡します。
				}else{
					window.open(s);
				}
			}
		});
	}


	/**
	 * ドラッグ系バインド
	 */
	function dragBind(){
		fileDragBind();
		htmlDragBind();
	}

	/**
	 * ドラッグ系バインド実処理
	 */
	function htmlDragBind(){
		var $move;
		var $toolTip;
		$('.fileBox').off(".fileMove").on({
			"dragstart.fileMove": function (ev) {
				$("body").off(".fileDrag");
				$move = $(ev.target)
				$('.fileBox.dir').off(".fileDrop").on({
					"dragover.fileDrop": function (ev) {
						ev.stopPropagation();
						ev.preventDefault();
						event.dataTransfer.dropEffect = 'move';
					},
//					"dragenter.fileDrop": function (ev) {
//						console.log("enter");
//						var toolTipClass = 'tooltip-'+getRandString();
//						$(ev.target).append('<div class="toolTipBoxWrap '+toolTipClass+'"><span class="toolTipBox">移動する</span></div>');
//						$toolTip = $('.' + toolTipClass);
//					},
//					"dragleave.fileDrop": function (ev) {
//						console.log($toolTip);
//						$toolTip.remove();
//					},
					"drop.fileDrop": function (ev) {
						moveDrop(ev, $move)
					},
				});

				$('.dropUpDir').off(".fileMove").on({
					"dragover.fileDrop": function (ev) {
						ev.stopPropagation();
						ev.preventDefault();
						event.dataTransfer.dropEffect = 'move';
					},
					"drop.fileMove": function (ev) {
						moveDrop(ev, $move)
					},
				});
			}
		});
	}

	function moveDrop(ev, $move){
		var $target = $(ev.target);
		if($target.hasClass("dropUpDir")){
			var t = $target.attr("t");
			var mid = $target.attr("mid");
		}else if(!$target.hasClass("fileBox")){
			$target = $target.parents(".fileBox");
			var t, n, s, df, mid, alt;
			[t, n, s, df, mid, alt] = getPa($target);
		}

		var fd = new FormData();
		fd.append("method","move_file");

		fd.append("to_t",t);
		fd.append("to_mid",mid);

		var m_t, m_n, m_s, m_df, m_mid, m_alt;
		[m_t, m_n, m_s, m_df, m_mid, m_alt] = getPa($move);

		fd.append("move_t",m_t);
		fd.append("move_mid",m_mid);

		if (mid == m_mid) {
			return false;  
		}

		ajaxExec(fd, function(){
			$move.remove();
			dragBind();
		});

	}

	/**
	 * ファイルドラッグ時
	 */
	function fileDragBind(){
		$("body").off(".fileDrag").on({
			"dragenter.fileDrag": function(){
				fileupload_form();
			},
		});

		$('.fileUpInput').off().on("change.upChange", function(){
			var files  = $(this).prop("files");
			fileUp(files);
		})

	}

	/**
	 * ファイルアップロード処理
	 */
	function fileupload_form(){
		$('.file-overlay, .file-upArea').css({display:"flex"})
		$('.file-overlay').off(".fileUp").on({
			"drop.fileUp dragover.fileUp": function (ev) {
				ev.stopPropagation();
				ev.preventDefault();
			},
			"click": function(){
				$('.file-overlay, .file-upArea').css({display:"none"})
			}
		});

		$('.file-upArea').off(".fileUp").on({
			"dragover.fileUp": function (ev) {
				ev.stopPropagation();
				ev.preventDefault();
				event.dataTransfer.dropEffect = 'copy';
			},
			"drop.fileUp": function (ev) {
				ev.stopPropagation();
				ev.preventDefault();
				var files = event.dataTransfer.files;
				fileUp(files);
			}
		});

	}



	/**
	 * ファイルアップ実処理
	 */
	function fileUp(files){
		if(files.length <= 0) return false;
		var fd = new FormData();
		fd.append("method", "fileUp");
		var ins = files.length
		for (var x = 0; x < ins; x++) {
		    fd.append("file" + x, files[x]);
		}
	    fd.append("df", "file");

		$('.upFileList').html("<i class='fa fa-spin fa-spinner text-primary fileloading' style='font-size: 70px;'></i>")
		ajaxExec(fd,function(data){
			if (data.status) {
				$('.fileloading').fadeOut("1000",function(){
					$('.fileloading').remove();
					$('.upFileList').html("このエリアをクリック、<br>もしくはファイルをここにドラッグしてください");
					$('.initUp').remove();
					$('.file-overlay, .file-upArea').css({display:"none"});
					$dirHtmlArea.append(data.html);
					clickBind();
					dragBind();
				});
			}
		});
	}

	/**
	 * 右クリメニュー表示
	 */
	function menuShow($elem, e) {
		console.log($elem);
		$('.dropdown-menu').css({
			left : e.pageX,
			top : e.pageY,
		}).show().attr({
			t : $elem.attr("t"),
			n : $elem.attr("n"),
			s : $elem.attr("s"),
			df : $elem.attr("df"),
			mid: $elem.attr("mid"),
			al: $elem.attr("al"),
		});
		if($elem.attr("df") == 'dir'){
			$('.cont_del').show();
			$('.cont_rename').show();
			$('.cont_edit').hide();
			$('.cont_newdir').hide();
			$('.cont_newfile').hide();
		}else if($elem.attr("df") == 'file'){
			$('.cont_del').show();
			$('.cont_rename').hide();
			$('.cont_edit').show();
			$('.cont_newdir').hide();
			$('.cont_newfile').hide();
		}else{
			$('.cont_del').hide();
			$('.cont_rename').hide();
			$('.cont_edit').hide();
			$('.cont_newdir').show();
			$('.cont_newfile').show();
		}
		menuBind();
		dropdownMenuFunc($elem);
	}

	/**
	 * 右クリメニュー操作処理
	 */
	function dropdownMenuFunc($elem) {
		$('.cont_del').off().on('click', function() {
			var t, n, s, df, mid, alt;
			[t, n, s, df, mid, alt] = getPa($(this));
			var addCom = (df == 'dir')? "\n※フォルダ内にあるファイルは削除されます。":"";
			swal({
				title : "削除する",
				text : "「" + n + "」を削除します。よろしいですか？" + addCom,
				type : "warning",
				showCancelButton : true,
				confirmButtonClass : 'btn-warning',
				confirmButtonText : "削除する",
				cancelButtonText : 'キャンセル',
				closeOnConfirm : false,
				closeOnCancel : false
			}, function(isConfirm) {
				if (isConfirm) {
					// 呼び出し前method定義
					var fd = new FormData();
					fd.append('method', "cont_del");
					fd.append('target', t);
					fd.append('name', n);
					fd.append('src', s);
					fd.append('media_id', mid);
					fd.append('df', df);
					ajaxExec(fd,function(){
						$elem.remove();
						swal.close();
					})
				} else {
					swal.close();
				}
			});
		});

		$('.cont_rename').off().on('click', function() {
			var t, n, s, df, mid, alt;
			[t, n, s, df, mid, alt] = getPa($(this));
			$('[name=dir_name_renew]').val(n);
			Custombox.open({target:"#rename-modal"});
			$('.dir_rename').off().on('click', function(){

				var new_name = $('[name=dir_name_renew]').val();
				var fd = new FormData();
				fd.append("method", "rename_dir");
				fd.append("new_name", new_name);
				fd.append("media_id", mid);
				ajaxExec(fd,function(data){
					$elem.attr("n", new_name)
					.find("p").text(new_name);
					Custombox.close();
				});
			});
		});


		$('.cont_edit').off().on('click', function() {
			var t, n, s, df, mid, alt;
			[t, n, s, df, mid, alt] = getPa($(this));

			$('.folderArea').hide();
			$('.imgEditArea').show();

			var $image = $('#cropperImg');
			$image.attr("src", s);
			$('[name=file_name]').val(n);
			$('[name=file_alt]').val(alt);
			$image.attr("src", s);
			cropperFunction ($image,mid);
		});

		$('.cont_newdir').off().on('click', function() {
			var t, n, s, df, mid, alt;
			[t, n, s, df, mid, alt] = getPa($(this));
			Custombox.open({target:"#newdir-modal"});
			$(".createDir").off().on("click", function(){
				var dir_name = $("[name=dir_name]").val();
				if(dir_name == null || dir_name == ''){
					alert("フォルダ名が入力されていません。");
					return false;
				}

				var fd = new FormData();
				fd.append("method", "cont_newdir");
				fd.append("dir_name", dir_name);
				fd.append("df", "dir");
				ajaxExec(fd,function(data){
					Custombox.close();
					$dirHtmlArea.prepend(data.html);
					$('.initUp').remove();
					clickBind();
					dragBind();
				});
			});
		});

		$('.cont_newfile').off().on('click', function() {
			fileupload_form();
		});


	}

	/**
	 * 右クリックメニュー非表示処理
	 */
	function menuBind() {
		$('body').off(".menuHide").on('click.menuHide', function(e) {
			$('.dropdown-menu').hide();
		});
	}



	/**
	 * ファイルくろっぱー
	 *
	 */
	function cropperFunction ($imageElem,mid){
		$imageElem.cropper('destroy');

		$imageElem.cropper({
			aspectRatio : 16 / 9,
			dragMode : "move",
			crop : function(event) {
//				console.log(event.detail.x);
//				console.log(event.detail.y);
//				console.log(event.detail.width);
//				console.log(event.detail.height);
//				console.log(event.detail.rotate);
//				console.log(event.detail.scaleX);
//				console.log(event.detail.scaleY);
			}
		});
		cropperInstance = $imageElem.data('cropper');


		$(".cropper-opt-ch").off().on("click", function(){
			var type = $(this).attr("ty");
			var val = $(this).attr("va");
			if(type == 'aspect'){
				if(val == '1')cropperInstance.setAspectRatio(1/1);
				if(val == '2')cropperInstance.setAspectRatio(16/9);
			}else if(type == 'rotate'){
				if(val == '1')cropperInstance.rotate(-15);
				if(val == '2')cropperInstance.rotate(15);
			}else if(type == 'export'){
				var data = cropperInstance.getData();
				data.option = {};
				data.option.fillColor = '#fff';
				result = $imageElem.cropper("getCroppedCanvas", data.option, data.secondOption);
				var uploadedImageType = 'image/jpeg';
				
				if (result != null) {
					var image = result.toDataURL(uploadedImageType)
				} else {
					var image = ""
				}
				

				var fd = new FormData();
				fd.append("img", image);
				fd.append("file_name", $('[name=file_name]').val());
				fd.append("file_alt", $('[name=file_alt]').val());
				fd.append("media_id", mid);

				if(val == '1'){
					fd.append("method", "override_image");
				}else if(val == '2'){
					fd.append("method", "rename_image");
				}else if(val == '3'){
					fd.append("method", "edit_image_name");
					fd.append('path',$('#cropperImg').attr('src'));
				}

				ajaxExec(fd,function(){
					swal({
						title : "Success",
						text : "変更されました。",
						type : "success",
						showCancelButton : false,
					}, function(isConfirm) {
						swal.close();
						$('.folderArea').show();
						$('.imgEditArea').hide();
						call_ajax_init();
					});
				});
			}

		});

	}





	/**
	 * 値取得
	 */
	function getPa($elem){
		var $p;
		if($elem.hasClass("fileBox")){
			$p = $elem;
		}else{
			$p = $elem.parents(".contextmenu_dir");
		}
		return [$p.attr("t"),$p.attr("n"),$p.attr("s"),$p.attr("df"),$p.attr("mid"),$p.attr("al")];
	}

	function getRandString(){
	                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            	return Math.random().toString(32).substring(2)
	}

	/**
	 * AJAX処理
	 */
	function ajaxExec(fd,callback){
	
	
	
		fd.append("dir", now_dir);
		fd.append("my_id", my_id);
		fd.append("dear_id", dear_id);
		ajax.get(fd).done(function(result) {
			loaded();
			if(typeof callback == 'function')callback(result.data);
		}).fail(function(result) {
			// 異常終了
			$('body').html(result.responseText);
		});
	}

});