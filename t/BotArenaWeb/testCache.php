<?php

echo "new:".session_cache_expire(24*60*60);
echo "new:".session_cache_expire();

session_cache_limiter('nocache');
$cache_limiter = session_cache_limiter();

echo "The cache limiter is now set to $cache_limiter<br />";
?>