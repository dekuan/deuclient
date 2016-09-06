<?php

require_once( __DIR__ . '/deuclient/src/CUCClient.php' );
require_once( __DIR__ . '/deuclient/src/CUCSession.php' );

use dekuan\deuclient as ucli;



testdeuclient_main();

function testdeuclient_main()
{
	//
	//      make login
	//
	$cUCli	= ucli\CUCClient::getInstance();
	$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.ladep.cn' );
	$cUCli->SetConfig( ucli\CUCClient::CFGKEY_SEED, 'sdfdfdcsf380f04/83221234cb-5af5c-1234f-88acd-12348sdsda2sdf' );



	echo "<pre>";

	if ( ucli\CUCClient::ERR_SUCCESS == $cUCli->CheckLogin() )
	{
		echo "CheckLogin successfully.\r\n";
		print_r( $cUCli->GetXTArray() );
	}
	else
	{
		echo "Failed to CheckLogin.\r\n";
	}


	//
	//	make cookie
	//
	$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.xs.cn' );

	//	...
	$sCookieString	= urldecode( 'X=mid%253D1011301016111816483435812320%2526nkn%253D%2525R6%25259Q%25258R%2525R5%2525O0%25258S%2525R9%2525OR%252599%2526t%253D0%2526imgid%253D159588np912r08093p37o5064930r6064%2526sts%253D1%2526act%253D0%2526src%253DCPJRO; T=v%253D1.0.1.1001%2526ltm%253D1454051458%2526rtm%253D1454137858%2526utm%253D1454137858%2526kpa%253D1%2526smid%253D%2526css%253D994544n4p3oq6r4926o014r48r9sps39%2526csc%253D2135553192' );
	$nErrorId	= $cUCli->MakeLoginWithCookieString( $sCookieString );
	$bIsLoggedIn	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorId );
	testdeuclient_output( __FUNCTION__, 'MakeLoginWithCookieString', $nErrorId, $bIsLoggedIn );
	echo "\t@ Make login via cookie string: " . ( $bIsLoggedIn ? "successfully" : "failed" ) . "\r\n";


	echo "</pre>";


	//	...
	return true;

}
function testdeuclient_output( $sFuncName, $sCallMethod, $nErrorId, $bAssert )
{
	printf( "\r\n# %s->%s\r\n", $sFuncName, $sCallMethod );
	printf( "# ErrorId : %6d, result : [%s]", $nErrorId, ( $bAssert ? "OK" : "ERROR" ) );
	printf( "\r\n" );
}




?>
