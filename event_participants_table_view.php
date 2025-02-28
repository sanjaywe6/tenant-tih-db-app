<?php
// This script and data application was generated by AppGini, https://bigprof.com/appgini
// Download AppGini for free from https://bigprof.com/appgini/download/

	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/event_participants_table.php');
	include_once(__DIR__ . '/event_participants_table_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('event_participants_table');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'event_participants_table';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`event_participants_table`.`id`" => "id",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event */" => "event_lookup",
		"`event_participants_table`.`name`" => "name",
		"`event_participants_table`.`designation`" => "designation",
		"`event_participants_table`.`participant_type`" => "participant_type",
		"`event_participants_table`.`accepted_status`" => "accepted_status",
		"if(`event_participants_table`.`status_date`,date_format(`event_participants_table`.`status_date`,'%d/%m/%Y'),'')" => "status_date",
		"`event_participants_table`.`created_by`" => "created_by",
		"`event_participants_table`.`created_at`" => "created_at",
		"`event_participants_table`.`last_updated_by`" => "last_updated_by",
		"`event_participants_table`.`last_updated_at`" => "last_updated_at",
		"`event_participants_table`.`event_participants_str`" => "event_participants_str",
		"`event_participants_table`.`tenant_id`" => "tenant_id",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`event_participants_table`.`id`',
		2 => '`event_table1`.`event_str`',
		3 => 3,
		4 => 4,
		5 => 5,
		6 => 6,
		7 => '`event_participants_table`.`status_date`',
		8 => 8,
		9 => 9,
		10 => 10,
		11 => 11,
		12 => 12,
		13 => '`event_participants_table`.`tenant_id`',
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`event_participants_table`.`id`" => "id",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event */" => "event_lookup",
		"`event_participants_table`.`name`" => "name",
		"`event_participants_table`.`designation`" => "designation",
		"`event_participants_table`.`participant_type`" => "participant_type",
		"`event_participants_table`.`accepted_status`" => "accepted_status",
		"if(`event_participants_table`.`status_date`,date_format(`event_participants_table`.`status_date`,'%d/%m/%Y'),'')" => "status_date",
		"`event_participants_table`.`created_by`" => "created_by",
		"`event_participants_table`.`created_at`" => "created_at",
		"`event_participants_table`.`last_updated_by`" => "last_updated_by",
		"`event_participants_table`.`last_updated_at`" => "last_updated_at",
		"`event_participants_table`.`event_participants_str`" => "event_participants_str",
		"`event_participants_table`.`tenant_id`" => "tenant_id",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`event_participants_table`.`id`" => "ID",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event */" => "Event",
		"`event_participants_table`.`name`" => "Name",
		"`event_participants_table`.`designation`" => "Designation",
		"`event_participants_table`.`participant_type`" => "Participant type",
		"`event_participants_table`.`accepted_status`" => "Accepted status",
		"`event_participants_table`.`status_date`" => "Status date",
		"`event_participants_table`.`created_by`" => "Created by",
		"`event_participants_table`.`created_at`" => "Created at",
		"`event_participants_table`.`last_updated_by`" => "Last updated by",
		"`event_participants_table`.`last_updated_at`" => "Last updated at",
		"`event_participants_table`.`event_participants_str`" => "Event participants str",
		"`event_participants_table`.`tenant_id`" => "Tenant ID",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`event_participants_table`.`id`" => "id",
		"IF(    CHAR_LENGTH(`event_table1`.`event_str`), CONCAT_WS('',   `event_table1`.`event_str`), '') /* Event */" => "event_lookup",
		"`event_participants_table`.`name`" => "name",
		"`event_participants_table`.`designation`" => "designation",
		"`event_participants_table`.`participant_type`" => "participant_type",
		"`event_participants_table`.`accepted_status`" => "accepted_status",
		"if(`event_participants_table`.`status_date`,date_format(`event_participants_table`.`status_date`,'%d/%m/%Y'),'')" => "status_date",
		"`event_participants_table`.`created_by`" => "created_by",
		"`event_participants_table`.`created_at`" => "created_at",
		"`event_participants_table`.`last_updated_by`" => "last_updated_by",
		"`event_participants_table`.`last_updated_at`" => "last_updated_at",
		"`event_participants_table`.`event_participants_str`" => "event_participants_str",
		"`event_participants_table`.`tenant_id`" => "tenant_id",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = ['event_lookup' => 'Event', ];

	$x->QueryFrom = "`event_participants_table` LEFT JOIN `event_table` as event_table1 ON `event_table1`.`id`=`event_participants_table`.`event_lookup` ";
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
	$x->ScriptFileName = 'event_participants_table_view.php';
	$x->TableTitle = 'Participants / Speaker / VIP List - App';
	$x->TableIcon = 'table.gif';
	$x->PrimaryKey = '`event_participants_table`.`id`';
	$x->DefaultSortField = '1';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth = [150, 150, 150, 150, 150, 150, 150, 150, 150, 150, 150, 150, 150, ];
	$x->ColCaption = ['ID', 'Event', 'Name', 'Designation', 'Participant type', 'Accepted status', 'Status date', 'Created by', 'Created at', 'Last updated by', 'Last updated at', 'Event participants str', 'Tenant ID', ];
	$x->ColFieldName = ['id', 'event_lookup', 'name', 'designation', 'participant_type', 'accepted_status', 'status_date', 'created_by', 'created_at', 'last_updated_by', 'last_updated_at', 'event_participants_str', 'tenant_id', ];
	$x->ColNumber  = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/event_participants_table_templateTV.html';
	$x->SelectedTemplate = 'templates/event_participants_table_templateTVS.html';
	$x->TemplateDV = 'templates/event_participants_table_templateDV.html';
	$x->TemplateDVP = 'templates/event_participants_table_templateDVP.html';

	$x->ShowTableHeader = 0;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = true;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: event_participants_table_init
	$render = true;
	if(function_exists('event_participants_table_init')) {
		$args = [];
		$render = event_participants_table_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: event_participants_table_header
	$headerCode = '';
	if(function_exists('event_participants_table_header')) {
		$args = [];
		$headerCode = event_participants_table_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: event_participants_table_footer
	$footerCode = '';
	if(function_exists('event_participants_table_footer')) {
		$args = [];
		$footerCode = event_participants_table_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
