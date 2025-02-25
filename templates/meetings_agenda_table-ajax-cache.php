<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'meetings_agenda_table';

		/* data for selected record, or defaults if none is selected */
		var data = {
			meeting_lookup: <?php echo json_encode(['id' => $rdata['meeting_lookup'], 'value' => $rdata['meeting_lookup'], 'text' => $jdata['meeting_lookup']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for meeting_lookup */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'meeting_lookup' && d.id == data.meeting_lookup.id)
				return { results: [ data.meeting_lookup ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

