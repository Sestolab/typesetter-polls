function gp_init_inline_edit(area_id, section_object){

	gp_editor = {
		edit_div: gp_editing.get_edit_area(area_id),
		save_path: gp_editing.get_path(area_id),
		isDirty: false,
		checkDirty: function(){
			return gp_editor.isDirty;
		},
		resetDirty: function(){},
		SaveData: function (){
			var content = $('#polls-editor-options select').serialize();
			$gp.postC(window.location.href, 'cmd=RefreshPollSection&'+content);
			gp_editor.isDirty = false;
			return content;
		},
		ui: { controls: {} },
	};

	gp_editing.editor_tools();

	gp_editor.ui.option_area = $('<div id="polls-editor-options">').prependTo('#ckeditor_controls');

	$('<p>'+PollsLang['select_poll']+'</p>').appendTo(gp_editor.ui.option_area);

	gp_editor.ui.controls.poll_id = $('<select name="poll_id" class="ckeditor_control">').appendTo(gp_editor.ui.option_area);

	$.each(PollsEditor.polls, function(i, q){
		$('<option value="'+i+'">'+q+'</option>').appendTo(gp_editor.ui.controls.poll_id);
	});

	if (section_object.poll_id)
		gp_editor.ui.controls.poll_id.val(section_object.poll_id);
	else
		gp_editor.isDirty = true;

	gp_editor.ui.controls.poll_id.on('change', function(){
		gp_editor.isDirty = true;
	});

	$('<a class="ckeditor_control" href="'+gpBLink+'/Admin_Polls">'+PollsLang['new']+'</a>').appendTo('#ckeditor_bottom');

	$gp.response.refresh_poll_section = function(arg){
		gp_editor.edit_div.html(arg.CONTENT);
	};

	loaded();
}

