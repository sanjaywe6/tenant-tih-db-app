<?php
// This script and data application was generated by AppGini, https://bigprof.com/appgini
// Download AppGini for free from https://bigprof.com/appgini/download/

	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/event_outcomes_expected_table.php');
	include_once(__DIR__ . '/event_outcomes_expected_table_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('event_outcomes_expected_table');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'event_outcomes_expected_table';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`event_outcomes_expected_table`.`id`" => "id",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event Details */" => "event_lookup",
		"`event_outcomes_expected_table`.`target_audience`" => "target_audience",
		"`event_outcomes_expected_table`.`expected_outcomes`" => "expected_outcomes",
		"`event_outcomes_expected_table`.`created_by`" => "created_by",
		"`event_outcomes_expected_table`.`created_at`" => "created_at",
		"`event_outcomes_expected_table`.`last_updated_by`" => "last_updated_by",
		"`event_outcomes_expected_table`.`last_updated_at`" => "last_updated_at",
		"`event_outcomes_expected_table`.`tenant_id`" => "tenant_id",
		"`event_outcomes_expected_table`.`outcomes_expected_str`" => "outcomes_expected_str",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`event_outcomes_expected_table`.`id`',
		2 => '`event_table1`.`event_str`',
		3 => 3,
		4 => 4,
		5 => 5,
		6 => 6,
		7 => 7,
		8 => 8,
		9 => '`event_outcomes_expected_table`.`tenant_id`',
		10 => 10,
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`event_outcomes_expected_table`.`id`" => "id",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event Details */" => "event_lookup",
		"`event_outcomes_expected_table`.`target_audience`" => "target_audience",
		"`event_outcomes_expected_table`.`expected_outcomes`" => "expected_outcomes",
		"`event_outcomes_expected_table`.`created_by`" => "created_by",
		"`event_outcomes_expected_table`.`created_at`" => "created_at",
		"`event_outcomes_expected_table`.`last_updated_by`" => "last_updated_by",
		"`event_outcomes_expected_table`.`last_updated_at`" => "last_updated_at",
		"`event_outcomes_expected_table`.`tenant_id`" => "tenant_id",
		"`event_outcomes_expected_table`.`outcomes_expected_str`" => "outcomes_expected_str",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`event_outcomes_expected_table`.`id`" => "ID",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event Details */" => "Event Details",
		"`event_outcomes_expected_table`.`target_audience`" => "Target audience",
		"`event_outcomes_expected_table`.`expected_outcomes`" => "Expected outcomes",
		"`event_outcomes_expected_table`.`created_by`" => "Created by",
		"`event_outcomes_expected_table`.`created_at`" => "Created at",
		"`event_outcomes_expected_table`.`last_updated_by`" => "Last updated by",
		"`event_outcomes_expected_table`.`last_updated_at`" => "Last updated at",
		"`event_outcomes_expected_table`.`tenant_id`" => "Tenant ID",
		"`event_outcomes_expected_table`.`outcomes_expected_str`" => "Outcomes expected str",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`event_outcomes_expected_table`.`id`" => "id",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event Details */" => "event_lookup",
		"`event_outcomes_expected_table`.`target_audience`" => "target_audience",
		"`event_outcomes_expected_table`.`expected_outcomes`" => "expected_outcomes",
		"`event_outcomes_expected_table`.`created_by`" => "created_by",
		"`event_outcomes_expected_table`.`created_at`" => "created_at",
		"`event_outcomes_expected_table`.`last_updated_by`" => "last_updated_by",
		"`event_outcomes_expected_table`.`last_updated_at`" => "last_updated_at",
		"`event_outcomes_expected_table`.`tenant_id`" => "tenant_id",
		"`event_outcomes_expected_table`.`outcomes_expected_str`" => "outcomes_expected_str",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = ['event_lookup' => 'Event Details', ];

	$x->QueryFrom = "`event_outcomes_expected_table` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`event_outcomes_expected_table`.`event_lookup` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm['view'] == 0 ? 1 : 0);
	$x->AllowDelete = $perm['delete'];
	$x->AllowMassDelete = (getLoggedAdmin() !== false);
	$x->AllowInsert = $perm['insert'];
	$x->AllowUpdate = $perm['edit'];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = (getLoggedAdmin() !== false);
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowPrintingDV = 1;
	$x->AllowCSV = 1;
	$x->AllowAdminShowSQL = showSQL();
	$x->RecordsPerPage = 100;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation['quick search'];
	$x->ScriptFileName = 'event_outcomes_expected_table_view.php';
	$x->TableTitle = 'Outcomes Expected Table';
	$x->TableIcon = 'table.gif';
	$x->PrimaryKey = '`event_outcomes_expected_table`.`id`';
	$x->DefaultSortField = '1';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth = [150, 150, 150, 150, 150, 150, 150, 150, 150, ];
	$x->ColCaption = ['ID', 'Event Details', 'Target audience', 'Expected outcomes', 'Created by', 'Created at', 'Last updated by', 'Last updated at', 'Outcomes expected str', ];
	$x->ColFieldName = ['id', 'event_lookup', 'target_audience', 'expected_outcomes', 'created_by', 'created_at', 'last_updated_by', 'last_updated_at', 'outcomes_expected_str', ];
	$x->ColNumber  = [1, 2, 3, 4, 5, 6, 7, 8, 10, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/event_outcomes_expected_table_templateTV.html';
	$x->SelectedTemplate = 'templates/event_outcomes_expected_table_templateTVS.html';
	$x->TemplateDV = 'templates/event_outcomes_expected_table_templateDV.html';
	$x->TemplateDVP = 'templates/event_outcomes_expected_table_templateDVP.html';

	$x->ShowTableHeader = 0;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = true;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: event_outcomes_expected_table_init
	$render = true;
	if(function_exists('event_outcomes_expected_table_init')) {
		$args = [];
		$render = event_outcomes_expected_table_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: event_outcomes_expected_table_header
	$headerCode = '';
	if(function_exists('event_outcomes_expected_table_header')) {
		$args = [];
		$headerCode = event_outcomes_expected_table_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: event_outcomes_expected_table_footer
	$footerCode = '';
	if(function_exists('event_outcomes_expected_table_footer')) {
		$args = [];
		$footerCode = event_outcomes_expected_table_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
