<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'meetings_decision_table';

		/* data for selected record, or defaults if none is selected */
		var data = {
			agenda_lookup: <?php echo json_encode(['id' => $rdata['agenda_lookup'], 'value' => $rdata['agenda_lookup'], 'text' => $jdata['agenda_lookup']]); ?>,
			decision_actor: <?php echo json_encode(['id' => $rdata['decision_actor'], 'value' => $rdata['decision_actor'], 'text' => $jdata['decision_actor']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for agenda_lookup */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'agenda_lookup' && d.id == data.agenda_lookup.id)
				return { results: [ data.agenda_lookup ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for decision_actor */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'decision_actor' && d.id == data.decision_actor.id)
				return { results: [ data.decision_actor ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

