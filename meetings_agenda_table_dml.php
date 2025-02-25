<?php

// Data functions (insert, update, delete, form) for table meetings_agenda_table

// This script and data application was generated by AppGini, https://bigprof.com/appgini
// Download AppGini for free from https://bigprof.com/appgini/download/

function meetings_agenda_table_insert(&$error_message = '') {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('meetings_agenda_table');
	if(!$arrPerm['insert']) {
		$error_message = $Translation['no insert permission'];
		return false;
	}

	// automatic meeting_lookup if passed as filterer
	if(Request::val('filterer_meeting_lookup')) {
		$_REQUEST['meeting_lookup'] = Request::val('filterer_meeting_lookup');
	}

	$data = [
		'agenda_description' => br2nl(Request::val('agenda_description', '')),
		'created_by' => parseCode('<%%creatorUsername%%>', true),
		'created_at' => parseCode('<%%creationDateTime%%>', true),
	];


	// automatic meeting_lookup if passed as filterer
	if(Request::val('filterer_meeting_lookup')) {
		$data['meeting_lookup'] = Request::val('filterer_meeting_lookup');
	}
	// record owner is current user
	$recordOwner = getLoggedMemberID();

	$recID = tableInsert('meetings_agenda_table', $data, $recordOwner, $error_message);

	// if this record is a copy of another record, copy children if applicable
	if(strlen(Request::val('SelectedID')) && $recID !== false)
		meetings_agenda_table_copy_children($recID, Request::val('SelectedID'));

	return $recID;
}

function meetings_agenda_table_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = []; // array of curl handlers for launching insert requests
	$eo = ['silentErrors' => true];
	$safe_sid = makeSafe($source_id);
	$currentUsername = getLoggedMemberID();
	$errorMessage = '';

	// launch requests, asynchronously
	curl_batch($requests);
}

