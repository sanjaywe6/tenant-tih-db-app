<?php

	#########################################################
	/*
	~~~~~~ LIST OF FUNCTIONS ~~~~~~
		get_table_groups() -- returns an associative array (table_group => tables_array)
		getTablePermissions($tn) -- returns an array of permissions allowed for logged member to given table (allowAccess, allowInsert, allowView, allowEdit, allowDelete) -- allowAccess is set to true if any access level is allowed
		get_sql_fields($tn) -- returns the SELECT part of the table view query
		get_sql_from($tn[, true, [, false]]) -- returns the FROM part of the table view query, with full joins (unless third paramaeter is set to true), optionally skipping permissions if true passed as 2nd param.
		get_joined_record($table, $id[, true]) -- returns assoc array of record values for given PK value of given table, with full joins, optionally skipping permissions if true passed as 3rd param.
		get_defaults($table) -- returns assoc array of table fields as array keys and default values (or empty), excluding automatic values as array values
		htmlUserBar() -- returns html code for displaying user login status to be used on top of pages.
		showNotifications($msg, $class) -- returns html code for displaying a notification. If no parameters provided, processes the GET request for possible notifications.
		parseMySQLDate(a, b) -- returns a if valid mysql date, or b if valid mysql date, or today if b is true, or empty if b is false.
		parseCode(code) -- calculates and returns special values to be inserted in automatic fields.
		addFilter(i, filterAnd, filterField, filterOperator, filterValue) -- enforce a filter over data
		clearFilters() -- clear all filters
		loadView($view, $data) -- passes $data to templates/{$view}.php and returns the output
		loadTable($table, $data) -- loads table template, passing $data to it
		br2nl($text) -- replaces all variations of HTML <br> tags with a new line character
		entitiesToUTF8($text) -- convert unicode entities (e.g. &#1234;) to actual UTF8 characters, requires multibyte string PHP extension
		func_get_args_byref() -- returns an array of arguments passed to a function, by reference
		permissions_sql($table, $level) -- returns an array containing the FROM and WHERE additions for applying permissions to an SQL query
		error_message($msg[, $back_url]) -- returns html code for a styled error message .. pass explicit false in second param to suppress back button
		toMySQLDate($formattedDate, $sep = datalist_date_separator, $ord = datalist_date_format)
		reIndex(&$arr) -- returns a copy of the given array, with keys replaced by 1-based numeric indices, and values replaced by original keys
		get_embed($provider, $url[, $width, $height, $retrieve]) -- returns embed code for a given url (supported providers: [auto-detect], or explicitly pass one of: youtube, vimeo, googlemap, dailymotion, videofileurl)
		check_record_permission($table, $id, $perm = 'view') -- returns true if current user has the specified permission $perm ('view', 'edit' or 'delete') for the given recors, false otherwise
		NavMenus($options) -- returns the HTML code for the top navigation menus. $options is not implemented currently.
		StyleSheet() -- returns the HTML code for included style sheet files to be placed in the <head> section.
		PrepareUploadedFile($FieldName, $MaxSize, $FileTypes={image file types}, $NoRename=false, $dir="") -- validates and moves uploaded file for given $FieldName into the given $dir (or the default one if empty)
		get_home_links($homeLinks, $default_classes, $tgroup) -- process $homeLinks array and return custom links for homepage. Applies $default_classes to links if links have classes defined, and filters links by $tgroup (using '*' matches all table_group values)
		quick_search_html($search_term, $label, $separate_dv = true) -- returns HTML code for the quick search box.
	~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	*/

	#########################################################

	function get_table_groups($skip_authentication = false) {
		$tables = getTableList($skip_authentication);
		$all_groups = ['Events / Meetings / Goals Apps', 'HRD Apps', 'SDP Apps', 'Program Apps', 'Technology Development Apps', 'Startup Data Management Apps', 'Employee Data Management Apps', 'Asset Management Apps', 'Accounts &amp; Finance Apps', 'Transport Apps', 'Suggestions &amp; Other Apps'];

		$groups = [];
		foreach($all_groups as $grp) {
			foreach($tables as $tn => $td) {
				if($td[3] && $td[3] == $grp) $groups[$grp][] = $tn;
				if(!$td[3]) $groups[0][] = $tn;
			}
		}

		return $groups;
	}

	#########################################################

	function getTablePermissions($tn) {
		static $table_permissions = [];
		if(isset($table_permissions[$tn])) return $table_permissions[$tn];

		$groupID = getLoggedGroupID();
		$memberID = makeSafe(getLoggedMemberID());
		$res_group = sql("SELECT `tableName`, `allowInsert`, `allowView`, `allowEdit`, `allowDelete` FROM `membership_grouppermissions` WHERE `groupID`='{$groupID}'", $eo);
		$res_user  = sql("SELECT `tableName`, `allowInsert`, `allowView`, `allowEdit`, `allowDelete` FROM `membership_userpermissions`  WHERE LCASE(`memberID`)='{$memberID}'", $eo);

		while($row = db_fetch_assoc($res_group)) {
			$table_permissions[$row['tableName']] = [
				1 => intval($row['allowInsert']),
				2 => intval($row['allowView']),
				3 => intval($row['allowEdit']),
				4 => intval($row['allowDelete']),
				'insert' => intval($row['allowInsert']),
				'view' => intval($row['allowView']),
				'edit' => intval($row['allowEdit']),
				'delete' => intval($row['allowDelete'])
			];
		}

		// user-specific permissions, if specified, overwrite his group permissions
		while($row = db_fetch_assoc($res_user)) {
			$table_permissions[$row['tableName']] = [
				1 => intval($row['allowInsert']),
				2 => intval($row['allowView']),
				3 => intval($row['allowEdit']),
				4 => intval($row['allowDelete']),
				'insert' => intval($row['allowInsert']),
				'view' => intval($row['allowView']),
				'edit' => intval($row['allowEdit']),
				'delete' => intval($row['allowDelete'])
			];
		}

		// if user has any type of access, set 'access' flag
		foreach($table_permissions as $t => $p) {
			$table_permissions[$t]['access'] = $table_permissions[$t][0] = false;

			if($p['insert'] || $p['view'] || $p['edit'] || $p['delete']) {
				$table_permissions[$t]['access'] = $table_permissions[$t][0] = true;
			}
		}

		return $table_permissions[$tn] ?? [];
	}

	#########################################################

	function get_sql_fields($table_name) {
		$sql_fields = [
			'tenants' => "`tenants`.`id` as 'id', `tenants`.`tenant_name` as 'tenant_name', `tenants`.`created_by` as 'created_by', `tenants`.`created_at` as 'created_at', `tenants`.`last_updated_by` as 'last_updated_by', `tenants`.`last_updated_at` as 'last_updated_at'",
			'user_table' => "`user_table`.`id` as 'id', `user_table`.`memberID` as 'memberID', `user_table`.`name` as 'name', `user_table`.`tenant_id` as 'tenant_id', `user_table`.`created_by` as 'created_by', `user_table`.`created_at` as 'created_at', `user_table`.`last_updated_by` as 'last_updated_by', `user_table`.`last_updated_at` as 'last_updated_at'",
			'suggestion' => "`suggestion`.`id` as 'id', `suggestion`.`suggestion` as 'suggestion', `suggestion`.`attachment` as 'attachment', `suggestion`.`created_by` as 'created_by', `suggestion`.`created_at` as 'created_at', `suggestion`.`last_updated_by` as 'last_updated_by', `suggestion`.`last_updated_at` as 'last_updated_at', `suggestion`.`tenant_id` as 'tenant_id'",
			'event_table' => "`event_table`.`id` as 'id', `event_table`.`event_name` as 'event_name', `event_table`.`participants` as 'participants', `event_table`.`venue` as 'venue', if(`event_table`.`event_from_date`,date_format(`event_table`.`event_from_date`,'%d/%m/%Y'),'') as 'event_from_date', if(`event_table`.`event_to_date`,date_format(`event_table`.`event_to_date`,'%d/%m/%Y'),'') as 'event_to_date', `event_table`.`created_by` as 'created_by', `event_table`.`created_at` as 'created_at', `event_table`.`last_updated_by` as 'last_updated_by', `event_table`.`last_updated_at` as 'last_updated_at', `event_table`.`tenant_id` as 'tenant_id', `event_table`.`event_str` as 'event_str'",
			'event_outcomes_expected_table' => "`event_outcomes_expected_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') as 'event_lookup', `event_outcomes_expected_table`.`target_audience` as 'target_audience', `event_outcomes_expected_table`.`expected_outcomes` as 'expected_outcomes', `event_outcomes_expected_table`.`created_by` as 'created_by', `event_outcomes_expected_table`.`created_at` as 'created_at', `event_outcomes_expected_table`.`last_updated_by` as 'last_updated_by', `event_outcomes_expected_table`.`last_updated_at` as 'last_updated_at', `event_outcomes_expected_table`.`tenant_id` as 'tenant_id', `event_outcomes_expected_table`.`outcomes_expected_str` as 'outcomes_expected_str'",
			'event_participants_table' => "`event_participants_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') as 'event_lookup', `event_participants_table`.`name` as 'name', `event_participants_table`.`designation` as 'designation', `event_participants_table`.`participant_type` as 'participant_type', `event_participants_table`.`accepted_status` as 'accepted_status', if(`event_participants_table`.`status_date`,date_format(`event_participants_table`.`status_date`,'%d/%m/%Y'),'') as 'status_date', `event_participants_table`.`created_by` as 'created_by', `event_participants_table`.`created_at` as 'created_at', `event_participants_table`.`last_updated_by` as 'last_updated_by', `event_participants_table`.`last_updated_at` as 'last_updated_at', `event_participants_table`.`event_participants_str` as 'event_participants_str', `event_participants_table`.`tenant_id` as 'tenant_id'",
			'event_decision_table' => "`event_decision_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_outcomes_expected_table1`.`outcomes_expected_str`), CONCAT_WS('',   `event_outcomes_expected_table1`.`outcomes_expected_str`), '') as 'outcomes_expected_lookup', `event_decision_table`.`decision_description` as 'decision_description', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'decision_actor', if(`event_decision_table`.`action_taken_with_date`,date_format(`event_decision_table`.`action_taken_with_date`,'%d/%m/%Y'),'') as 'action_taken_with_date', `event_decision_table`.`decision_status` as 'decision_status', if(`event_decision_table`.`decision_status_update_date`,date_format(`event_decision_table`.`decision_status_update_date`,'%d/%m/%Y'),'') as 'decision_status_update_date', `event_decision_table`.`decision_status_remarks_by_superior` as 'decision_status_remarks_by_superior', `event_decision_table`.`created_by` as 'created_by', `event_decision_table`.`created_at` as 'created_at', `event_decision_table`.`last_updated_by` as 'last_updated_by', `event_decision_table`.`last_updated_at` as 'last_updated_at', `event_decision_table`.`tenant_id` as 'tenant_id', `event_decision_table`.`event_decision_str` as 'event_decision_str'",
			'meetings_table' => "`meetings_table`.`id` as 'id', IF(    CHAR_LENGTH(`visiting_card_table1`.`id`), CONCAT_WS('',   `visiting_card_table1`.`id`), '') as 'visiting_card_lookup', IF(    CHAR_LENGTH(`event_table1`.`id`), CONCAT_WS('',   `event_table1`.`id`), '') as 'event_lookup', `meetings_table`.`meeting_title` as 'meeting_title', `meetings_table`.`participants` as 'participants', `meetings_table`.`venue` as 'venue', if(`meetings_table`.`meeting_from_date`,date_format(`meetings_table`.`meeting_from_date`,'%d/%m/%Y'),'') as 'meeting_from_date', if(`meetings_table`.`meeting_to_date`,date_format(`meetings_table`.`meeting_to_date`,'%d/%m/%Y'),'') as 'meeting_to_date', `meetings_table`.`created_by` as 'created_by', `meetings_table`.`created_at` as 'created_at', `meetings_table`.`last_updated_by` as 'last_updated_by', `meetings_table`.`last_updated_at` as 'last_updated_at', `meetings_table`.`meetings_str` as 'meetings_str', `meetings_table`.`tenant_id` as 'tenant_id'",
			'meetings_agenda_table' => "`meetings_agenda_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_table1`.`meetings_str`), CONCAT_WS('',   `meetings_table1`.`meetings_str`), '') as 'meeting_lookup', `meetings_agenda_table`.`agenda_description` as 'agenda_description', `meetings_agenda_table`.`created_by` as 'created_by', `meetings_agenda_table`.`created_at` as 'created_at', `meetings_agenda_table`.`last_updated_by` as 'last_updated_by', `meetings_agenda_table`.`last_updated_at` as 'last_updated_at', `meetings_agenda_table`.`meetings_agenda_str` as 'meetings_agenda_str', `meetings_agenda_table`.`tenant_id` as 'tenant_id'",
			'meetings_participants_table' => "`meetings_participants_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_table1`.`meetings_str`), CONCAT_WS('',   `meetings_table1`.`meetings_str`), '') as 'meeting_lookup', `meetings_participants_table`.`name` as 'name', `meetings_participants_table`.`designation` as 'designation', `meetings_participants_table`.`participant_type` as 'participant_type', `meetings_participants_table`.`accepted_status` as 'accepted_status', if(`meetings_participants_table`.`status_date`,date_format(`meetings_participants_table`.`status_date`,'%d/%m/%Y'),'') as 'status_date', `meetings_participants_table`.`created_by` as 'created_by', `meetings_participants_table`.`created_at` as 'created_at', `meetings_participants_table`.`last_updated_by` as 'last_updated_by', `meetings_participants_table`.`last_updated_at` as 'last_updated_at', `meetings_participants_table`.`meetings_participants_str` as 'meetings_participants_str', `meetings_participants_table`.`tenant_id` as 'tenant_id'",
			'meetings_decision_table' => "`meetings_decision_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_agenda_table1`.`meetings_agenda_str`), CONCAT_WS('',   `meetings_agenda_table1`.`meetings_agenda_str`), '') as 'agenda_lookup', `meetings_decision_table`.`decision_description` as 'decision_description', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'decision_actor', if(`meetings_decision_table`.`action_taken_with_date`,date_format(`meetings_decision_table`.`action_taken_with_date`,'%d/%m/%Y'),'') as 'action_taken_with_date', `meetings_decision_table`.`decision_status` as 'decision_status', if(`meetings_decision_table`.`decision_status_update_date`,date_format(`meetings_decision_table`.`decision_status_update_date`,'%d/%m/%Y'),'') as 'decision_status_update_date', `meetings_decision_table`.`decision_status_remarks_by_superior` as 'decision_status_remarks_by_superior', `meetings_decision_table`.`created_by` as 'created_by', `meetings_decision_table`.`created_at` as 'created_at', `meetings_decision_table`.`last_updated_by` as 'last_updated_by', `meetings_decision_table`.`last_updated_at` as 'last_updated_at', `meetings_decision_table`.`meetings_decision_str` as 'meetings_decision_str', `meetings_decision_table`.`tenant_id` as 'tenant_id'",
			'visiting_card_table' => "`visiting_card_table`.`id` as 'id', `visiting_card_table`.`name` as 'name', `visiting_card_table`.`recommended_by` as 'recommended_by', `visiting_card_table`.`designation` as 'designation', `visiting_card_table`.`company_name` as 'company_name', `visiting_card_table`.`mobile_no` as 'mobile_no', `visiting_card_table`.`email` as 'email', `visiting_card_table`.`company_website_addr` as 'company_website_addr', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'given_by', `visiting_card_table`.`suggested_way_forward` as 'suggested_way_forward', `visiting_card_table`.`front_img` as 'front_img', `visiting_card_table`.`back_img` as 'back_img', `visiting_card_table`.`created_by` as 'created_by', `visiting_card_table`.`created_at` as 'created_at', `visiting_card_table`.`last_updated_by` as 'last_updated_by', `visiting_card_table`.`last_updated_at` as 'last_updated_at', `visiting_card_table`.`visiting_card_str` as 'visiting_card_str', `visiting_card_table`.`tenant_id` as 'tenant_id'",
		];

		if(isset($sql_fields[$table_name])) return $sql_fields[$table_name];

		return false;
	}

	#########################################################

	function get_sql_from($table_name, $skip_permissions = false, $skip_joins = false, $lower_permissions = false) {
		$sql_from = [
			'tenants' => "`tenants` ",
			'user_table' => "`user_table` ",
			'suggestion' => "`suggestion` ",
			'event_table' => "`event_table` ",
			'event_outcomes_expected_table' => "`event_outcomes_expected_table` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`event_outcomes_expected_table`.`event_lookup` ",
			'event_participants_table' => "`event_participants_table` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`event_participants_table`.`event_lookup` ",
			'event_decision_table' => "`event_decision_table` LEFT JOIN `event_outcomes_expected_table` as event_outcomes_expected_table1 ON `event_outcomes_expected_table1`.`id`=`event_decision_table`.`outcomes_expected_lookup` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`event_decision_table`.`decision_actor` ",
			'meetings_table' => "`meetings_table` LEFT JOIN `visiting_card_table` as visiting_card_table1 ON `visiting_card_table1`.`id`=`meetings_table`.`visiting_card_lookup` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`meetings_table`.`event_lookup` ",
			'meetings_agenda_table' => "`meetings_agenda_table` LEFT JOIN `meetings_table` as meetings_table1 ON `meetings_table1`.`id`=`meetings_agenda_table`.`meeting_lookup` ",
			'meetings_participants_table' => "`meetings_participants_table` LEFT JOIN `meetings_table` as meetings_table1 ON `meetings_table1`.`id`=`meetings_participants_table`.`meeting_lookup` ",
			'meetings_decision_table' => "`meetings_decision_table` LEFT JOIN `meetings_agenda_table` as meetings_agenda_table1 ON `meetings_agenda_table1`.`id`=`meetings_decision_table`.`agenda_lookup` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`meetings_decision_table`.`decision_actor` ",
			'visiting_card_table' => "`visiting_card_table` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`visiting_card_table`.`given_by` ",
		];

		$pkey = [
			'tenants' => 'id',
			'user_table' => 'id',
			'suggestion' => 'id',
			'event_table' => 'id',
			'event_outcomes_expected_table' => 'id',
			'event_participants_table' => 'id',
			'event_decision_table' => 'id',
			'meetings_table' => 'id',
			'meetings_agenda_table' => 'id',
			'meetings_participants_table' => 'id',
			'meetings_decision_table' => 'id',
			'visiting_card_table' => 'id',
		];

		if(!isset($sql_from[$table_name])) return false;

		$from = ($skip_joins ? "`{$table_name}`" : $sql_from[$table_name]);

		if($skip_permissions) return $from . ' WHERE 1=1';

		// mm: build the query based on current member's permissions
		// allowing lower permissions if $lower_permissions set to 'user' or 'group'
		$perm = getTablePermissions($table_name);
		if($perm['view'] == 1 || ($perm['view'] > 1 && $lower_permissions == 'user')) { // view owner only
			$from .= ", `membership_userrecords` WHERE `{$table_name}`.`{$pkey[$table_name]}`=`membership_userrecords`.`pkValue` AND `membership_userrecords`.`tableName`='{$table_name}' AND LCASE(`membership_userrecords`.`memberID`)='" . getLoggedMemberID() . "'";
		} elseif($perm['view'] == 2 || ($perm['view'] > 2 && $lower_permissions == 'group')) { // view group only
			$from .= ", `membership_userrecords` WHERE `{$table_name}`.`{$pkey[$table_name]}`=`membership_userrecords`.`pkValue` AND `membership_userrecords`.`tableName`='{$table_name}' AND `membership_userrecords`.`groupID`='" . getLoggedGroupID() . "'";
		} elseif($perm['view'] == 3) { // view all
			$from .= ' WHERE 1=1';
		} else { // view none
			return false;
		}

		return $from;
	}

	#########################################################

	function get_joined_record($table, $id, $skip_permissions = false) {
		$sql_fields = get_sql_fields($table);
		$sql_from = get_sql_from($table, $skip_permissions);

		if(!$sql_fields || !$sql_from) return false;

		$pk = getPKFieldName($table);
		if(!$pk) return false;

		$safe_id = makeSafe($id, false);
		$sql = "SELECT {$sql_fields} FROM {$sql_from} AND `{$table}`.`{$pk}`='{$safe_id}'";
		$eo = ['silentErrors' => true];
		$res = sql($sql, $eo);
		if($row = db_fetch_assoc($res)) return $row;

		return false;
	}

	#########################################################

	function get_defaults($table) {
		/* array of tables and their fields, with default values (or empty), excluding automatic values */
		$defaults = [
			'tenants' => [
				'id' => '',
				'tenant_name' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
			],
			'user_table' => [
				'id' => '',
				'memberID' => '',
				'name' => '',
				'tenant_id' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
			],
			'suggestion' => [
				'id' => '',
				'suggestion' => '',
				'attachment' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'tenant_id' => '',
			],
			'event_table' => [
				'id' => '',
				'event_name' => '',
				'participants' => '',
				'venue' => '',
				'event_from_date' => '',
				'event_to_date' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'tenant_id' => '',
				'event_str' => '',
			],
			'event_outcomes_expected_table' => [
				'id' => '',
				'event_lookup' => '',
				'target_audience' => '',
				'expected_outcomes' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'tenant_id' => '',
				'outcomes_expected_str' => '',
			],
			'event_participants_table' => [
				'id' => '',
				'event_lookup' => '',
				'name' => '',
				'designation' => '',
				'participant_type' => '',
				'accepted_status' => '',
				'status_date' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'event_participants_str' => '',
				'tenant_id' => '',
			],
			'event_decision_table' => [
				'id' => '',
				'outcomes_expected_lookup' => '',
				'decision_description' => '',
				'decision_actor' => 'None',
				'action_taken_with_date' => '',
				'decision_status' => 'Yet to Start',
				'decision_status_update_date' => '',
				'decision_status_remarks_by_superior' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'tenant_id' => '',
				'event_decision_str' => '',
			],
			'meetings_table' => [
				'id' => '',
				'visiting_card_lookup' => '',
				'event_lookup' => '',
				'meeting_title' => '',
				'participants' => '',
				'venue' => '',
				'meeting_from_date' => '1',
				'meeting_to_date' => '1',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'meetings_str' => '',
				'tenant_id' => '',
			],
			'meetings_agenda_table' => [
				'id' => '',
				'meeting_lookup' => '',
				'agenda_description' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'meetings_agenda_str' => '',
				'tenant_id' => '',
			],
			'meetings_participants_table' => [
				'id' => '',
				'meeting_lookup' => '',
				'name' => '',
				'designation' => '',
				'participant_type' => '',
				'accepted_status' => '',
				'status_date' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'meetings_participants_str' => '',
				'tenant_id' => '',
			],
			'meetings_decision_table' => [
				'id' => '',
				'agenda_lookup' => '',
				'decision_description' => '',
				'decision_actor' => '',
				'action_taken_with_date' => '',
				'decision_status' => 'Yet to Start',
				'decision_status_update_date' => '',
				'decision_status_remarks_by_superior' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'meetings_decision_str' => '',
				'tenant_id' => '',
			],
			'visiting_card_table' => [
				'id' => '',
				'name' => '',
				'recommended_by' => '',
				'designation' => '',
				'company_name' => '',
				'mobile_no' => '',
				'email' => '',
				'company_website_addr' => '',
				'given_by' => '',
				'suggested_way_forward' => '',
				'front_img' => '',
				'back_img' => '',
				'created_by' => '',
				'created_at' => '',
				'last_updated_by' => '',
				'last_updated_at' => '',
				'visiting_card_str' => '',
				'tenant_id' => '',
			],
		];

		return isset($defaults[$table]) ? $defaults[$table] : [];
	}

	#########################################################

	function htmlUserBar() {
		global $Translation;
		if(!defined('PREPEND_PATH')) define('PREPEND_PATH', '');

		$mi = getMemberInfo();
		$adminConfig = config('adminConfig');
		$home_page = (basename($_SERVER['PHP_SELF']) == 'index.php');
		ob_start();

		?>
		<nav class="navbar navbar-default navbar-fixed-top hidden-print" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<!-- application title is obtained from the name besides the yellow database icon in AppGini, use underscores for spaces -->
				<a class="navbar-brand" href="<?php echo PREPEND_PATH; ?>index.php"><i class="glyphicon glyphicon-home"></i> <?php echo APP_TITLE; ?></a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav"><?php echo ($home_page && !HOMEPAGE_NAVMENUS ? '' : NavMenus()); ?></ul>

				<?php if(userCanImport()){ ?>
					<ul class="nav navbar-nav">
						<a href="<?php echo PREPEND_PATH; ?>import-csv.php" class="btn btn-default navbar-btn hidden-xs btn-import-csv" title="<?php echo html_attr($Translation['import csv file']); ?>"><i class="glyphicon glyphicon-th"></i> <?php echo $Translation['import CSV']; ?></a>
						<a href="<?php echo PREPEND_PATH; ?>import-csv.php" class="btn btn-default navbar-btn visible-xs btn-lg btn-import-csv" title="<?php echo html_attr($Translation['import csv file']); ?>"><i class="glyphicon glyphicon-th"></i> <?php echo $Translation['import CSV']; ?></a>
					</ul>
				<?php } ?>

				<?php if(getLoggedAdmin() !== false) { ?>
					<ul class="nav navbar-nav">
						<a href="<?php echo PREPEND_PATH; ?>admin/pageHome.php" class="btn btn-danger navbar-btn hidden-xs" title="<?php echo html_attr($Translation['admin area']); ?>"><i class="glyphicon glyphicon-cog"></i> <?php echo $Translation['admin area']; ?></a>
						<a href="<?php echo PREPEND_PATH; ?>admin/pageHome.php" class="btn btn-danger navbar-btn visible-xs btn-lg" title="<?php echo html_attr($Translation['admin area']); ?>"><i class="glyphicon glyphicon-cog"></i> <?php echo $Translation['admin area']; ?></a>
					</ul>
				<?php } ?>

				<?php if(!Request::val('signIn') && !Request::val('loginFailed')) { ?>
					<?php if(!$mi['username'] || $mi['username'] == $adminConfig['anonymousMember']) { ?>
						<p class="navbar-text navbar-right hidden-xs">&nbsp;</p>
						<a href="<?php echo PREPEND_PATH; ?>index.php?signIn=1" class="btn btn-success navbar-btn navbar-right hidden-xs"><?php echo $Translation['sign in']; ?></a>
						<p class="navbar-text navbar-right hidden-xs">
							<?php echo $Translation['not signed in']; ?>
						</p>
						<a href="<?php echo PREPEND_PATH; ?>index.php?signIn=1" class="btn btn-success btn-block btn-lg navbar-btn visible-xs">
							<?php echo $Translation['not signed in']; ?>
							<i class="glyphicon glyphicon-chevron-right"></i> 
							<?php echo $Translation['sign in']; ?>
						</a>
					<?php } else { ?>
						<ul class="nav navbar-nav navbar-right hidden-xs">
							<!-- logged user profile menu -->
							<li class="dropdown" title="<?php echo html_attr("{$Translation['signed as']} {$mi['username']}"); ?>">
								<a href="#" class="dropdown-toggle profile-menu-icon" data-toggle="dropdown"><i class="glyphicon glyphicon-user icon"></i><span class="profile-menu-text"><?php echo $mi['username']; ?></span><b class="caret"></b></a>
								<ul class="dropdown-menu profile-menu">
									<li class="user-profile-menu-item" title="<?php echo html_attr("{$Translation['Your info']}"); ?>">
										<a href="<?php echo PREPEND_PATH; ?>membership_profile.php"><i class="glyphicon glyphicon-user"></i> <span class="username"><?php echo $mi['username']; ?></span></a>
									</li>
									<li class="keyboard-shortcuts-menu-item" title="<?php echo html_attr("{$Translation['keyboard shortcuts']}"); ?>" class="hidden-xs">
										<a href="#" class="help-shortcuts-launcher">
											<img src="<?php echo PREPEND_PATH; ?>resources/images/keyboard.png">
											<?php echo html_attr($Translation['keyboard shortcuts']); ?>
										</a>
									</li>
									<li class="sign-out-menu-item" title="<?php echo html_attr("{$Translation['sign out']}"); ?>">
										<a href="<?php echo PREPEND_PATH; ?>index.php?signOut=1"><i class="glyphicon glyphicon-log-out"></i> <?php echo $Translation['sign out']; ?></a>
									</li>
								</ul>
							</li>
						</ul>
						<ul class="nav navbar-nav visible-xs">
							<a class="btn navbar-btn btn-default btn-lg visible-xs" href="<?php echo PREPEND_PATH; ?>index.php?signOut=1"><i class="glyphicon glyphicon-log-out"></i> <?php echo $Translation['sign out']; ?></a>
							<p class="navbar-text text-center signed-in-as">
								<?php echo $Translation['signed as']; ?> <strong><a href="<?php echo PREPEND_PATH; ?>membership_profile.php" class="navbar-link username"><?php echo $mi['username']; ?></a></strong>
							</p>
						</ul>
						<script>
							/* periodically check if user is still signed in */
							setInterval(function() {
								$j.ajax({
									url: '<?php echo PREPEND_PATH; ?>ajax_check_login.php',
									success: function(username) {
										if(!username.length) window.location = '<?php echo PREPEND_PATH; ?>index.php?signIn=1';
									}
								});
							}, 60000);
						</script>
					<?php } ?>
				<?php } ?>
			</div>
		</nav>
		<?php

		return ob_get_clean();
	}

	#########################################################

	function showNotifications($msg = '', $class = '', $fadeout = true) {
		global $Translation;
		if($error_message = strip_tags(Request::val('error_message')))
			$error_message = '<div class="text-bold">' . $error_message . '</div>';

		if(!$msg) { // if no msg, use url to detect message to display
			if(Request::val('record-added-ok')) {
				$msg = $Translation['new record saved'];
				$class = 'alert-success';
			} elseif(Request::val('record-added-error')) {
				$msg = $Translation['Couldn\'t save the new record'] . $error_message;
				$class = 'alert-danger';
				$fadeout = false;
			} elseif(Request::val('record-updated-ok')) {
				$msg = $Translation['record updated'];
				$class = 'alert-success';
			} elseif(Request::val('record-updated-error')) {
				$msg = $Translation['Couldn\'t save changes to the record'] . $error_message;
				$class = 'alert-danger';
				$fadeout = false;
			} elseif(Request::val('record-deleted-ok')) {
				$msg = $Translation['The record has been deleted successfully'];
				$class = 'alert-success';
			} elseif(Request::val('record-deleted-error')) {
				$msg = $Translation['Couldn\'t delete this record'] . $error_message;
				$class = 'alert-danger';
				$fadeout = false;
			} else {
				return '';
			}
		}
		$id = 'notification-' . rand();

		ob_start();
		// notification template
		?>
		<div id="%%ID%%" class="alert alert-dismissable %%CLASS%%" style="opacity: 1; padding-top: 6px; padding-bottom: 6px; animation: fadeIn 1.5s ease-out; z-index: 100; position: relative;">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			%%MSG%%
		</div>
		<script>
			$j(function() {
				var autoDismiss = <?php echo $fadeout ? 'true' : 'false'; ?>,
					embedded = !$j('nav').length,
					messageDelay = 10, fadeDelay = 1.5;

				if(!autoDismiss) {
					if(embedded)
						$j('#%%ID%%').before('<div class="modal-top-spacer"></div>');
					else
						$j('#%%ID%%').css({ margin: '0 0 1rem' });

					return;
				}

				// below code runs only in case of autoDismiss

				if(embedded)
					$j('#%%ID%%').css({ margin: '1rem 0 -1rem' });
				else
					$j('#%%ID%%').css({ margin: '-15px 0 -20px' });

				setTimeout(function() {
					$j('#%%ID%%').css({    animation: 'fadeOut ' + fadeDelay + 's ease-out' });
				}, messageDelay * 1000);

				setTimeout(function() {
					$j('#%%ID%%').css({    visibility: 'hidden' });
				}, (messageDelay + fadeDelay) * 1000);
			})
		</script>
		<style>
			@keyframes fadeIn {
				0%   { opacity: 0; }
				100% { opacity: 1; }
			}
			@keyframes fadeOut {
				0%   { opacity: 1; }
				100% { opacity: 0; }
			}
		</style>

		<?php
		$out = ob_get_clean();

		$out = str_replace('%%ID%%', $id, $out);
		$out = str_replace('%%MSG%%', $msg, $out);
		$out = str_replace('%%CLASS%%', $class, $out);

		return $out;
	}

	#########################################################

	function validMySQLDate($date) {
		$date = trim($date);

		try {
			$dtObj = new DateTime($date);
		} catch(Exception $e) {
			return false;
		}

		$parts = explode('-', $date);
		return (
			count($parts) == 3
			// see https://dev.mysql.com/doc/refman/8.0/en/datetime.html
			&& intval($parts[0]) >= 1000
			&& intval($parts[0]) <= 9999
			&& intval($parts[1]) >= 1
			&& intval($parts[1]) <= 12
			&& intval($parts[2]) >= 1
			&& intval($parts[2]) <= 31
		);
	}

	#########################################################

	function parseMySQLDate($date, $altDate) {
		// is $date valid?
		if(validMySQLDate($date)) return trim($date);

		if($date != '--' && validMySQLDate($altDate)) return trim($altDate);

		if($date != '--' && $altDate && is_numeric($altDate))
			return @date('Y-m-d', @time() + ($altDate >= 1 ? $altDate - 1 : $altDate) * 86400);

		return '';
	}

	#########################################################

	function parseCode($code, $isInsert = true, $rawData = false) {
		$mi = Authentication::getUser();

		if($isInsert) {
			$arrCodes = [
				'<%%creatorusername%%>' => $mi['username'],
				'<%%creatorgroupid%%>' => $mi['groupId'],
				'<%%creatorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%creatorgroup%%>' => $mi['group'],

				'<%%creationdate%%>' => ($rawData ? date('Y-m-d') : date(app_datetime_format('phps'))),
				'<%%creationtime%%>' => ($rawData ? date('H:i:s') : date(app_datetime_format('phps', 't'))),
				'<%%creationdatetime%%>' => ($rawData ? date('Y-m-d H:i:s') : date(app_datetime_format('phps', 'dt'))),
				'<%%creationtimestamp%%>' => ($rawData ? date('Y-m-d H:i:s') : time()),
			];
		} else {
			$arrCodes = [
				'<%%editorusername%%>' => $mi['username'],
				'<%%editorgroupid%%>' => $mi['groupId'],
				'<%%editorip%%>' => $_SERVER['REMOTE_ADDR'],
				'<%%editorgroup%%>' => $mi['group'],

				'<%%editingdate%%>' => ($rawData ? date('Y-m-d') : date(app_datetime_format('phps'))),
				'<%%editingtime%%>' => ($rawData ? date('H:i:s') : date(app_datetime_format('phps', 't'))),
				'<%%editingdatetime%%>' => ($rawData ? date('Y-m-d H:i:s') : date(app_datetime_format('phps', 'dt'))),
				'<%%editingtimestamp%%>' => ($rawData ? date('Y-m-d H:i:s') : time()),
			];
		}

		$pc = str_ireplace(array_keys($arrCodes), array_values($arrCodes), $code);

		return $pc;
	}

	#########################################################

	function parseMySQLDateTime($datetime, $altDateTime) {
		// is $datetime valid?
		if(mysql_datetime($datetime)) return mysql_datetime($datetime);

		if($altDateTime === '') return '';

		// is $altDateTime valid?
		if(mysql_datetime($altDateTime)) return mysql_datetime($altDateTime);

		/* parse $altDateTime */
		$matches = [];
		if(!preg_match('/^([+-])(\d+)(s|m|h|d)(0)?$/', $altDateTime, $matches))
			return '';

		$sign = ($matches[1] == '-' ? -1 : 1);
		$unit = $matches[3];
		$qty = $matches[2];

		// m0 means increment minutes, set seconds to 0
		// h0 means increment hours, set minutes and seconds to 0
		// d0 means increment days, set time to 00:00:00
		$zeroTime = $matches[4] == '0';

		switch($unit) {
			case 's':
				$seconds = $qty * $sign;
				break;
			case 'm':
				$seconds = $qty * 60 * $sign;
				if($zeroTime) return @date('Y-m-d H:i:00', @time() + $seconds);
				break;
			case 'h':
				$seconds = $qty * 3600 * $sign;
				if($zeroTime) return @date('Y-m-d H:00:00', @time() + $seconds);
				break;
			case 'd':
				$seconds = $qty * 86400 * $sign;
				if($zeroTime) return @date('Y-m-d 00:00:00', @time() + $seconds);
				break;
		}

		return @date('Y-m-d H:i:s', @time() + $seconds);
	}

	#########################################################

	function addFilter($index, $filterAnd, $filterField, $filterOperator, $filterValue) {
		// validate input
		if($index < 1 || $index > 80 || !is_int($index)) return false;
		if($filterAnd != 'or')   $filterAnd = 'and';
		$filterField = intval($filterField);

		/* backward compatibility */
		if(in_array($filterOperator, FILTER_OPERATORS)) {
			$filterOperator = array_search($filterOperator, FILTER_OPERATORS);
		}

		if(!in_array($filterOperator, array_keys(FILTER_OPERATORS))) {
			$filterOperator = 'like';
		}

		if(!$filterField) {
			$filterOperator = '';
			$filterValue = '';
		}

		$_REQUEST['FilterAnd'][$index] = $filterAnd;
		$_REQUEST['FilterField'][$index] = $filterField;
		$_REQUEST['FilterOperator'][$index] = $filterOperator;
		$_REQUEST['FilterValue'][$index] = $filterValue;

		return true;
	}

	#########################################################

	function clearFilters() {
		for($i=1; $i<=80; $i++) {
			addFilter($i, '', 0, '', '');
		}
	}

	#########################################################

	/**
	* Loads a given view from the templates folder, passing the given data to it
	* @param $view the name of a php file (without extension) to be loaded from the 'templates' folder
	* @param $the_data_to_pass_to_the_view (optional) associative array containing the data to pass to the view
	* @return string the output of the parsed view
	*/
	function loadView($view, $the_data_to_pass_to_the_view = false) {
		global $Translation;

		$view = __DIR__ . "/templates/$view.php";
		if(!is_file($view)) return false;

		if(is_array($the_data_to_pass_to_the_view)) {
			foreach($the_data_to_pass_to_the_view as $data_k => $data_v)
				$$data_k = $data_v;
		}
		unset($the_data_to_pass_to_the_view, $data_k, $data_v);

		ob_start();
		@include($view);
		return ob_get_clean();
	}

	#########################################################

	/**
	* Loads a table template from the templates folder, passing the given data to it
	* @param $table_name the name of the table whose template is to be loaded from the 'templates' folder
	* @param $the_data_to_pass_to_the_table associative array containing the data to pass to the table template
	* @return the output of the parsed table template as a string
	*/
	function loadTable($table_name, $the_data_to_pass_to_the_table = []) {
		$dont_load_header = $the_data_to_pass_to_the_table['dont_load_header'];
		$dont_load_footer = $the_data_to_pass_to_the_table['dont_load_footer'];

		$header = $table = $footer = '';

		if(!$dont_load_header) {
			// try to load tablename-header
			if(!($header = loadView("{$table_name}-header", $the_data_to_pass_to_the_table))) {
				$header = loadView('table-common-header', $the_data_to_pass_to_the_table);
			}
		}

		$table = loadView($table_name, $the_data_to_pass_to_the_table);

		if(!$dont_load_footer) {
			// try to load tablename-footer
			if(!($footer = loadView("{$table_name}-footer", $the_data_to_pass_to_the_table))) {
				$footer = loadView('table-common-footer', $the_data_to_pass_to_the_table);
			}
		}

		return "{$header}{$table}{$footer}";
	}

	#########################################################

	function br2nl($text) {
		return  preg_replace('/\<br(\s*)?\/?\>/i', "\n", $text);
	}

	#########################################################

	function entitiesToUTF8($input) {
		return preg_replace_callback('/(&#[0-9]+;)/', '_toUTF8', $input);
	}

	function _toUTF8($m) {
		if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
		} else {
			return $m[1];
		}
	}

	#########################################################

	function func_get_args_byref() {
		if(!function_exists('debug_backtrace')) return false;

		$trace = debug_backtrace();
		return $trace[1]['args'];
	}

	#########################################################

	function permissions_sql($table, $level = 'all') {
		if(!in_array($level, ['user', 'group'])) { $level = 'all'; }
		$perm = getTablePermissions($table);
		$from = '';
		$where = '';
		$pk = getPKFieldName($table);

		if($perm['view'] == 1 || ($perm['view'] > 1 && $level == 'user')) { // view owner only
			$from = 'membership_userrecords';
			$where = "(`$table`.`$pk`=membership_userrecords.pkValue and membership_userrecords.tableName='$table' and lcase(membership_userrecords.memberID)='" . getLoggedMemberID() . "')";
		} elseif($perm['view'] == 2 || ($perm['view'] > 2 && $level == 'group')) { // view group only
			$from = 'membership_userrecords';
			$where = "(`$table`.`$pk`=membership_userrecords.pkValue and membership_userrecords.tableName='$table' and membership_userrecords.groupID='" . getLoggedGroupID() . "')";
		} elseif($perm['view'] == 3) { // view all
			// no further action
		} elseif($perm['view'] == 0) { // view none
			return false;
		}

		return ['where' => $where, 'from' => $from, 0 => $where, 1 => $from];
	}

	#########################################################

	function error_message($msg, $back_url = '', $full_page = true) {
		global $Translation;

		ob_start();

		if($full_page) include(__DIR__ . '/header.php');

		echo '<div class="panel panel-danger">';
			echo '<div class="panel-heading"><h3 class="panel-title">' . $Translation['error:'] . '</h3></div>';
			echo '<div class="panel-body"><p class="text-danger">' . $msg . '</p>';
			if($back_url !== false) { // explicitly passing false suppresses the back link completely
				echo '<div class="text-center">';
				if($back_url) {
					echo '<a href="' . $back_url . '" class="btn btn-danger btn-lg vspacer-lg"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['< back'] . '</a>';
				// in embedded mode, close modal window
				} elseif(Request::val('Embedded')) {
					echo '<button class="btn btn-danger btn-lg" type="button" onclick="AppGini.closeParentModal();"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['< back'] . '</button>';
				} else {
					echo '<a href="#" class="btn btn-danger btn-lg vspacer-lg" onclick="history.go(-1); return false;"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['< back'] . '</a>';
				}
				echo '</div>';
			}
			echo '</div>';
		echo '</div>';

		if($full_page) include(__DIR__ . '/footer.php');

		return ob_get_clean();
	}

	#########################################################

	function toMySQLDate($formattedDate, $sep = datalist_date_separator, $ord = datalist_date_format) {
		// extract date elements
		$de=explode($sep, $formattedDate);
		$mySQLDate=intval($de[strpos($ord, 'Y')]).'-'.intval($de[strpos($ord, 'm')]).'-'.intval($de[strpos($ord, 'd')]);
		return $mySQLDate;
	}

	#########################################################

	function reIndex(&$arr) {
		$i=1;
		foreach($arr as $n=>$v) {
			$arr2[$i]=$n;
			$i++;
		}
		return $arr2;
	}

	#########################################################

	function get_embed($provider, $url, $max_width = '', $max_height = '', $retrieve = 'html') {
		global $Translation;
		if(!$url) return '';

		$providers = [
			'youtube' => ['oembed' => 'https://www.youtube.com/oembed', 'regex' => '/^http.*(youtu\.be|youtube\.com)\/.*/i'],
			'vimeo' => ['oembed' => 'https://vimeo.com/api/oembed.json', 'regex' => '/^http.*vimeo\.com\/.*/i'],
			'googlemap' => ['oembed' => '', 'regex' => '/^http.*\.google\..*maps/i'],
			'dailymotion' => ['oembed' => 'https://www.dailymotion.com/services/oembed', 'regex' => '/^http.*(dailymotion\.com|dai\.ly)\/.*/i'],
			'videofileurl' => ['oembed' => '', 'regex' => '/\.(mp4|webm|ogg|ogv)$/i'],
		];

		if(!$max_height) $max_height = 360;
		if(!$max_width) $max_width = 480;

		if(!isset($providers[$provider])) {
			// try detecting provider from URL based on regex
			foreach($providers as $p => $opts) {
				if(preg_match($opts['regex'], $url)) {
					$provider = $p;
					break;
				}
			}

			if(!isset($providers[$provider]))
				return '<div class="text-danger">' . $Translation['invalid provider'] . '</div>';
		}

		if(isset($providers[$provider]['regex']) && !preg_match($providers[$provider]['regex'], $url)) {
			return '<div class="text-danger">' . $Translation['invalid url'] . '</div>';
		}

		if($providers[$provider]['oembed']) {
			$oembed = $providers[$provider]['oembed'] . '?url=' . urlencode($url) . "&amp;maxwidth={$max_width}&amp;maxheight={$max_height}&amp;format=json";
			$data_json = request_cache($oembed);

			$data = json_decode($data_json, true);
			if($data === null) {
				/* an error was returned rather than a json string */
				if($retrieve == 'html') return "<div class=\"text-danger\">{$data_json}\n<!-- {$oembed} --></div>";
				return '';
			}

			// if html data not empty, apply max width and height in place of provided height and width
			$provided_width = $data['width'] ?? null;
			$provided_height = $data['height'] ?? null;
			if($provided_width && $provided_height) {
				$aspect_ratio = $provided_width / $provided_height;
				if($max_width / $aspect_ratio < $max_height) {
					$max_height = intval($max_width / $aspect_ratio);
				} else {
					$max_width = intval($max_height * $aspect_ratio);
				}

				$data['html'] = str_replace("width=\"{$provided_width}\"", "width=\"{$max_width}\"", $data['html']);
				$data['html'] = str_replace("height=\"{$provided_height}\"", "height=\"{$max_height}\"", $data['html']);
			}

			return (isset($data[$retrieve]) ? $data[$retrieve] : $data['html']);
		}

		/* special cases (where there is no oEmbed provider) */
		if($provider == 'googlemap') return get_embed_googlemap($url, $max_width, $max_height, $retrieve);
		if($provider == 'videofileurl') return get_embed_videofileurl($url, $max_width, $max_height, $retrieve);

		return '<div class="text-danger">' . $Translation['invalid provider'] . '</div>';
	}

	#########################################################

	function get_embed_videofileurl($url, $max_width = '', $max_height = '', $retrieve = 'html') {
		global $Translation;

		$allowed_exts = ['mp4', 'webm', 'ogg', 'ogv'];
		$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

		if(!in_array($ext, $allowed_exts)) {
			return '<div class="text-danger">' . $Translation['invalid url'] . '</div>';
		}

		$video = "<video controls style=\"max-width: 100%%; height: auto;\" src=\"%s\"></video>";

		switch($retrieve) {
			case 'html':
				return sprintf($video, $url);
			default: // 'thumbnail'
				return '';
		}
	}

	#########################################################

	function get_embed_googlemap($url, $max_width = '', $max_height = '', $retrieve = 'html') {
		global $Translation;
		$url_parts = parse_url($url);
		$coords_regex = '/-?\d+(\.\d+)?[,+]-?\d+(\.\d+)?(,\d{1,2}z)?/'; /* https://stackoverflow.com/questions/2660201 */

		if(!preg_match($coords_regex, $url_parts['path'] . '?' . $url_parts['query'], $m))
			return '<div class="text-danger">' . $Translation['cant retrieve coordinates from url'] . '</div>';

		list($lat, $long, $zoom) = explode(',', $m[0]);
		$zoom = intval($zoom);
		if(!$zoom) $zoom = 15; /* default zoom */
		if(!$max_height) $max_height = 360;
		if(!$max_width) $max_width = 480;

		$api_key = config('adminConfig')['googleAPIKey'];

		// if max_height is all numeric, append 'px' to it
		$frame_height = $max_height;
		if(is_numeric($frame_height)) $frame_height .= 'px';

		$embed_url = 'https://www.google.com/maps/embed/v1/%s?' . http_build_query([
			'key' => $api_key,
			'zoom' => $zoom,
			'maptype' => 'roadmap',
		], '', '&amp;');

		$thumbnail_url = 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query([
			'key' => $api_key,
			'zoom' => $zoom,
			'maptype' => 'roadmap',
			'size' => "{$max_width}x{$max_height}",
			'center' => "$lat,$long",
		], '', '&amp;');

		$iframe = "<iframe allowfullscreen loading=\"lazy\" style=\"border: none; width: 100%%; height: $frame_height;\" src=\"%s\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>";

		switch($retrieve) {
			case 'html':
				$embed_url = sprintf($embed_url, 'view') . '&amp;' . http_build_query(['center' => "$lat,$long"]);
				return sprintf($iframe, $embed_url);
			case 'html-pinpoint':
				$embed_url = sprintf($embed_url, 'place') . '&amp;' . http_build_query(['q' => "$lat,$long"]);
				return sprintf($iframe, $embed_url);
			case 'thumbnail-pinpoint':
				return $thumbnail_url . '&amp;' . http_build_query(['markers' => "$lat,$long"]);
			default: // 'thumbnail'
				return $thumbnail_url;
		}
	}

	#########################################################

	function request_cache($request, $force_fetch = false) {
		static $cache_table_exists = null;
		$max_cache_lifetime = 7 * 86400; /* max cache lifetime in seconds before refreshing from source */

		// force fetching request if no cache table exists
		if($cache_table_exists === null)
			$cache_table_exists = sqlValue("show tables like 'membership_cache'");

		if(!$cache_table_exists)
			return request_cache($request, true);

		/* retrieve response from cache if exists */
		if(!$force_fetch) {
			$res = sql("select response, request_ts from membership_cache where request='" . md5($request) . "'", $eo);
			if(!$row = db_fetch_array($res)) return request_cache($request, true);

			$response = $row[0];
			$response_ts = $row[1];
			if($response_ts < time() - $max_cache_lifetime) return request_cache($request, true);
		}

		/* if no response in cache, issue a request */
		if(!$response || $force_fetch) {
			$response = @file_get_contents($request);
			if($response === false) {
				$error = error_get_last();
				$error_message = preg_replace('/.*: (.*)/', '$1', $error['message']);
				return $error_message;
			} elseif($cache_table_exists) {
				/* store response in cache */
				$ts = time();
				sql("replace into membership_cache set request='" . md5($request) . "', request_ts='{$ts}', response='" . makeSafe($response, false) . "'", $eo);
			}
		}

		return $response;
	}

	#########################################################

	function check_record_permission($table, $id, $perm = 'view') {
		if($perm != 'edit' && $perm != 'delete') $perm = 'view';

		$perms = getTablePermissions($table);
		if(!$perms[$perm]) return false;

		$safe_id = makeSafe($id);
		$safe_table = makeSafe($table);

		// fix for zero-fill: quote id only if not numeric
		if(!is_numeric($safe_id)) $safe_id = "'$safe_id'";

		if($perms[$perm] == 1) { // own records only
			$username = getLoggedMemberID();
			$owner = sqlValue("select memberID from membership_userrecords where tableName='{$safe_table}' and pkValue={$safe_id}");
			if($owner == $username) return true;
		} elseif($perms[$perm] == 2) { // group records
			$group_id = getLoggedGroupID();
			$owner_group_id = sqlValue("select groupID from membership_userrecords where tableName='{$safe_table}' and pkValue={$safe_id}");
			if($owner_group_id == $group_id) return true;
		} elseif($perms[$perm] == 3) { // all records
			return true;
		}

		return false;
	}

	#########################################################

	function NavMenus($options = []) {
		if(!defined('PREPEND_PATH')) define('PREPEND_PATH', '');
		global $Translation;
		$prepend_path = PREPEND_PATH;

		/* default options */
		if(empty($options)) {
			$options = ['tabs' => 7];
		}

		$table_group_name = array_keys(get_table_groups()); /* 0 => group1, 1 => group2 .. */
		/* if only one group named 'None', set to translation of 'select a table' */
		if((count($table_group_name) == 1 && $table_group_name[0] == 'None') || count($table_group_name) < 1) $table_group_name[0] = $Translation['select a table'];
		$table_group_index = array_flip($table_group_name); /* group1 => 0, group2 => 1 .. */
		$menu = array_fill(0, count($table_group_name), '');

		$t = time();
		$arrTables = getTableList();
		if(is_array($arrTables)) {
			foreach($arrTables as $tn => $tc) {
				/* ---- list of tables where hide link in nav menu is set ---- */
				$tChkHL = array_search($tn, ['suggestion','event_table','event_outcomes_expected_table','event_participants_table','event_decision_table','meetings_table','meetings_agenda_table','meetings_participants_table','meetings_decision_table','visiting_card_table']);

				/* ---- list of tables where filter first is set ---- */
				$tChkFF = array_search($tn, []);
				if($tChkFF !== false && $tChkFF !== null) {
					$searchFirst = '&Filter_x=1';
				} else {
					$searchFirst = '';
				}

				/* when no groups defined, $table_group_index['None'] is NULL, so $menu_index is still set to 0 */
				$menu_index = intval($table_group_index[$tc[3]]);
				if(!$tChkHL && $tChkHL !== 0) $menu[$menu_index] .= "<li><a href=\"{$prepend_path}{$tn}_view.php?t={$t}{$searchFirst}\"><img src=\"{$prepend_path}" . ($tc[2] ? $tc[2] : 'blank.gif') . "\" height=\"32\"> {$tc[0]}</a></li>";
			}
		}

		// custom nav links, as defined in "hooks/links-navmenu.php" 
		global $navLinks;
		if(is_array($navLinks)) {
			$memberInfo = getMemberInfo();
			$links_added = [];
			foreach($navLinks as $link) {
				if(!isset($link['url']) || !isset($link['title'])) continue;
				if(getLoggedAdmin() !== false || @in_array($memberInfo['group'], $link['groups']) || @in_array('*', $link['groups'])) {
					$menu_index = intval($link['table_group']);
					if(!$links_added[$menu_index]) $menu[$menu_index] .= '<li class="divider"></li>';

					/* add prepend_path to custom links if they aren't absolute links */
					if(!preg_match('/^(http|\/\/)/i', $link['url'])) $link['url'] = $prepend_path . $link['url'];
					if(!preg_match('/^(http|\/\/)/i', $link['icon']) && $link['icon']) $link['icon'] = $prepend_path . $link['icon'];

					$menu[$menu_index] .= "<li><a href=\"{$link['url']}\"><img src=\"" . ($link['icon'] ? $link['icon'] : "{$prepend_path}blank.gif") . "\" height=\"32\"> {$link['title']}</a></li>";
					$links_added[$menu_index]++;
				}
			}
		}

		$menu_wrapper = '';
		for($i = 0; $i < count($menu); $i++) {
			$menu_wrapper .= <<<EOT
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">{$table_group_name[$i]} <b class="caret"></b></a>
					<ul class="dropdown-menu" role="menu">{$menu[$i]}</ul>
				</li>
EOT;
		}

		return $menu_wrapper;
	}

	#########################################################

	function StyleSheet() {
		if(!defined('PREPEND_PATH')) define('PREPEND_PATH', '');
		$prepend_path = PREPEND_PATH;
		$mtime = filemtime( __DIR__ . '/dynamic.css');

		$css_links = <<<EOT

			<link rel="stylesheet" href="{$prepend_path}resources/initializr/css/spacelab.css">
			<link rel="stylesheet" href="{$prepend_path}resources/lightbox/css/lightbox.css" media="screen">
			<link rel="stylesheet" href="{$prepend_path}resources/select2/select2.css" media="screen">
			<link rel="stylesheet" href="{$prepend_path}resources/timepicker/bootstrap-timepicker.min.css" media="screen">
			<link rel="stylesheet" href="{$prepend_path}dynamic.css?{$mtime}">
EOT;

		return $css_links;
	}

	#########################################################

	function PrepareUploadedFile($FieldName, $MaxSize, $FileTypes = 'jpg|jpeg|gif|png|webp', $NoRename = false, $dir = '') {
		global $Translation;
		$f = $_FILES[$FieldName];
		if($f['error'] == 4 || !$f['name']) return '';

		$dir = getUploadDir($dir);

		/* get php.ini upload_max_filesize in bytes */
		$php_upload_size_limit = toBytes(ini_get('upload_max_filesize'));
		$MaxSize = min($MaxSize, $php_upload_size_limit);

		if($f['size'] > $MaxSize || $f['error']) {
			echo error_message(str_replace(['<MaxSize>', '{MaxSize}'], intval($MaxSize / 1024), $Translation['file too large']));
			exit;
		}
		if(!preg_match('/\.(' . $FileTypes . ')$/i', $f['name'], $ft)) {
			echo error_message(str_replace(['<FileTypes>', '{FileTypes}'], str_replace('|', ', ', $FileTypes), $Translation['invalid file type']));
			exit;
		}

		$name = str_replace(' ', '_', $f['name']);
		if(!$NoRename) $name = substr(md5(microtime() . rand(0, 100000)), -17) . $ft[0];

		if(!file_exists($dir)) @mkdir($dir, 0777);

		if(!@move_uploaded_file($f['tmp_name'], $dir . $name)) {
			echo error_message("Couldn't save the uploaded file. Try chmoding the upload folder '{$dir}' to 777.");
			exit;
		}

		@chmod($dir . $name, 0666);
		return $name;
	}

	#########################################################

	function get_home_links($homeLinks, $default_classes, $tgroup = '') {
		if(!is_array($homeLinks) || !count($homeLinks)) return '';

		$memberInfo = getMemberInfo();

		ob_start();
		foreach($homeLinks as $link) {
			if(!isset($link['url']) || !isset($link['title'])) continue;
			if($tgroup != $link['table_group'] && $tgroup != '*') continue;

			/* fall-back classes if none defined */
			if(!$link['grid_column_classes']) $link['grid_column_classes'] = $default_classes['grid_column'];
			if(!$link['panel_classes']) $link['panel_classes'] = $default_classes['panel'];
			if(!$link['link_classes']) $link['link_classes'] = $default_classes['link'];

			if(getLoggedAdmin() !== false || @in_array($memberInfo['group'], $link['groups']) || @in_array('*', $link['groups'])) {
				?>
				<div class="col-xs-12 <?php echo $link['grid_column_classes']; ?>">
					<div class="panel <?php echo $link['panel_classes']; ?>">
						<div class="panel-body">
							<a class="btn btn-block btn-lg <?php echo $link['link_classes']; ?>" title="<?php echo preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", html_attr(strip_tags($link['description']))); ?>" href="<?php echo $link['url']; ?>"><?php echo ($link['icon'] ? '<img src="' . $link['icon'] . '">' : ''); ?><strong><?php echo $link['title']; ?></strong></a>
							<div class="panel-body-description"><?php echo $link['description']; ?></div>
						</div>
					</div>
				</div>
				<?php
			}
		}

		return ob_get_clean();
	}

	#########################################################

	function quick_search_html($search_term, $label, $separate_dv = true) {
		global $Translation;

		$safe_search = html_attr($search_term);
		$safe_label = html_attr($label);
		$safe_clear_label = html_attr($Translation['Reset Filters']);

		if($separate_dv) {
			$reset_selection = "document.forms[0].SelectedID.value = '';";
		} else {
			$reset_selection = "document.forms[0].writeAttribute('novalidate', 'novalidate');";
		}
		$reset_selection .= ' document.forms[0].NoDV.value=1; return true;';

		$html = <<<EOT
		<div class="input-group" id="quick-search">
			<input type="text" id="SearchString" name="SearchString" value="{$safe_search}" class="form-control" placeholder="{$safe_label}">
			<span class="input-group-btn">
				<button name="Search_x" value="1" id="Search" type="submit" onClick="{$reset_selection}" class="btn btn-default" title="{$safe_label}"><i class="glyphicon glyphicon-search"></i></button>
				<button name="ClearQuickSearch" value="1" id="ClearQuickSearch" type="submit" onClick="\$j('#SearchString').val(''); {$reset_selection}" class="btn btn-default" title="{$safe_clear_label}"><i class="glyphicon glyphicon-remove-circle"></i></button>
			</span>
		</div>
EOT;
		return $html;
	}

	#########################################################

	function getLookupFields($skipPermissions = false, $filterByPermission = 'view') {
		$pcConfig = [
			'tenants' => [
			],
			'user_table' => [
			],
			'suggestion' => [
			],
			'event_table' => [
			],
			'event_outcomes_expected_table' => [
				'event_lookup' => [
					'parent-table' => 'event_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Outcomes expected table <span class="hidden child-label-event_outcomes_expected_table child-field-caption">(Event Details)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Event Details', 2 => 'Target audience', 3 => 'Expected outcomes', 4 => 'Created by', 5 => 'Created at', 6 => 'Last updated by', 7 => 'Last updated at', 8 => 'Tenant ID', 9 => 'Outcomes expected str'],
					'display-field-names' => [0 => 'id', 1 => 'event_lookup', 2 => 'target_audience', 3 => 'expected_outcomes', 4 => 'created_by', 5 => 'created_at', 6 => 'last_updated_by', 7 => 'last_updated_at', 8 => 'tenant_id', 9 => 'outcomes_expected_str'],
					'sortable-fields' => [0 => '`event_outcomes_expected_table`.`id`', 1 => '`event_table1`.`event_str`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => '`event_outcomes_expected_table`.`tenant_id`', 9 => 10],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-event_outcomes_expected_table',
					'template-printable' => 'children-event_outcomes_expected_table-printable',
					'query' => "SELECT `event_outcomes_expected_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') as 'event_lookup', `event_outcomes_expected_table`.`target_audience` as 'target_audience', `event_outcomes_expected_table`.`expected_outcomes` as 'expected_outcomes', `event_outcomes_expected_table`.`created_by` as 'created_by', `event_outcomes_expected_table`.`created_at` as 'created_at', `event_outcomes_expected_table`.`last_updated_by` as 'last_updated_by', `event_outcomes_expected_table`.`last_updated_at` as 'last_updated_at', `event_outcomes_expected_table`.`tenant_id` as 'tenant_id', `event_outcomes_expected_table`.`outcomes_expected_str` as 'outcomes_expected_str' FROM `event_outcomes_expected_table` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`event_outcomes_expected_table`.`event_lookup` "
				],
			],
			'event_participants_table' => [
				'event_lookup' => [
					'parent-table' => 'event_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Participants / Speaker / VIP List - App <span class="hidden child-label-event_participants_table child-field-caption">(Event)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Event', 2 => 'Name', 3 => 'Designation', 4 => 'Participant type', 5 => 'Accepted status', 6 => 'Status date', 7 => 'Created by', 8 => 'Created at', 9 => 'Last updated by', 10 => 'Last updated at', 11 => 'Event participants str', 12 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 1 => 'event_lookup', 2 => 'name', 3 => 'designation', 4 => 'participant_type', 5 => 'accepted_status', 6 => 'status_date', 7 => 'created_by', 8 => 'created_at', 9 => 'last_updated_by', 10 => 'last_updated_at', 11 => 'event_participants_str', 12 => 'tenant_id'],
					'sortable-fields' => [0 => '`event_participants_table`.`id`', 1 => '`event_table1`.`event_str`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => '`event_participants_table`.`status_date`', 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => '`event_participants_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-event_participants_table',
					'template-printable' => 'children-event_participants_table-printable',
					'query' => "SELECT `event_participants_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') as 'event_lookup', `event_participants_table`.`name` as 'name', `event_participants_table`.`designation` as 'designation', `event_participants_table`.`participant_type` as 'participant_type', `event_participants_table`.`accepted_status` as 'accepted_status', if(`event_participants_table`.`status_date`,date_format(`event_participants_table`.`status_date`,'%d/%m/%Y'),'') as 'status_date', `event_participants_table`.`created_by` as 'created_by', `event_participants_table`.`created_at` as 'created_at', `event_participants_table`.`last_updated_by` as 'last_updated_by', `event_participants_table`.`last_updated_at` as 'last_updated_at', `event_participants_table`.`event_participants_str` as 'event_participants_str', `event_participants_table`.`tenant_id` as 'tenant_id' FROM `event_participants_table` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`event_participants_table`.`event_lookup` "
				],
			],
			'event_decision_table' => [
				'outcomes_expected_lookup' => [
					'parent-table' => 'event_outcomes_expected_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Decision - App <span class="hidden child-label-event_decision_table child-field-caption">(Expected Outcomes of Meeting)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Expected Outcomes of Meeting', 2 => 'Decision description', 3 => 'Decision actor', 4 => 'Action taken with date', 5 => 'Decision status', 6 => 'Decision status update date', 7 => 'Decision status remarks by superior', 8 => 'Created by', 9 => 'Created at', 10 => 'Last updated by', 11 => 'Last updated at', 12 => 'Tenant ID', 13 => 'Event decision str'],
					'display-field-names' => [0 => 'id', 1 => 'outcomes_expected_lookup', 2 => 'decision_description', 3 => 'decision_actor', 4 => 'action_taken_with_date', 5 => 'decision_status', 6 => 'decision_status_update_date', 7 => 'decision_status_remarks_by_superior', 8 => 'created_by', 9 => 'created_at', 10 => 'last_updated_by', 11 => 'last_updated_at', 12 => 'tenant_id', 13 => 'event_decision_str'],
					'sortable-fields' => [0 => '`event_decision_table`.`id`', 1 => '`event_outcomes_expected_table1`.`outcomes_expected_str`', 2 => 3, 3 => 4, 4 => '`event_decision_table`.`action_taken_with_date`', 5 => 6, 6 => '`event_decision_table`.`decision_status_update_date`', 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => '`event_decision_table`.`tenant_id`', 13 => 14],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-event_decision_table',
					'template-printable' => 'children-event_decision_table-printable',
					'query' => "SELECT `event_decision_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_outcomes_expected_table1`.`outcomes_expected_str`), CONCAT_WS('',   `event_outcomes_expected_table1`.`outcomes_expected_str`), '') as 'outcomes_expected_lookup', `event_decision_table`.`decision_description` as 'decision_description', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'decision_actor', if(`event_decision_table`.`action_taken_with_date`,date_format(`event_decision_table`.`action_taken_with_date`,'%d/%m/%Y'),'') as 'action_taken_with_date', `event_decision_table`.`decision_status` as 'decision_status', if(`event_decision_table`.`decision_status_update_date`,date_format(`event_decision_table`.`decision_status_update_date`,'%d/%m/%Y'),'') as 'decision_status_update_date', `event_decision_table`.`decision_status_remarks_by_superior` as 'decision_status_remarks_by_superior', `event_decision_table`.`created_by` as 'created_by', `event_decision_table`.`created_at` as 'created_at', `event_decision_table`.`last_updated_by` as 'last_updated_by', `event_decision_table`.`last_updated_at` as 'last_updated_at', `event_decision_table`.`tenant_id` as 'tenant_id', `event_decision_table`.`event_decision_str` as 'event_decision_str' FROM `event_decision_table` LEFT JOIN `event_outcomes_expected_table` as event_outcomes_expected_table1 ON `event_outcomes_expected_table1`.`id`=`event_decision_table`.`outcomes_expected_lookup` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`event_decision_table`.`decision_actor` "
				],
				'decision_actor' => [
					'parent-table' => 'user_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Decision - App <span class="hidden child-label-event_decision_table child-field-caption">(Decision actor)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Expected Outcomes of Meeting', 2 => 'Decision description', 3 => 'Decision actor', 4 => 'Action taken with date', 5 => 'Decision status', 6 => 'Decision status update date', 7 => 'Decision status remarks by superior', 8 => 'Created by', 9 => 'Created at', 10 => 'Last updated by', 11 => 'Last updated at', 12 => 'Tenant ID', 13 => 'Event decision str'],
					'display-field-names' => [0 => 'id', 1 => 'outcomes_expected_lookup', 2 => 'decision_description', 3 => 'decision_actor', 4 => 'action_taken_with_date', 5 => 'decision_status', 6 => 'decision_status_update_date', 7 => 'decision_status_remarks_by_superior', 8 => 'created_by', 9 => 'created_at', 10 => 'last_updated_by', 11 => 'last_updated_at', 12 => 'tenant_id', 13 => 'event_decision_str'],
					'sortable-fields' => [0 => '`event_decision_table`.`id`', 1 => '`event_outcomes_expected_table1`.`outcomes_expected_str`', 2 => 3, 3 => 4, 4 => '`event_decision_table`.`action_taken_with_date`', 5 => 6, 6 => '`event_decision_table`.`decision_status_update_date`', 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => '`event_decision_table`.`tenant_id`', 13 => 14],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-event_decision_table',
					'template-printable' => 'children-event_decision_table-printable',
					'query' => "SELECT `event_decision_table`.`id` as 'id', IF(    CHAR_LENGTH(`event_outcomes_expected_table1`.`outcomes_expected_str`), CONCAT_WS('',   `event_outcomes_expected_table1`.`outcomes_expected_str`), '') as 'outcomes_expected_lookup', `event_decision_table`.`decision_description` as 'decision_description', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'decision_actor', if(`event_decision_table`.`action_taken_with_date`,date_format(`event_decision_table`.`action_taken_with_date`,'%d/%m/%Y'),'') as 'action_taken_with_date', `event_decision_table`.`decision_status` as 'decision_status', if(`event_decision_table`.`decision_status_update_date`,date_format(`event_decision_table`.`decision_status_update_date`,'%d/%m/%Y'),'') as 'decision_status_update_date', `event_decision_table`.`decision_status_remarks_by_superior` as 'decision_status_remarks_by_superior', `event_decision_table`.`created_by` as 'created_by', `event_decision_table`.`created_at` as 'created_at', `event_decision_table`.`last_updated_by` as 'last_updated_by', `event_decision_table`.`last_updated_at` as 'last_updated_at', `event_decision_table`.`tenant_id` as 'tenant_id', `event_decision_table`.`event_decision_str` as 'event_decision_str' FROM `event_decision_table` LEFT JOIN `event_outcomes_expected_table` as event_outcomes_expected_table1 ON `event_outcomes_expected_table1`.`id`=`event_decision_table`.`outcomes_expected_lookup` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`event_decision_table`.`decision_actor` "
				],
			],
			'meetings_table' => [
				'visiting_card_lookup' => [
					'parent-table' => 'visiting_card_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Meetings - App <span class="hidden child-label-meetings_table child-field-caption">(Visiting card details)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 3 => 'Meeting title', 4 => 'Participants', 5 => 'Venue', 6 => 'Meeting from date', 7 => 'Meeting to date', 8 => 'Created by', 9 => 'Created at', 10 => 'Last updated by', 11 => 'Last updated at', 12 => 'Meetings str', 13 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 3 => 'meeting_title', 4 => 'participants', 5 => 'venue', 6 => 'meeting_from_date', 7 => 'meeting_to_date', 8 => 'created_by', 9 => 'created_at', 10 => 'last_updated_by', 11 => 'last_updated_at', 12 => 'meetings_str', 13 => 'tenant_id'],
					'sortable-fields' => [0 => '`meetings_table`.`id`', 1 => '`visiting_card_table1`.`id`', 2 => '`event_table1`.`id`', 3 => 4, 4 => 5, 5 => 6, 6 => '`meetings_table`.`meeting_from_date`', 7 => '`meetings_table`.`meeting_to_date`', 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => 13, 13 => '`meetings_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 7,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-meetings_table',
					'template-printable' => 'children-meetings_table-printable',
					'query' => "SELECT `meetings_table`.`id` as 'id', IF(    CHAR_LENGTH(`visiting_card_table1`.`id`), CONCAT_WS('',   `visiting_card_table1`.`id`), '') as 'visiting_card_lookup', IF(    CHAR_LENGTH(`event_table1`.`id`), CONCAT_WS('',   `event_table1`.`id`), '') as 'event_lookup', `meetings_table`.`meeting_title` as 'meeting_title', `meetings_table`.`participants` as 'participants', `meetings_table`.`venue` as 'venue', if(`meetings_table`.`meeting_from_date`,date_format(`meetings_table`.`meeting_from_date`,'%d/%m/%Y'),'') as 'meeting_from_date', if(`meetings_table`.`meeting_to_date`,date_format(`meetings_table`.`meeting_to_date`,'%d/%m/%Y'),'') as 'meeting_to_date', `meetings_table`.`created_by` as 'created_by', `meetings_table`.`created_at` as 'created_at', `meetings_table`.`last_updated_by` as 'last_updated_by', `meetings_table`.`last_updated_at` as 'last_updated_at', `meetings_table`.`meetings_str` as 'meetings_str', `meetings_table`.`tenant_id` as 'tenant_id' FROM `meetings_table` LEFT JOIN `visiting_card_table` as visiting_card_table1 ON `visiting_card_table1`.`id`=`meetings_table`.`visiting_card_lookup` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`meetings_table`.`event_lookup` "
				],
				'event_lookup' => [
					'parent-table' => 'event_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Meetings - App <span class="hidden child-label-meetings_table child-field-caption">(Event Details)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 3 => 'Meeting title', 4 => 'Participants', 5 => 'Venue', 6 => 'Meeting from date', 7 => 'Meeting to date', 8 => 'Created by', 9 => 'Created at', 10 => 'Last updated by', 11 => 'Last updated at', 12 => 'Meetings str', 13 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 3 => 'meeting_title', 4 => 'participants', 5 => 'venue', 6 => 'meeting_from_date', 7 => 'meeting_to_date', 8 => 'created_by', 9 => 'created_at', 10 => 'last_updated_by', 11 => 'last_updated_at', 12 => 'meetings_str', 13 => 'tenant_id'],
					'sortable-fields' => [0 => '`meetings_table`.`id`', 1 => '`visiting_card_table1`.`id`', 2 => '`event_table1`.`id`', 3 => 4, 4 => 5, 5 => 6, 6 => '`meetings_table`.`meeting_from_date`', 7 => '`meetings_table`.`meeting_to_date`', 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => 13, 13 => '`meetings_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 7,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-meetings_table',
					'template-printable' => 'children-meetings_table-printable',
					'query' => "SELECT `meetings_table`.`id` as 'id', IF(    CHAR_LENGTH(`visiting_card_table1`.`id`), CONCAT_WS('',   `visiting_card_table1`.`id`), '') as 'visiting_card_lookup', IF(    CHAR_LENGTH(`event_table1`.`id`), CONCAT_WS('',   `event_table1`.`id`), '') as 'event_lookup', `meetings_table`.`meeting_title` as 'meeting_title', `meetings_table`.`participants` as 'participants', `meetings_table`.`venue` as 'venue', if(`meetings_table`.`meeting_from_date`,date_format(`meetings_table`.`meeting_from_date`,'%d/%m/%Y'),'') as 'meeting_from_date', if(`meetings_table`.`meeting_to_date`,date_format(`meetings_table`.`meeting_to_date`,'%d/%m/%Y'),'') as 'meeting_to_date', `meetings_table`.`created_by` as 'created_by', `meetings_table`.`created_at` as 'created_at', `meetings_table`.`last_updated_by` as 'last_updated_by', `meetings_table`.`last_updated_at` as 'last_updated_at', `meetings_table`.`meetings_str` as 'meetings_str', `meetings_table`.`tenant_id` as 'tenant_id' FROM `meetings_table` LEFT JOIN `visiting_card_table` as visiting_card_table1 ON `visiting_card_table1`.`id`=`meetings_table`.`visiting_card_lookup` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`meetings_table`.`event_lookup` "
				],
			],
			'meetings_agenda_table' => [
				'meeting_lookup' => [
					'parent-table' => 'meetings_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Agenda table <span class="hidden child-label-meetings_agenda_table child-field-caption">(Meeting)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Meeting', 2 => 'Agenda description', 3 => 'Created by', 4 => 'Created at', 5 => 'Last updated by', 6 => 'Last updated at', 7 => 'Meetings agenda str', 8 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 1 => 'meeting_lookup', 2 => 'agenda_description', 3 => 'created_by', 4 => 'created_at', 5 => 'last_updated_by', 6 => 'last_updated_at', 7 => 'meetings_agenda_str', 8 => 'tenant_id'],
					'sortable-fields' => [0 => '`meetings_agenda_table`.`id`', 1 => '`meetings_table1`.`meetings_str`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => '`meetings_agenda_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => false,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-meetings_agenda_table',
					'template-printable' => 'children-meetings_agenda_table-printable',
					'query' => "SELECT `meetings_agenda_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_table1`.`meetings_str`), CONCAT_WS('',   `meetings_table1`.`meetings_str`), '') as 'meeting_lookup', `meetings_agenda_table`.`agenda_description` as 'agenda_description', `meetings_agenda_table`.`created_by` as 'created_by', `meetings_agenda_table`.`created_at` as 'created_at', `meetings_agenda_table`.`last_updated_by` as 'last_updated_by', `meetings_agenda_table`.`last_updated_at` as 'last_updated_at', `meetings_agenda_table`.`meetings_agenda_str` as 'meetings_agenda_str', `meetings_agenda_table`.`tenant_id` as 'tenant_id' FROM `meetings_agenda_table` LEFT JOIN `meetings_table` as meetings_table1 ON `meetings_table1`.`id`=`meetings_agenda_table`.`meeting_lookup` "
				],
			],
			'meetings_participants_table' => [
				'meeting_lookup' => [
					'parent-table' => 'meetings_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Participants / Speaker / VIP List - App <span class="hidden child-label-meetings_participants_table child-field-caption">(Meeting)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Meeting', 2 => 'Name', 3 => 'Designation', 4 => 'Participant type', 5 => 'Accepted status', 6 => 'Status date', 7 => 'Created by', 8 => 'Created at', 9 => 'Last updated by', 10 => 'Last updated at', 11 => 'Meetings participants str', 12 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 1 => 'meeting_lookup', 2 => 'name', 3 => 'designation', 4 => 'participant_type', 5 => 'accepted_status', 6 => 'status_date', 7 => 'created_by', 8 => 'created_at', 9 => 'last_updated_by', 10 => 'last_updated_at', 11 => 'meetings_participants_str', 12 => 'tenant_id'],
					'sortable-fields' => [0 => '`meetings_participants_table`.`id`', 1 => '`meetings_table1`.`meetings_str`', 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => '`meetings_participants_table`.`status_date`', 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => '`meetings_participants_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-meetings_participants_table',
					'template-printable' => 'children-meetings_participants_table-printable',
					'query' => "SELECT `meetings_participants_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_table1`.`meetings_str`), CONCAT_WS('',   `meetings_table1`.`meetings_str`), '') as 'meeting_lookup', `meetings_participants_table`.`name` as 'name', `meetings_participants_table`.`designation` as 'designation', `meetings_participants_table`.`participant_type` as 'participant_type', `meetings_participants_table`.`accepted_status` as 'accepted_status', if(`meetings_participants_table`.`status_date`,date_format(`meetings_participants_table`.`status_date`,'%d/%m/%Y'),'') as 'status_date', `meetings_participants_table`.`created_by` as 'created_by', `meetings_participants_table`.`created_at` as 'created_at', `meetings_participants_table`.`last_updated_by` as 'last_updated_by', `meetings_participants_table`.`last_updated_at` as 'last_updated_at', `meetings_participants_table`.`meetings_participants_str` as 'meetings_participants_str', `meetings_participants_table`.`tenant_id` as 'tenant_id' FROM `meetings_participants_table` LEFT JOIN `meetings_table` as meetings_table1 ON `meetings_table1`.`id`=`meetings_participants_table`.`meeting_lookup` "
				],
			],
			'meetings_decision_table' => [
				'agenda_lookup' => [
					'parent-table' => 'meetings_agenda_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Decision - App <span class="hidden child-label-meetings_decision_table child-field-caption">(Agenda of Meeting)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Agenda of Meeting', 2 => 'Decision description', 3 => 'Decision actor', 4 => 'Action taken with date', 5 => 'Decision status', 6 => 'Decision status update date', 7 => 'Decision status remarks by superior', 8 => 'Created by', 9 => 'Created at', 10 => 'Last updated by', 11 => 'Last updated at', 12 => 'Meetings decision str', 13 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 1 => 'agenda_lookup', 2 => 'decision_description', 3 => 'decision_actor', 4 => 'action_taken_with_date', 5 => 'decision_status', 6 => 'decision_status_update_date', 7 => 'decision_status_remarks_by_superior', 8 => 'created_by', 9 => 'created_at', 10 => 'last_updated_by', 11 => 'last_updated_at', 12 => 'meetings_decision_str', 13 => 'tenant_id'],
					'sortable-fields' => [0 => '`meetings_decision_table`.`id`', 1 => '`meetings_agenda_table1`.`meetings_agenda_str`', 2 => 3, 3 => 4, 4 => '`meetings_decision_table`.`action_taken_with_date`', 5 => 6, 6 => '`meetings_decision_table`.`decision_status_update_date`', 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => 13, 13 => '`meetings_decision_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-meetings_decision_table',
					'template-printable' => 'children-meetings_decision_table-printable',
					'query' => "SELECT `meetings_decision_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_agenda_table1`.`meetings_agenda_str`), CONCAT_WS('',   `meetings_agenda_table1`.`meetings_agenda_str`), '') as 'agenda_lookup', `meetings_decision_table`.`decision_description` as 'decision_description', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'decision_actor', if(`meetings_decision_table`.`action_taken_with_date`,date_format(`meetings_decision_table`.`action_taken_with_date`,'%d/%m/%Y'),'') as 'action_taken_with_date', `meetings_decision_table`.`decision_status` as 'decision_status', if(`meetings_decision_table`.`decision_status_update_date`,date_format(`meetings_decision_table`.`decision_status_update_date`,'%d/%m/%Y'),'') as 'decision_status_update_date', `meetings_decision_table`.`decision_status_remarks_by_superior` as 'decision_status_remarks_by_superior', `meetings_decision_table`.`created_by` as 'created_by', `meetings_decision_table`.`created_at` as 'created_at', `meetings_decision_table`.`last_updated_by` as 'last_updated_by', `meetings_decision_table`.`last_updated_at` as 'last_updated_at', `meetings_decision_table`.`meetings_decision_str` as 'meetings_decision_str', `meetings_decision_table`.`tenant_id` as 'tenant_id' FROM `meetings_decision_table` LEFT JOIN `meetings_agenda_table` as meetings_agenda_table1 ON `meetings_agenda_table1`.`id`=`meetings_decision_table`.`agenda_lookup` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`meetings_decision_table`.`decision_actor` "
				],
				'decision_actor' => [
					'parent-table' => 'user_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Agenda decision table <span class="hidden child-label-meetings_decision_table child-field-caption">(Decision actor)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Agenda of Meeting', 2 => 'Decision description', 3 => 'Decision actor', 4 => 'Action taken with date', 5 => 'Decision status', 6 => 'Decision status update date', 7 => 'Decision status remarks by superior', 8 => 'Created by', 9 => 'Created at', 10 => 'Last updated by', 11 => 'Last updated at', 12 => 'Meetings decision str', 13 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 1 => 'agenda_lookup', 2 => 'decision_description', 3 => 'decision_actor', 4 => 'action_taken_with_date', 5 => 'decision_status', 6 => 'decision_status_update_date', 7 => 'decision_status_remarks_by_superior', 8 => 'created_by', 9 => 'created_at', 10 => 'last_updated_by', 11 => 'last_updated_at', 12 => 'meetings_decision_str', 13 => 'tenant_id'],
					'sortable-fields' => [0 => '`meetings_decision_table`.`id`', 1 => '`meetings_agenda_table1`.`meetings_agenda_str`', 2 => 3, 3 => 4, 4 => '`meetings_decision_table`.`action_taken_with_date`', 5 => 6, 6 => '`meetings_decision_table`.`decision_status_update_date`', 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => 13, 13 => '`meetings_decision_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-meetings_decision_table',
					'template-printable' => 'children-meetings_decision_table-printable',
					'query' => "SELECT `meetings_decision_table`.`id` as 'id', IF(    CHAR_LENGTH(`meetings_agenda_table1`.`meetings_agenda_str`), CONCAT_WS('',   `meetings_agenda_table1`.`meetings_agenda_str`), '') as 'agenda_lookup', `meetings_decision_table`.`decision_description` as 'decision_description', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'decision_actor', if(`meetings_decision_table`.`action_taken_with_date`,date_format(`meetings_decision_table`.`action_taken_with_date`,'%d/%m/%Y'),'') as 'action_taken_with_date', `meetings_decision_table`.`decision_status` as 'decision_status', if(`meetings_decision_table`.`decision_status_update_date`,date_format(`meetings_decision_table`.`decision_status_update_date`,'%d/%m/%Y'),'') as 'decision_status_update_date', `meetings_decision_table`.`decision_status_remarks_by_superior` as 'decision_status_remarks_by_superior', `meetings_decision_table`.`created_by` as 'created_by', `meetings_decision_table`.`created_at` as 'created_at', `meetings_decision_table`.`last_updated_by` as 'last_updated_by', `meetings_decision_table`.`last_updated_at` as 'last_updated_at', `meetings_decision_table`.`meetings_decision_str` as 'meetings_decision_str', `meetings_decision_table`.`tenant_id` as 'tenant_id' FROM `meetings_decision_table` LEFT JOIN `meetings_agenda_table` as meetings_agenda_table1 ON `meetings_agenda_table1`.`id`=`meetings_decision_table`.`agenda_lookup` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`meetings_decision_table`.`decision_actor` "
				],
			],
			'visiting_card_table' => [
				'given_by' => [
					'parent-table' => 'user_table',
					'parent-primary-key' => 'id',
					'child-primary-key' => 'id',
					'child-primary-key-index' => 0,
					'tab-label' => 'Visiting card table <span class="hidden child-label-visiting_card_table child-field-caption">(Given by)</span>',
					'auto-close' => false,
					'table-icon' => 'table.gif',
					'display-refresh' => true,
					'display-add-new' => true,
					'forced-where' => '',
					'display-fields' => [0 => 'ID', 1 => 'Name', 2 => 'Recommended by', 3 => 'Designation', 4 => 'Company name', 5 => 'Mobile no', 6 => 'Email', 7 => 'Company website address', 8 => 'Given by', 9 => 'Suggested way forward', 10 => 'Front img', 11 => 'Back img', 12 => 'Created by', 13 => 'Created at', 14 => 'Last updated by', 15 => 'Last updated at', 16 => 'Visiting card str', 17 => 'Tenant ID'],
					'display-field-names' => [0 => 'id', 1 => 'name', 2 => 'recommended_by', 3 => 'designation', 4 => 'company_name', 5 => 'mobile_no', 6 => 'email', 7 => 'company_website_addr', 8 => 'given_by', 9 => 'suggested_way_forward', 10 => 'front_img', 11 => 'back_img', 12 => 'created_by', 13 => 'created_at', 14 => 'last_updated_by', 15 => 'last_updated_at', 16 => 'visiting_card_str', 17 => 'tenant_id'],
					'sortable-fields' => [0 => '`visiting_card_table`.`id`', 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10, 10 => 11, 11 => 12, 12 => 13, 13 => 14, 14 => 15, 15 => 16, 16 => 17, 17 => '`visiting_card_table`.`tenant_id`'],
					'records-per-page' => 10,
					'default-sort-by' => 0,
					'default-sort-direction' => 'desc',
					'open-detail-view-on-click' => true,
					'display-page-selector' => true,
					'show-page-progress' => true,
					'template' => 'children-visiting_card_table',
					'template-printable' => 'children-visiting_card_table-printable',
					'query' => "SELECT `visiting_card_table`.`id` as 'id', `visiting_card_table`.`name` as 'name', `visiting_card_table`.`recommended_by` as 'recommended_by', `visiting_card_table`.`designation` as 'designation', `visiting_card_table`.`company_name` as 'company_name', `visiting_card_table`.`mobile_no` as 'mobile_no', `visiting_card_table`.`email` as 'email', `visiting_card_table`.`company_website_addr` as 'company_website_addr', IF(    CHAR_LENGTH(`user_table1`.`memberID`) || CHAR_LENGTH(`user_table1`.`name`), CONCAT_WS('',   `user_table1`.`memberID`, '::', `user_table1`.`name`), '') as 'given_by', `visiting_card_table`.`suggested_way_forward` as 'suggested_way_forward', `visiting_card_table`.`front_img` as 'front_img', `visiting_card_table`.`back_img` as 'back_img', `visiting_card_table`.`created_by` as 'created_by', `visiting_card_table`.`created_at` as 'created_at', `visiting_card_table`.`last_updated_by` as 'last_updated_by', `visiting_card_table`.`last_updated_at` as 'last_updated_at', `visiting_card_table`.`visiting_card_str` as 'visiting_card_str', `visiting_card_table`.`tenant_id` as 'tenant_id' FROM `visiting_card_table` LEFT JOIN `user_table` as user_table1 ON `user_table1`.`id`=`visiting_card_table`.`given_by` "
				],
			],
		];

		if($skipPermissions) return $pcConfig;

		if(!in_array($filterByPermission, ['access', 'insert', 'edit', 'delete'])) $filterByPermission = 'view';

		/**
		* dynamic configuration based on current user's permissions
		* $userPCConfig array is populated only with parent tables where the user has access to
		* at least one child table
		*/
		$userPCConfig = [];
		foreach($pcConfig as $tn => $lookupFields) {
			$perm = getTablePermissions($tn);
			if(!$perm[$filterByPermission]) continue;

			foreach($lookupFields as $fn => $ChildConfig) {
				$permParent = getTablePermissions($ChildConfig['parent-table']);
				if(!$permParent[$filterByPermission]) continue;

				$userPCConfig[$tn][$fn] = $pcConfig[$tn][$fn];
				// show add new only if configured above AND the user has insert permission
				$userPCConfig[$tn][$fn]['display-add-new'] = ($perm['insert'] && $pcConfig[$tn][$fn]['display-add-new']);
			}
		}

		return $userPCConfig;
	}

	#########################################################

	function getChildTables($parentTable, $skipPermissions = false, $filterByPermission = 'view') {
		$pcConfig = getLookupFields($skipPermissions, $filterByPermission);
		$childTables = [];
		foreach($pcConfig as $tn => $lookupFields)
			foreach($lookupFields as $fn => $ChildConfig)
				if($ChildConfig['parent-table'] == $parentTable)
					$childTables[$tn][$fn] = $ChildConfig;

		return $childTables;
	}

	#########################################################

	function isDetailViewEnabled($tn) {
		$tables = ['tenants', 'user_table', 'suggestion', 'event_table', 'event_outcomes_expected_table', 'event_participants_table', 'event_decision_table', 'meetings_table', 'meetings_agenda_table', 'meetings_participants_table', 'meetings_decision_table', 'visiting_card_table', ];
		return in_array($tn, $tables);
	}

	#########################################################

	function appDir($path = '') {
		// if path not empty and doesn't start with a slash, add it
		if($path && $path[0] != '/') $path = '/' . $path;
		return __DIR__ . $path;
	}

	#########################################################

	/**
	 * Inserts a new record in a table, performing various before and after tasks
	 * @param string $tableName the name of the table to insert into
	 * @param array $data associative array of field names and values to insert
	 * @param string $recordOwner the username of the record owner
	 * @param string $errorMessage error message to be set in case of failure
	 * 
	 * @return mixed the ID of the inserted record if successful, false otherwise
	 */
	function tableInsert($tableName, $data, $recordOwner, &$errorMessage = '') {
		global $Translation;

		// mm: can member insert record?
		if(!getTablePermissions($tableName)['insert']) {
			$errorMessage = $Translation['no insert permission'];
			return false;
		}

		$memberInfo = getMemberInfo();

		// check for required fields
		$fields = get_table_fields($tableName);
		$notNullFields = notNullFields($tableName);
		foreach($notNullFields as $fieldName) {
			if($data[$fieldName] !== '') continue;

			$errorMessage = "{$fields[$fieldName]['info']['caption']}: {$Translation['field not null']}";
			return false;
		}

		@include_once(__DIR__ . "/hooks/{$tableName}.php");

		// hook: before_insert
		$beforeInsertFunc = "{$tableName}_before_insert";
		if(function_exists($beforeInsertFunc)) {
			$args = [];
			if(!$beforeInsertFunc($data, $memberInfo, $args)) {
				if(isset($args['error_message'])) $errorMessage = $args['error_message'];
				return false;
			}
		}

		$pkIsAutoInc = pkIsAutoIncrement($tableName);
		$pkField = getPKFieldName($tableName) ?: '';

		$error = '';
		// set empty fields to NULL
		$data = array_map(function($v) { return ($v === '' ? NULL : $v); }, $data);
		insert($tableName, backtick_keys_once($data), $error);
		if($error) {
			$errorMessage = $error;
			return false;
		}

		$recID = $pkIsAutoInc ? db_insert_id() : ($data[$pkField] ?? false);

		update_calc_fields($tableName, $recID, calculated_fields()[$tableName]);

		// hook: after_insert
		$afterInsertFunc = "{$tableName}_after_insert";
		if(function_exists($afterInsertFunc)) {
			if($row = getRecord($tableName, $recID)) {
				$data = array_map('makeSafe', $row);
			}
			$data['selectedID'] = makeSafe($recID);
			$args = [];
			if(!$afterInsertFunc($data, $memberInfo, $args)) { return $recID; }
		}

		// mm: save ownership data
		// record owner is current user
		set_record_owner($tableName, $recID, $recordOwner);

		return $recID;
	}

	#########################################################

	/**
	 * Checks whether the primary key of a table is auto-increment
	 * @param string $tn the name of the table
	 * 
	 * @return bool true if the primary key is auto-increment, false otherwise
	 */
	function pkIsAutoIncrement($tn) {
		// caching
		static $cache = [];

		if(isset($cache[$tn])) return $cache[$tn];

		$pk = getPKFieldName($tn);
		if(!$pk) {
			$cache[$tn] = false;
			return false;
		}

		$isAutoInc = sqlValue("SHOW COLUMNS FROM `$tn` WHERE Field='{$pk}' AND Extra LIKE '%auto_increment%'");
		$cache[$tn] = $isAutoInc ? true : false;
		return $cache[$tn];
	}

	#########################################################

	/**
	 * @return bool true if the current user is an admin and revealing SQL is allowed, false otherwise
	 */
	function showSQL() {
		$allowAdminShowSQL = true;
		return $allowAdminShowSQL && getLoggedAdmin() !== false;
	}

