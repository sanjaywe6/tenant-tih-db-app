<?php

// Data functions (insert, update, delete, form) for table event_outcomes_expected_table

// This script and data application was generated by AppGini, https://bigprof.com/appgini
// Download AppGini for free from https://bigprof.com/appgini/download/

function event_outcomes_expected_table_insert(&$error_message = '') {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('event_outcomes_expected_table');
	if(!$arrPerm['insert']) {
		$error_message = $Translation['no insert permission'];
		return false;
	}

	// automatic event_lookup if passed as filterer
	if(Request::val('filterer_event_lookup')) {
		$_REQUEST['event_lookup'] = Request::val('filterer_event_lookup');
	}

	$data = [
		'target_audience' => Request::val('target_audience', ''),
		'expected_outcomes' => br2nl(Request::val('expected_outcomes', '')),
		'created_by' => parseCode('<%%creatorUsername%%>', true),
		'created_at' => parseCode('<%%creationDateTime%%>', true),
	];


	// automatic event_lookup if passed as filterer
	if(Request::val('filterer_event_lookup')) {
		$data['event_lookup'] = Request::val('filterer_event_lookup');
	}
	// record owner is current user
	$recordOwner = getLoggedMemberID();

	$recID = tableInsert('event_outcomes_expected_table', $data, $recordOwner, $error_message);

	// if this record is a copy of another record, copy children if applicable
	if(strlen(Request::val('SelectedID')) && $recID !== false)
		event_outcomes_expected_table_copy_children($recID, Request::val('SelectedID'));

	return $recID;
}

function event_outcomes_expected_table_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = []; // array of curl handlers for launching insert requests
	$eo = ['silentErrors' => true];
	$safe_sid = makeSafe($source_id);
	$currentUsername = getLoggedMemberID();
	$errorMessage = '';

	// launch requests, asynchronously
	curl_batch($requests);
}

