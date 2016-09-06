<?php

require_once( __DIR__ . '/xsuclient/src/CUCClient.php' );
require_once( __DIR__ . '/xsuclient/src/CUCSession.php' );

use xscn\xsuclient as ucli;



testxsuclient_main();


function testxsuclient_main()
{
	$cUCli	= ucli\CUCClient::getInstance();

	//	...
	$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.xs.cn' );
	$cUCli->SetConfig( ucli\CUCClient::CFGKEY_SEED, '5adf23adb-8815-46ea-ees1f-198sdcsf380f04/83221234cb-5af5c-1234f-88acd-12348sdsda2sdf' );
	

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


	$cUCli->Logout();

	echo "Logged out.\r\n";


	echo "</pre>";
}


?>