function meetings_agenda_table_delete($selected_id, $AllowDeleteOfParents = false, $skipChecks = false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id = makeSafe($selected_id);

	// mm: can member delete record?
	if(!check_record_permission('meetings_agenda_table', $selected_id, 'delete')) {
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: meetings_agenda_table_before_delete
	if(function_exists('meetings_agenda_table_before_delete')) {
		$args = [];
		if(!meetings_agenda_table_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'] . (
				!empty($args['error_message']) ?
					'<div class="text-bold">' . strip_tags($args['error_message']) . '</div>'
					: '' 
			);
	}

	// child table: meetings_decision_table
	$res = sql("SELECT `id` FROM `meetings_agenda_table` WHERE `id`='{$selected_id}'", $eo);
	$id = db_fetch_row($res);
	$rires = sql("SELECT COUNT(1) FROM `meetings_decision_table` WHERE `agenda_lookup`='" . makeSafe($id[0]) . "'", $eo);
	$rirow = db_fetch_row($rires);
	$childrenATag = '<a class="alert-link" href="meetings_decision_table_view.php?filterer_agenda_lookup=' . urlencode($id[0]) . '">%s</a>';
	if($rirow[0] && !$AllowDeleteOfParents && !$skipChecks) {
		$RetMsg = $Translation["couldn't delete"];
		$RetMsg = str_replace('<RelatedRecords>', sprintf($childrenATag, $rirow[0]), $RetMsg);
		$RetMsg = str_replace(['[<TableName>]', '<TableName>'], sprintf($childrenATag, 'meetings_decision_table'), $RetMsg);
		return $RetMsg;
	} elseif($rirow[0] && $AllowDeleteOfParents && !$skipChecks) {
		$RetMsg = $Translation['confirm delete'];
		$RetMsg = str_replace('<RelatedRecords>', sprintf($childrenATag, $rirow[0]), $RetMsg);
		$RetMsg = str_replace(['[<TableName>]', '<TableName>'], sprintf($childrenATag, 'meetings_decision_table'), $RetMsg);
		$RetMsg = str_replace('<Delete>', '<input type="button" class="btn btn-danger" value="' . html_attr($Translation['yes']) . '" onClick="window.location = `meetings_agenda_table_view.php?SelectedID=' . urlencode($selected_id) . '&delete_x=1&confirmed=1&csrf_token=' . urlencode(csrf_token(false, true)) . (Request::val('Embedded') ? '&Embedded=1' : '') . '`;">', $RetMsg);
		$RetMsg = str_replace('<Cancel>', '<input type="button" class="btn btn-success" value="' . html_attr($Translation[ 'no']) . '" onClick="window.location = `meetings_agenda_table_view.php?SelectedID=' . urlencode($selected_id) . (Request::val('Embedded') ? '&Embedded=1' : '') . '`;">', $RetMsg);
		return $RetMsg;
	}

	sql("DELETE FROM `meetings_agenda_table` WHERE `id`='{$selected_id}'", $eo);

	// hook: meetings_agenda_table_after_delete
	if(function_exists('meetings_agenda_table_after_delete')) {
		$args = [];
		meetings_agenda_table_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("DELETE FROM `membership_userrecords` WHERE `tableName`='meetings_agenda_table' AND `pkValue`='{$selected_id}'", $eo);
}

function meetings_agenda_table_update(&$selected_id, &$error_message = '') {
	global $Translation;

	// mm: can member edit record?
	if(!check_record_permission('meetings_agenda_table', $selected_id, 'edit')) return false;

	$data = [
		'agenda_description' => br2nl(Request::val('agenda_description', '')),
		'last_updated_by' => parseCode('<%%editorUsername%%>', false),
		'last_updated_at' => parseCode('<%%editingDateTime%%>', false),
	];

	// get existing values
	$old_data = getRecord('meetings_agenda_table', $selected_id);
	if(is_array($old_data)) {
		$old_data = array_map('makeSafe', $old_data);
		$old_data['selectedID'] = makeSafe($selected_id);
	}

	$data['selectedID'] = makeSafe($selected_id);

	// hook: meetings_agenda_table_before_update
	if(function_exists('meetings_agenda_table_before_update')) {
		$args = ['old_data' => $old_data];
		if(!meetings_agenda_table_before_update($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$set = $data; unset($set['selectedID']);
	foreach ($set as $field => $value) {
		$set[$field] = ($value !== '' && $value !== NULL) ? $value : NULL;
	}

	if(!update(
		'meetings_agenda_table', 
		backtick_keys_once($set), 
		['`id`' => $selected_id], 
		$error_message
	)) {
		echo $error_message;
		echo '<a href="meetings_agenda_table_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
		exit;
	}


	update_calc_fields('meetings_agenda_table', $data['selectedID'], calculated_fields()['meetings_agenda_table']);

	// hook: meetings_agenda_table_after_update
	if(function_exists('meetings_agenda_table_after_update')) {
		if($row = getRecord('meetings_agenda_table', $data['selectedID'])) $data = array_map('makeSafe', $row);

		$data['selectedID'] = $data['id'];
		$args = ['old_data' => $old_data];
		if(!meetings_agenda_table_after_update($data, getMemberInfo(), $args)) return;
	}

	// mm: update record update timestamp
	set_record_owner('meetings_agenda_table', $selected_id);
}

function meetings_agenda_table_form($selectedId = '', $allowUpdate = true, $allowInsert = true, $allowDelete = true, $separateDV = true, $templateDV = '', $templateDVP = '') {
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selectedId. If $selectedId
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;
	$eo = ['silentErrors' => true];
	$noUploads = $row = $urow = $jsReadOnly = $jsEditable = $lookups = null;
	$noSaveAsCopy = false;
	$hasSelectedId = strlen($selectedId) > 0;

	// mm: get table permissions
	$arrPerm = getTablePermissions('meetings_agenda_table');
	$allowInsert = ($arrPerm['insert'] ? true : false);
	$allowUpdate = $hasSelectedId && check_record_permission('meetings_agenda_table', $selectedId, 'edit');
	$allowDelete = $hasSelectedId && check_record_permission('meetings_agenda_table', $selectedId, 'delete');

	if(!$allowInsert && !$hasSelectedId)
		// no insert permission and no record selected
		// so show access denied error -- except if TVDV: just hide DV
		return $separateDV ? $Translation['tableAccessDenied'] : '';

	if($hasSelectedId && !check_record_permission('meetings_agenda_table', $selectedId, 'view'))
		return $Translation['tableAccessDenied'];

	// print preview?
	$dvprint = $hasSelectedId && Request::val('dvprint_x') != '';

	$showSaveNew = !$dvprint && ($allowInsert && !$hasSelectedId);
	$showSaveChanges = !$dvprint && $allowUpdate && $hasSelectedId;
	$showDelete = !$dvprint && $allowDelete && $hasSelectedId;
	$showSaveAsCopy = !$dvprint && ($allowInsert && $hasSelectedId && !$noSaveAsCopy);
	$fieldsAreEditable = !$dvprint && (($allowInsert && !$hasSelectedId) || ($allowUpdate && $hasSelectedId) || $showSaveAsCopy);

	$filterer_meeting_lookup = Request::val('filterer_meeting_lookup');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: meeting_lookup
	$combo_meeting_lookup = new DataCombo;

	if($hasSelectedId) {
		if(!($row = getRecord('meetings_agenda_table', $selectedId))) {
			return error_message($Translation['No records found'], 'meetings_agenda_table_view.php', false);
		}
		$combo_meeting_lookup->SelectedData = $row['meeting_lookup'];
		$urow = $row; /* unsanitized data */
		$row = array_map('safe_html', $row);
	} else {
		$filterField = Request::val('FilterField');
		$filterOperator = Request::val('FilterOperator');
		$filterValue = Request::val('FilterValue');
		$combo_meeting_lookup->SelectedData = $filterer_meeting_lookup;
	}
	$combo_meeting_lookup->HTML = '<span id="meeting_lookup-container' . $rnd1 . '"></span><input type="hidden" name="meeting_lookup" id="meeting_lookup' . $rnd1 . '" value="' . html_attr($combo_meeting_lookup->SelectedData) . '">';
	$combo_meeting_lookup->MatchText = '<span id="meeting_lookup-container-readonly' . $rnd1 . '"></span><input type="hidden" name="meeting_lookup" id="meeting_lookup' . $rnd1 . '" value="' . html_attr($combo_meeting_lookup->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_meeting_lookup__RAND__ = { text: "", value: "<?php echo addslashes($hasSelectedId ? $urow['meeting_lookup'] : htmlspecialchars($filterer_meeting_lookup, ENT_QUOTES)); ?>"};

		$j(function() {
			setTimeout(function() {
				if(typeof(meeting_lookup_reload__RAND__) == 'function') meeting_lookup_reload__RAND__();
			}, 50); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function meeting_lookup_reload__RAND__() {
		<?php if($fieldsAreEditable) { ?>

			$j("#meeting_lookup-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_meeting_lookup__RAND__.value, t: 'meetings_agenda_table', f: 'meeting_lookup' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="meeting_lookup"]').val(resp.results[0].id);
							$j('[id=meeting_lookup-container-readonly__RAND__]').html('<span class="match-text" id="meeting_lookup-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=meetings_table_view_parent]').hide(); } else { $j('.btn[id=meetings_table_view_parent]').show(); }


							if(typeof(meeting_lookup_update_autofills__RAND__) == 'function') meeting_lookup_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { return { s: term, p: page, t: 'meetings_agenda_table', f: 'meeting_lookup' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_meeting_lookup__RAND__.value = e.added.id;
				AppGini.current_meeting_lookup__RAND__.text = e.added.text;
				$j('[name="meeting_lookup"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=meetings_table_view_parent]').hide(); } else { $j('.btn[id=meetings_table_view_parent]').show(); }


				if(typeof(meeting_lookup_update_autofills__RAND__) == 'function') meeting_lookup_update_autofills__RAND__();
			});

			if(!$j("#meeting_lookup-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_meeting_lookup__RAND__.value, t: 'meetings_agenda_table', f: 'meeting_lookup' },
					success: function(resp) {
						$j('[name="meeting_lookup"]').val(resp.results[0].id);
						$j('[id=meeting_lookup-container-readonly__RAND__]').html('<span class="match-text" id="meeting_lookup-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=meetings_table_view_parent]').hide(); } else { $j('.btn[id=meetings_table_view_parent]').show(); }

						if(typeof(meeting_lookup_update_autofills__RAND__) == 'function') meeting_lookup_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_meeting_lookup__RAND__.value, t: 'meetings_agenda_table', f: 'meeting_lookup' },
				success: function(resp) {
					$j('[id=meeting_lookup-container__RAND__], [id=meeting_lookup-container-readonly__RAND__]').html('<span class="match-text" id="meeting_lookup-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=meetings_table_view_parent]').hide(); } else { $j('.btn[id=meetings_table_view_parent]').show(); }

					if(typeof(meeting_lookup_update_autofills__RAND__) == 'function') meeting_lookup_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_clean());


	// code for template based detail view forms

	// open the detail view template
	if($dvprint) {
		$template_file = is_file("./{$templateDVP}") ? "./{$templateDVP}" : './templates/meetings_agenda_table_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	} else {
		$template_file = is_file("./{$templateDV}") ? "./{$templateDV}" : './templates/meetings_agenda_table_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Detail View', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', (Request::val('Embedded') ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($showSaveNew) {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
	} elseif($showSaveAsCopy) {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if(Request::val('Embedded')) {
		$backAction = 'AppGini.closeParentModal(); return false;';
	} else {
		$backAction = 'return true;';
	}

	if($hasSelectedId) {
		if(!Request::val('Embedded')) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($allowUpdate)
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		else
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);

		if($allowDelete)
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		else
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);

		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);

		// if not in embedded mode and user has insert only but no view/update/delete,
		// remove 'back' button
		if(
			$allowInsert
			&& !$allowUpdate && !$allowDelete && !$arrPerm['view']
			&& !Request::val('Embedded')
		)
			$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '', $templateCode);
		elseif($separateDV)
			$templateCode = str_replace(
				'<%%DESELECT_BUTTON%%>', 
				'<button
					type="submit" 
					class="btn btn-default" 
					id="deselect" 
					name="deselect_x" 
					value="1" 
					onclick="' . $backAction . '" 
					title="' . html_attr($Translation['Back']) . '">
						<i class="glyphicon glyphicon-chevron-left"></i> ' .
						$Translation['Back'] .
				'</button>',
				$templateCode
			);
		else
			$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '', $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(!$fieldsAreEditable) {
		$jsReadOnly = '';
		$jsReadOnly .= "\t\$j('#agenda_description').replaceWith('<div class=\"form-control-static\" id=\"agenda_description\">' + (\$j('#agenda_description').val() || '') + '</div>');\n";
		$jsReadOnly .= "\t\$j('.select2-container').hide();\n";

		$noUploads = true;
	} else {
		// temporarily disable form change handler till time and datetime pickers are enabled
		$jsEditable = "\t\$j('form').eq(0).data('already_changed', true);";
		$jsEditable .= "\t\$j('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(meeting_lookup)%%>', $combo_meeting_lookup->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(meeting_lookup)%%>', $combo_meeting_lookup->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(meeting_lookup)%%>', urlencode($combo_meeting_lookup->MatchText), $templateCode);

	/* lookup fields array: 'lookup field name' => ['parent table name', 'lookup field caption'] */
	$lookup_fields = ['meeting_lookup' => ['meetings_table', 'Meeting'], ];
	foreach($lookup_fields as $luf => $ptfc) {
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if(($pt_perm['view'] && isDetailViewEnabled($ptfc[0])) || $pt_perm['edit']) {
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] /* && !Request::val('Embedded')*/) {
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-default add_new_parent" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus text-success"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(meeting_lookup)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(agenda_description)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(created_by)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(created_at)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(last_updated_by)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(last_updated_at)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(meetings_agenda_str)%%>', '', $templateCode);

	// process values
	if($hasSelectedId) {
		$templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		$templateCode = str_replace('<%%VALUE(meeting_lookup)%%>', safe_html($urow['meeting_lookup']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(meeting_lookup)%%>', urlencode($urow['meeting_lookup']), $templateCode);
		$templateCode = str_replace('<%%VALUE(agenda_description)%%>', safe_html($urow['agenda_description'], $fieldsAreEditable), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(agenda_description)%%>', urlencode($urow['agenda_description']), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_by)%%>', safe_html($urow['created_by']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_by)%%>', urlencode($urow['created_by']), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_at)%%>', safe_html($urow['created_at']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_at)%%>', urlencode($urow['created_at']), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_by)%%>', safe_html($urow['last_updated_by']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_by)%%>', urlencode($urow['last_updated_by']), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_at)%%>', safe_html($urow['last_updated_at']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_at)%%>', urlencode($urow['last_updated_at']), $templateCode);
		$templateCode = str_replace('<%%VALUE(meetings_agenda_str)%%>', safe_html($urow['meetings_agenda_str']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(meetings_agenda_str)%%>', urlencode($urow['meetings_agenda_str']), $templateCode);
	} else {
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(meeting_lookup)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(meeting_lookup)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(agenda_description)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(agenda_description)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_by)%%>', '<%%creatorUsername%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_by)%%>', urlencode('<%%creatorUsername%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_at)%%>', '<%%creationDateTime%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_at)%%>', urlencode('<%%creationDateTime%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_by)%%>', '<%%editorUsername%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_by)%%>', urlencode('<%%editorUsername%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_at)%%>', '<%%editingDateTime%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_at)%%>', urlencode('<%%editingDateTime%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(meetings_agenda_str)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(meetings_agenda_str)%%>', urlencode(''), $templateCode);
	}

	// process translations
	$templateCode = parseTemplate($templateCode);

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if(Request::val('dvprint_x') == '') {
		$templateCode .= "\n\n<script>\$j(function() {\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption) {
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$hasSelectedId) {
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields
	$filterField = Request::val('FilterField');
	$filterOperator = Request::val('FilterOperator');
	$filterValue = Request::val('FilterValue');
	if(isset($filterField[1]) && $filterField[1] == '2' && $filterOperator[1] == '<=>')
		$templateCode.="\n<input type=hidden name=meeting_lookup value=\"" . html_attr($filterValue[1]) . "\">\n";

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('meetings_agenda_table');
	if($hasSelectedId) {
		$jdata = get_joined_record('meetings_agenda_table', $selectedId);
		if($jdata === false) $jdata = get_defaults('meetings_agenda_table');
		$rdata = $row;
	}
	$templateCode .= loadView('meetings_agenda_table-ajax-cache', ['rdata' => $rdata, 'jdata' => $jdata]);

	// hook: meetings_agenda_table_dv
	if(function_exists('meetings_agenda_table_dv')) {
		$args = [];
		meetings_agenda_table_dv(($hasSelectedId ? $selectedId : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}