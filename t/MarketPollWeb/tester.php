<?php
namespace xnan\MarketPollWeb;
 include_once("settings.php"); ?>
<html>
<body>
<h1>Market Poll Tester</h1>
<iframe src="index.php?q=pollQuotes" refresh="" width="1200" height="700">
</iframe>
<script>
	setTimeout(function() {
		location.reload();		
	},<?php echo testerRefreshMillis();?>);
</script>
</body>
</html>