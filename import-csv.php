<?php
	define('PREPEND_PATH', '');
	include_once(__DIR__ . '/lib.php');

	// accept a record as an assoc array, return transformed row ready to insert to table
	$transformFunctions = [
		'tenants' => function($data, $options = []) {

			return $data;
		},
		'user_table' => function($data, $options = []) {

			return $data;
		},
		'suggestion' => function($data, $options = []) {

			return $data;
		},
		'event_table' => function($data, $options = []) {
			if(isset($data['event_from_date'])) $data['event_from_date'] = guessMySQLDateTime($data['event_from_date']);
			if(isset($data['event_to_date'])) $data['event_to_date'] = guessMySQLDateTime($data['event_to_date']);

			return $data;
		},
		'event_outcomes_expected_table' => function($data, $options = []) {
			if(isset($data['event_lookup'])) $data['event_lookup'] = pkGivenLookupText($data['event_lookup'], 'event_outcomes_expected_table', 'event_lookup');

			return $data;
		},
		'event_participants_table' => function($data, $options = []) {
			if(isset($data['event_lookup'])) $data['event_lookup'] = pkGivenLookupText($data['event_lookup'], 'event_participants_table', 'event_lookup');
			if(isset($data['status_date'])) $data['status_date'] = guessMySQLDateTime($data['status_date']);

			return $data;
		},
		'event_decision_table' => function($data, $options = []) {
			if(isset($data['outcomes_expected_lookup'])) $data['outcomes_expected_lookup'] = pkGivenLookupText($data['outcomes_expected_lookup'], 'event_decision_table', 'outcomes_expected_lookup');
			if(isset($data['decision_actor'])) $data['decision_actor'] = pkGivenLookupText($data['decision_actor'], 'event_decision_table', 'decision_actor');
			if(isset($data['action_taken_with_date'])) $data['action_taken_with_date'] = guessMySQLDateTime($data['action_taken_with_date']);
			if(isset($data['decision_status_update_date'])) $data['decision_status_update_date'] = guessMySQLDateTime($data['decision_status_update_date']);

			return $data;
		},
		'meetings_table' => function($data, $options = []) {
			if(isset($data['visiting_card_lookup'])) $data['visiting_card_lookup'] = pkGivenLookupText($data['visiting_card_lookup'], 'meetings_table', 'visiting_card_lookup');
			if(isset($data['event_lookup'])) $data['event_lookup'] = pkGivenLookupText($data['event_lookup'], 'meetings_table', 'event_lookup');
			if(isset($data['meeting_from_date'])) $data['meeting_from_date'] = guessMySQLDateTime($data['meeting_from_date']);
			if(isset($data['meeting_to_date'])) $data['meeting_to_date'] = guessMySQLDateTime($data['meeting_to_date']);

			return $data;
		},
		'meetings_agenda_table' => function($data, $options = []) {
			if(isset($data['meeting_lookup'])) $data['meeting_lookup'] = pkGivenLookupText($data['meeting_lookup'], 'meetings_agenda_table', 'meeting_lookup');

			return $data;
		},
		'meetings_participants_table' => function($data, $options = []) {
			if(isset($data['meeting_lookup'])) $data['meeting_lookup'] = pkGivenLookupText($data['meeting_lookup'], 'meetings_participants_table', 'meeting_lookup');
			if(isset($data['status_date'])) $data['status_date'] = guessMySQLDateTime($data['status_date']);

			return $data;
		},
		'meetings_decision_table' => function($data, $options = []) {
			if(isset($data['agenda_lookup'])) $data['agenda_lookup'] = pkGivenLookupText($data['agenda_lookup'], 'meetings_decision_table', 'agenda_lookup');
			if(isset($data['decision_actor'])) $data['decision_actor'] = pkGivenLookupText($data['decision_actor'], 'meetings_decision_table', 'decision_actor');
			if(isset($data['action_taken_with_date'])) $data['action_taken_with_date'] = guessMySQLDateTime($data['action_taken_with_date']);
			if(isset($data['decision_status_update_date'])) $data['decision_status_update_date'] = guessMySQLDateTime($data['decision_status_update_date']);

			return $data;
		},
		'visiting_card_table' => function($data, $options = []) {
			if(isset($data['given_by'])) $data['given_by'] = pkGivenLookupText($data['given_by'], 'visiting_card_table', 'given_by');

			return $data;
		},
	];

	// accept a record as an assoc array, return a boolean indicating whether to import or skip record
	$filterFunctions = [
		'tenants' => function($data, $options = []) { return true; },
		'user_table' => function($data, $options = []) { return true; },
		'suggestion' => function($data, $options = []) { return true; },
		'event_table' => function($data, $options = []) { return true; },
		'event_outcomes_expected_table' => function($data, $options = []) { return true; },
		'event_participants_table' => function($data, $options = []) { return true; },
		'event_decision_table' => function($data, $options = []) { return true; },
		'meetings_table' => function($data, $options = []) { return true; },
		'meetings_agenda_table' => function($data, $options = []) { return true; },
		'meetings_participants_table' => function($data, $options = []) { return true; },
		'meetings_decision_table' => function($data, $options = []) { return true; },
		'visiting_card_table' => function($data, $options = []) { return true; },
	];

	/*
	Hook file for overwriting/amending $transformFunctions and $filterFunctions:
	hooks/import-csv.php
	If found, it's included below

	The way this works is by either completely overwriting any of the above 2 arrays,
	or, more commonly, overwriting a single function, for example:
		$transformFunctions['tablename'] = function($data, $options = []) {
			// new definition here
			// then you must return transformed data
			return $data;
		};

	Another scenario is transforming a specific field and leaving other fields to the default
	transformation. One possible way of doing this is to store the original transformation function
	in GLOBALS array, calling it inside the custom transformation function, then modifying the
	specific field:
		$GLOBALS['originalTransformationFunction'] = $transformFunctions['tablename'];
		$transformFunctions['tablename'] = function($data, $options = []) {
			$data = call_user_func_array($GLOBALS['originalTransformationFunction'], [$data, $options]);
			$data['fieldname'] = 'transformed value';
			return $data;
		};
	*/

	@include(__DIR__ . '/hooks/import-csv.php');

	$ui = new CSVImportUI($transformFunctions, $filterFunctions);
