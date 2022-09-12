<?php
namespace xnan\MarketBotRunner;

 include_once("settings.php");?>
<html>
<body>
<h1>MarketBotRunner Tester</h1>
<iframe src="index.php?q=run&botArena=mathArena" refresh="" width="1200" height="700">
</iframe>
<script>
	setTimeout(function() {
		location.reload();		
	},<?php echo testerRefreshMillis();?>);
</script>
</body>
</html>