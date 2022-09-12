<?php
namespace xnan\MarketPollWeb;

echo "ok<br>";
printf("path del script:%s<br>",realpath(dirname(__FILE__)));

printf("existe local php:%s<br>\n",file_exists("/usr/local/bin/php") ? "si" : "no");
printf("existe bin php:%s<br>\n",file_exists("/usr/bin/php") ? "si" : "no");
printf("existe MarketPollWeb:%s<br>\n",file_exists("home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb") ? "si" : "no");
printf("existe MarketPollWeb/inex.php:%s<br>\n",file_exists("home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php") ? "si" : "no");

printf("existe /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb:%s<br>\n",file_exists("home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb") ? "si" : "no");

printf("existe /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php:%s<br>\n",file_exists("home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php") ? "si" : "no");

printf("existe /home/xnanclic:%s<br>\n",file_exists("/home/xnanclic") ? "si" : "no");
printf("existe /home/xnanclic/public_html:%s<br>\n",file_exists("/home/xnanclic/public_html") ? "si" : "no");
printf("existe /home/xnanclic/public_html/prod:%s<br>\n",file_exists("/home/xnanclic/public_html/prod") ? "si" : "no");
printf("existe /home/xnanclic/public_html/prod/trurl:%s<br>\n",file_exists("/home/xnanclic/public_html/prod/trurl") ? "si" : "no");
printf("existe /home/xnanclic/public_html/prod/trurl/p0:%s<br>\n",file_exists("/home/xnanclic/public_html/prod/trurl/p0") ? "si" : "no");
printf("existe /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb:%s<br>\n",file_exists("/home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb") ? "si" : "no");
printf("existe /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php:%s<br>\n",file_exists("/home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php") ? "si" : "no");

printf("existe /usr/local/bin/php-cgi:%s<br>\n",file_exists("/usr/local/bin/php-cgi") ? "si" : "no");

printf("existe /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/runFromCron.php:%s<br>\n",file_exists("/home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/runFromCron.php") ? "si" : "no");


//	/usr/local/bin/php /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php > marketQuotes.log
//  /usr/local/bin/php /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/index.php >> marketQuotes1.log 2>&1

phpinfo();
?>