function event_outcomes_expected_table_delete($selected_id, $AllowDeleteOfParents = false, $skipChecks = false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id = makeSafe($selected_id);

	// mm: can member delete record?
	if(!check_record_permission('event_outcomes_expected_table', $selected_id, 'delete')) {
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: event_outcomes_expected_table_before_delete
	if(function_exists('event_outcomes_expected_table_before_delete')) {
		$args = [];
		if(!event_outcomes_expected_table_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'] . (
				!empty($args['error_message']) ?
					'<div class="text-bold">' . strip_tags($args['error_message']) . '</div>'
					: '' 
			);
	}

	// child table: event_decision_table
	$res = sql("SELECT `id` FROM `event_outcomes_expected_table` WHERE `id`='{$selected_id}'", $eo);
	$id = db_fetch_row($res);
	$rires = sql("SELECT COUNT(1) FROM `event_decision_table` WHERE `outcomes_expected_lookup`='" . makeSafe($id[0]) . "'", $eo);
	$rirow = db_fetch_row($rires);
	$childrenATag = '<a class="alert-link" href="event_decision_table_view.php?filterer_outcomes_expected_lookup=' . urlencode($id[0]) . '">%s</a>';
	if($rirow[0] && !$AllowDeleteOfParents && !$skipChecks) {
		$RetMsg = $Translation["couldn't delete"];
		$RetMsg = str_replace('<RelatedRecords>', sprintf($childrenATag, $rirow[0]), $RetMsg);
		$RetMsg = str_replace(['[<TableName>]', '<TableName>'], sprintf($childrenATag, 'event_decision_table'), $RetMsg);
		return $RetMsg;
	} elseif($rirow[0] && $AllowDeleteOfParents && !$skipChecks) {
		$RetMsg = $Translation['confirm delete'];
		$RetMsg = str_replace('<RelatedRecords>', sprintf($childrenATag, $rirow[0]), $RetMsg);
		$RetMsg = str_replace(['[<TableName>]', '<TableName>'], sprintf($childrenATag, 'event_decision_table'), $RetMsg);
		$RetMsg = str_replace('<Delete>', '<input type="button" class="btn btn-danger" value="' . html_attr($Translation['yes']) . '" onClick="window.location = `event_outcomes_expected_table_view.php?SelectedID=' . urlencode($selected_id) . '&delete_x=1&confirmed=1&csrf_token=' . urlencode(csrf_token(false, true)) . (Request::val('Embedded') ? '&Embedded=1' : '') . '`;">', $RetMsg);
		$RetMsg = str_replace('<Cancel>', '<input type="button" class="btn btn-success" value="' . html_attr($Translation[ 'no']) . '" onClick="window.location = `event_outcomes_expected_table_view.php?SelectedID=' . urlencode($selected_id) . (Request::val('Embedded') ? '&Embedded=1' : '') . '`;">', $RetMsg);
		return $RetMsg;
	}

	sql("DELETE FROM `event_outcomes_expected_table` WHERE `id`='{$selected_id}'", $eo);

	// hook: event_outcomes_expected_table_after_delete
	if(function_exists('event_outcomes_expected_table_after_delete')) {
		$args = [];
		event_outcomes_expected_table_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("DELETE FROM `membership_userrecords` WHERE `tableName`='event_outcomes_expected_table' AND `pkValue`='{$selected_id}'", $eo);
}

function event_outcomes_expected_table_update(&$selected_id, &$error_message = '') {
	global $Translation;

	// mm: can member edit record?
	if(!check_record_permission('event_outcomes_expected_table', $selected_id, 'edit')) return false;

	$data = [
		'target_audience' => Request::val('target_audience', ''),
		'expected_outcomes' => br2nl(Request::val('expected_outcomes', '')),
		'last_updated_by' => parseCode('<%%editorUsername%%>', false),
		'last_updated_at' => parseCode('<%%editingDateTime%%>', false),
	];

	// get existing values
	$old_data = getRecord('event_outcomes_expected_table', $selected_id);
	if(is_array($old_data)) {
		$old_data = array_map('makeSafe', $old_data);
		$old_data['selectedID'] = makeSafe($selected_id);
	}

	$data['selectedID'] = makeSafe($selected_id);

	// hook: event_outcomes_expected_table_before_update
	if(function_exists('event_outcomes_expected_table_before_update')) {
		$args = ['old_data' => $old_data];
		if(!event_outcomes_expected_table_before_update($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$set = $data; unset($set['selectedID']);
	foreach ($set as $field => $value) {
		$set[$field] = ($value !== '' && $value !== NULL) ? $value : NULL;
	}

	if(!update(
		'event_outcomes_expected_table', 
		backtick_keys_once($set), 
		['`id`' => $selected_id], 
		$error_message
	)) {
		echo $error_message;
		echo '<a href="event_outcomes_expected_table_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
		exit;
	}


	update_calc_fields('event_outcomes_expected_table', $data['selectedID'], calculated_fields()['event_outcomes_expected_table']);

	// hook: event_outcomes_expected_table_after_update
	if(function_exists('event_outcomes_expected_table_after_update')) {
		if($row = getRecord('event_outcomes_expected_table', $data['selectedID'])) $data = array_map('makeSafe', $row);

		$data['selectedID'] = $data['id'];
		$args = ['old_data' => $old_data];
		if(!event_outcomes_expected_table_after_update($data, getMemberInfo(), $args)) return;
	}

	// mm: update record update timestamp
	set_record_owner('event_outcomes_expected_table', $selected_id);
}

function event_outcomes_expected_table_form($selectedId = '', $allowUpdate = true, $allowInsert = true, $allowDelete = true, $separateDV = true, $templateDV = '', $templateDVP = '') {
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
	$arrPerm = getTablePermissions('event_outcomes_expected_table');
	$allowInsert = ($arrPerm['insert'] ? true : false);
	$allowUpdate = $hasSelectedId && check_record_permission('event_outcomes_expected_table', $selectedId, 'edit');
	$allowDelete = $hasSelectedId && check_record_permission('event_outcomes_expected_table', $selectedId, 'delete');

	if(!$allowInsert && !$hasSelectedId)
		// no insert permission and no record selected
		// so show access denied error -- except if TVDV: just hide DV
		return $separateDV ? $Translation['tableAccessDenied'] : '';

	if($hasSelectedId && !check_record_permission('event_outcomes_expected_table', $selectedId, 'view'))
		return $Translation['tableAccessDenied'];

	// print preview?
	$dvprint = $hasSelectedId && Request::val('dvprint_x') != '';

	$showSaveNew = !$dvprint && ($allowInsert && !$hasSelectedId);
	$showSaveChanges = !$dvprint && $allowUpdate && $hasSelectedId;
	$showDelete = !$dvprint && $allowDelete && $hasSelectedId;
	$showSaveAsCopy = !$dvprint && ($allowInsert && $hasSelectedId && !$noSaveAsCopy);
	$fieldsAreEditable = !$dvprint && (($allowInsert && !$hasSelectedId) || ($allowUpdate && $hasSelectedId) || $showSaveAsCopy);

	$filterer_event_lookup = Request::val('filterer_event_lookup');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: event_lookup
	$combo_event_lookup = new DataCombo;

	if($hasSelectedId) {
		if(!($row = getRecord('event_outcomes_expected_table', $selectedId))) {
			return error_message($Translation['No records found'], 'event_outcomes_expected_table_view.php', false);
		}
		$combo_event_lookup->SelectedData = $row['event_lookup'];
		$urow = $row; /* unsanitized data */
		$row = array_map('safe_html', $row);
	} else {
		$filterField = Request::val('FilterField');
		$filterOperator = Request::val('FilterOperator');
		$filterValue = Request::val('FilterValue');
		$combo_event_lookup->SelectedData = $filterer_event_lookup;
	}
	$combo_event_lookup->HTML = '<span id="event_lookup-container' . $rnd1 . '"></span><input type="hidden" name="event_lookup" id="event_lookup' . $rnd1 . '" value="' . html_attr($combo_event_lookup->SelectedData) . '">';
	$combo_event_lookup->MatchText = '<span id="event_lookup-container-readonly' . $rnd1 . '"></span><input type="hidden" name="event_lookup" id="event_lookup' . $rnd1 . '" value="' . html_attr($combo_event_lookup->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_event_lookup__RAND__ = { text: "", value: "<?php echo addslashes($hasSelectedId ? $urow['event_lookup'] : htmlspecialchars($filterer_event_lookup, ENT_QUOTES)); ?>"};

		$j(function() {
			setTimeout(function() {
				if(typeof(event_lookup_reload__RAND__) == 'function') event_lookup_reload__RAND__();
			}, 50); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function event_lookup_reload__RAND__() {
		<?php if($fieldsAreEditable) { ?>

			$j("#event_lookup-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_event_lookup__RAND__.value, t: 'event_outcomes_expected_table', f: 'event_lookup' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="event_lookup"]').val(resp.results[0].id);
							$j('[id=event_lookup-container-readonly__RAND__]').html('<span class="match-text" id="event_lookup-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=event_table_view_parent]').hide(); } else { $j('.btn[id=event_table_view_parent]').show(); }


							if(typeof(event_lookup_update_autofills__RAND__) == 'function') event_lookup_update_autofills__RAND__();
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
					data: function(term, page) { return { s: term, p: page, t: 'event_outcomes_expected_table', f: 'event_lookup' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_event_lookup__RAND__.value = e.added.id;
				AppGini.current_event_lookup__RAND__.text = e.added.text;
				$j('[name="event_lookup"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=event_table_view_parent]').hide(); } else { $j('.btn[id=event_table_view_parent]').show(); }


				if(typeof(event_lookup_update_autofills__RAND__) == 'function') event_lookup_update_autofills__RAND__();
			});

			if(!$j("#event_lookup-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_event_lookup__RAND__.value, t: 'event_outcomes_expected_table', f: 'event_lookup' },
					success: function(resp) {
						$j('[name="event_lookup"]').val(resp.results[0].id);
						$j('[id=event_lookup-container-readonly__RAND__]').html('<span class="match-text" id="event_lookup-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=event_table_view_parent]').hide(); } else { $j('.btn[id=event_table_view_parent]').show(); }

						if(typeof(event_lookup_update_autofills__RAND__) == 'function') event_lookup_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_event_lookup__RAND__.value, t: 'event_outcomes_expected_table', f: 'event_lookup' },
				success: function(resp) {
					$j('[id=event_lookup-container__RAND__], [id=event_lookup-container-readonly__RAND__]').html('<span class="match-text" id="event_lookup-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=event_table_view_parent]').hide(); } else { $j('.btn[id=event_table_view_parent]').show(); }

					if(typeof(event_lookup_update_autofills__RAND__) == 'function') event_lookup_update_autofills__RAND__();
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
		$template_file = is_file("./{$templateDVP}") ? "./{$templateDVP}" : './templates/event_outcomes_expected_table_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	} else {
		$template_file = is_file("./{$templateDV}") ? "./{$templateDV}" : './templates/event_outcomes_expected_table_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Outcomes expected table details', $templateCode);
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
		$jsReadOnly .= "\t\$j('#target_audience').replaceWith('<div class=\"form-control-static\" id=\"target_audience\">' + (\$j('#target_audience').val() || '') + '</div>');\n";
		$jsReadOnly .= "\t\$j('#expected_outcomes').replaceWith('<div class=\"form-control-static\" id=\"expected_outcomes\">' + (\$j('#expected_outcomes').val() || '') + '</div>');\n";
		$jsReadOnly .= "\t\$j('.select2-container').hide();\n";

		$noUploads = true;
	} else {
		// temporarily disable form change handler till time and datetime pickers are enabled
		$jsEditable = "\t\$j('form').eq(0).data('already_changed', true);";
		$jsEditable .= "\t\$j('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(event_lookup)%%>', $combo_event_lookup->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(event_lookup)%%>', $combo_event_lookup->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(event_lookup)%%>', urlencode($combo_event_lookup->MatchText), $templateCode);

	/* lookup fields array: 'lookup field name' => ['parent table name', 'lookup field caption'] */
	$lookup_fields = ['event_lookup' => ['event_table', 'Event Details'], ];
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
	$templateCode = str_replace('<%%UPLOADFILE(event_lookup)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(target_audience)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(expected_outcomes)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(created_by)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(created_at)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(last_updated_by)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(last_updated_at)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(tenant_id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(outcomes_expected_str)%%>', '', $templateCode);

	// process values
	if($hasSelectedId) {
		$templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		$templateCode = str_replace('<%%VALUE(event_lookup)%%>', safe_html($urow['event_lookup']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(event_lookup)%%>', urlencode($urow['event_lookup']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(target_audience)%%>', safe_html($urow['target_audience']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(target_audience)%%>', html_attr($row['target_audience']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(target_audience)%%>', urlencode($urow['target_audience']), $templateCode);
		$templateCode = str_replace('<%%VALUE(expected_outcomes)%%>', safe_html($urow['expected_outcomes'], $fieldsAreEditable), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(expected_outcomes)%%>', urlencode($urow['expected_outcomes']), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_by)%%>', safe_html($urow['created_by']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_by)%%>', urlencode($urow['created_by']), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_at)%%>', safe_html($urow['created_at']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_at)%%>', urlencode($urow['created_at']), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_by)%%>', safe_html($urow['last_updated_by']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_by)%%>', urlencode($urow['last_updated_by']), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_at)%%>', safe_html($urow['last_updated_at']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_at)%%>', urlencode($urow['last_updated_at']), $templateCode);
		$templateCode = str_replace('<%%VALUE(tenant_id)%%>', safe_html($urow['tenant_id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(tenant_id)%%>', urlencode($urow['tenant_id']), $templateCode);
		$templateCode = str_replace('<%%VALUE(outcomes_expected_str)%%>', safe_html($urow['outcomes_expected_str']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(outcomes_expected_str)%%>', urlencode($urow['outcomes_expected_str']), $templateCode);
	} else {
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(event_lookup)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(event_lookup)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(target_audience)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(target_audience)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(expected_outcomes)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(expected_outcomes)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_by)%%>', '<%%creatorUsername%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_by)%%>', urlencode('<%%creatorUsername%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(created_at)%%>', '<%%creationDateTime%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(created_at)%%>', urlencode('<%%creationDateTime%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_by)%%>', '<%%editorUsername%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_by)%%>', urlencode('<%%editorUsername%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(last_updated_at)%%>', '<%%editingDateTime%%>', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(last_updated_at)%%>', urlencode('<%%editingDateTime%%>'), $templateCode);
		$templateCode = str_replace('<%%VALUE(tenant_id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(tenant_id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(outcomes_expected_str)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(outcomes_expected_str)%%>', urlencode(''), $templateCode);
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
		$templateCode.="\n<input type=hidden name=event_lookup value=\"" . html_attr($filterValue[1]) . "\">\n";

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('event_outcomes_expected_table');
	if($hasSelectedId) {
		$jdata = get_joined_record('event_outcomes_expected_table', $selectedId);
		if($jdata === false) $jdata = get_defaults('event_outcomes_expected_table');
		$rdata = $row;
	}
	$templateCode .= loadView('event_outcomes_expected_table-ajax-cache', ['rdata' => $rdata, 'jdata' => $jdata]);

	// hook: event_outcomes_expected_table_dv
	if(function_exists('event_outcomes_expected_table_dv')) {
		$args = [];
		event_outcomes_expected_table_dv(($hasSelectedId ? $selectedId : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}