<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'event_outcomes_expected_table';

		/* data for selected record, or defaults if none is selected */
		var data = {
			event_lookup: <?php echo json_encode(['id' => $rdata['event_lookup'], 'value' => $rdata['event_lookup'], 'text' => $jdata['event_lookup']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for event_lookup */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'event_lookup' && d.id == data.event_lookup.id)
				return { results: [ data.event_lookup ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

