<?php
	$rdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $rdata)));
	$jdata = array_map('to_utf8', array_map('safe_html', array_map('html_attr_tags_ok', $jdata)));
?>
<script>
	$j(function() {
		var tn = 'visiting_card_table';

		/* data for selected record, or defaults if none is selected */
		var data = {
			given_by: <?php echo json_encode(['id' => $rdata['given_by'], 'value' => $rdata['given_by'], 'text' => $jdata['given_by']]); ?>
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for given_by */
		cache.addCheck(function(u, d) {
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'given_by' && d.id == data.given_by.id)
				return { results: [ data.given_by ], more: false, elapsed: 0.01 };
			return false;
		});

		cache.start();
	});
</script>

