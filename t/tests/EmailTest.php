<?php
declare(strict_types=1);

/*use xnan\Trurl;
use xnan\Trurl\Market;
use xnan\Trurl\MarketSimulator;
use xnan\Trurl\Asset;
use xnan\Trurl\MarketPoll;*/
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Email;

chdir( __DIR__ );
require '../vendor/autoload.php';
//require("../autoloader.php");
//include_once("../settings.php");


final class EmailTest extends Framework\TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            Framework\Email::class,
            Framework\Email::fromString('user@example.com')
        );
    }

    public function testCannotBeCreatedFromInvalidEmailAddress(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FrameWork\Email::fromString('invalid');
    }

    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            Framework\Email::fromString('user@example.com')
        );
    }
}


//include_once("common.php");

/*
Trurl\Functions::Load;

function testMarketBotRunnerQuery($query) {
	$domain=Trurl\domain();
	$arr=Trurl\callServiceMarketBotRunnerArray($query);
	if (!$arr) echo "test: testMarketBotRunnerQuery FAIL: query: $query msg: does not return csv convertible to array";
	echo "test: testMarketBotRunnerQuery OK: query: $query";
}

function test() {
	testMarketBotRunnerQuery("q=botArenasAsCsv&live=true");
}

test();
*/

?>