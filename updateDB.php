<?php
	// check this file's MD5 to make sure it wasn't called before
	$tenantId = Authentication::tenantIdPadded();
	$setupHash = __DIR__ . "/setup{$tenantId}.md5";

	$prevMD5 = @file_get_contents($setupHash);
	$thisMD5 = md5_file(__FILE__);

	// check if this setup file already run
	if($thisMD5 != $prevMD5) {
		// set up tables
		setupTable('tenants', []);

		setupTable('user_table', []);

		setupTable('suggestion', [
				"ALTER TABLE `suggestion` ADD `tenant_id_1` INT NULL ",
				"ALTER TABLE `suggestion` DROP `tenant_id_1`",
			]);

		setupTable('event_table', []);

		setupTable('event_outcomes_expected_table', []);
		setupIndexes('event_outcomes_expected_table', ['event_lookup',]);

		setupTable('event_participants_table', [
				"ALTER TABLE `event_participants_table` ADD `tenant_id` INT NULL ",
			]);
		setupIndexes('event_participants_table', ['event_lookup',]);

		setupTable('event_decision_table', [
				"ALTER TABLE `event_decision_table` ADD `tenant_id_1` INT NULL ",
				"ALTER TABLE `event_decision_table` DROP `tenant_id_1`",
			]);
		setupIndexes('event_decision_table', ['outcomes_expected_lookup','decision_actor',]);

		setupTable('meetings_table', [
				"ALTER TABLE `meetings_table` ADD `tenant_id` INT NULL ",
			]);
		setupIndexes('meetings_table', ['visiting_card_lookup','event_lookup',]);

		setupTable('meetings_agenda_table', [
				"ALTER TABLE `meetings_agenda_table` ADD `tenant_id` INT NULL ",
			]);
		setupIndexes('meetings_agenda_table', ['meeting_lookup',]);

		setupTable('meetings_participants_table', [
				"ALTER TABLE `meetings_participants_table` ADD `tenant_id` INT NULL ",
			]);
		setupIndexes('meetings_participants_table', ['meeting_lookup',]);

		setupTable('meetings_decision_table', [
				"ALTER TABLE `meetings_decision_table` ADD `tenant_id` INT NULL ",
			]);
		setupIndexes('meetings_decision_table', ['agenda_lookup','decision_actor',]);

		setupTable('visiting_card_table', [
				"ALTER TABLE `visiting_card_table` ADD `tenant_id` INT NULL ",
			]);
		setupIndexes('visiting_card_table', ['given_by',]);



		// set up internal tables
		setupTable('appgini_query_log', []);
		setupTable('appgini_csv_import_jobs', []);

		// save MD5
		@file_put_contents($setupHash, $thisMD5);
	}


	function setupIndexes($tableName, $arrFields) {
		if(!is_array($arrFields) || !count($arrFields)) return false;

		foreach($arrFields as $fieldName) {
			if(!$res = @db_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")) continue;
			if(!$row = @db_fetch_assoc($res)) continue;
			if($row['Key']) continue;

			@db_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
		}
	}


	function setupTable($tableName, $arrAlter = []) {
		global $Translation;
		$oldTableName = '';

		$createSQL = createTableIfNotExists($tableName, true);
		ob_start();

		echo '<div style="padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;">';

		// is there a table rename query?
		if(!empty($arrAlter)) {
			$matches = [];
			if(preg_match("/ALTER TABLE `(.*)` RENAME `$tableName`/i", $arrAlter[0], $matches)) {
				$oldTableName = $matches[1];
			}
		}

		if($res = @db_query("SELECT COUNT(1) FROM `$tableName`")) { // table already exists
			if($row = @db_fetch_array($res)) {
				echo str_replace(['<TableName>', '<NumRecords>'], [$tableName, $row[0]], $Translation['table exists']);
				if(!empty($arrAlter)) {
					echo '<br>';
					foreach($arrAlter as $alter) {
						if($alter != '') {
							echo "$alter ... ";
							if(!@db_query($alter)) {
								echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
								echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
							} else {
								echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
							}
						}
					}
				} else {
					echo $Translation['table uptodate'];
				}
			} else {
				echo str_replace('<TableName>', $tableName, $Translation['couldnt count']);
			}
		} else { // given tableName doesn't exist

			if($oldTableName != '') { // if we have a table rename query
				if($ro = @db_query("SELECT COUNT(1) FROM `$oldTableName`")) { // if old table exists, rename it.
					$renameQuery = array_shift($arrAlter); // get and remove rename query

					echo "$renameQuery ... ";
					if(!@db_query($renameQuery)) {
						echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
						echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . db_error(db_link()) . '</div>';
					} else {
						echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
					}

					if(!empty($arrAlter)) setupTable($tableName, $arrAlter); // execute Alter queries on renamed table ...
				} else { // if old tableName doesn't exist (nor the new one since we're here), then just create the table.
					setupTable($tableName); // no Alter queries passed ...
				}
			} else { // tableName doesn't exist and no rename, so just create the table
				echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
				if(!@db_query($createSQL)) {
					echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
					echo '<div class="text-danger">' . $Translation['mysql said'] . db_error(db_link()) . '</div>';

					// create table with a dummy field
					@db_query("CREATE TABLE IF NOT EXISTS `$tableName` (`_dummy_deletable_field` TINYINT)");
				} else {
					echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
				}
			}

			// set Admin group permissions for newly created table if membership_grouppermissions exists
			if($ro = @db_query("SELECT COUNT(1) FROM `membership_grouppermissions`")) {
				// get Admins group id
				$ro = @db_query("SELECT `groupID` FROM `membership_groups` WHERE `name`='Admins'");
				if($ro) {
					$adminGroupID = intval(db_fetch_row($ro)[0]);
					if($adminGroupID) @db_query("INSERT IGNORE INTO `membership_grouppermissions` SET
						`groupID`='$adminGroupID',
						`tableName`='$tableName',
						`allowInsert`=1, `allowView`=1, `allowEdit`=1, `allowDelete`=1
					");
				}
			}
		}

		echo '</div>';

		$out = ob_get_clean();
		if(defined('APPGINI_SETUP') && APPGINI_SETUP) echo $out;
	}
