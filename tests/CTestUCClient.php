<?php

@ ini_set( 'date.timezone', 'Etc/GMT＋0' );
@ date_default_timezone_set( 'Etc/GMT＋0' );

@ ini_set( 'display_errors',	'on' );
@ ini_set( 'max_execution_time',	'60' );
@ ini_set( 'max_input_time',	'0' );
@ ini_set( 'memory_limit',	'512M' );

//	mb 环境定义
mb_internal_encoding( "UTF-8" );

//	Turn on output buffering
ob_start();



require_once( dirname( __DIR__ ) . "/vendor/autoload.php" );
require_once( dirname( __DIR__ ) . "/src/CUCClient.php" );
require_once( dirname( __DIR__ ) . "/src/CUCSession.php" );
require_once( dirname( __DIR__ ) . "/vendor/dekuan/delib/src/CLib.php" );

use dekuan\deuclient as ucli;




/**
 *	class of testCheckLoginWithString
 */
class CTestUCClient extends PHPUnit_Framework_TestCase
{
	const CONST_SEED	= '5adf23adb-8815-46ea-ees1f-198sdcsf380f04/dfdfdfdfdf-5af5c-1234f-88acd-1121134234343434343';


	/**
	 * @runInSeparateProcess
	 */
	public function testHeader()
	{
		print( "\r\n" . __CLASS__ . "::" . __FUNCTION__ . "\r\n" );
		print( "--------------------------------------------------------------------------------\r\n" );

		return true;
	}

	public function testConstVariables()
	{
		//
		//	...
		//
		echo "\r\n";
		printf( "CUCClient::CKT\t\t: \"%s\"\r\n", ucli\CUCClient::CKT );
		printf( "CUCClient::CKX\t\t: \"%s\"\r\n", ucli\CUCClient::CKX );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testForConfig()
	{
		$cUCli	= ucli\CUCClient::getInstance();

		$arrConfigData	=
		[
			ucli\CUCClient::CFGKEY_DOMAIN	=> '.xs.cn',
			ucli\CUCClient::CFGKEY_PATH	=> '/',
			ucli\CUCClient::CFGKEY_SEED	=> 'my-random-seed-string',	//	seed
			ucli\CUCClient::CFGKEY_SECURE	=> false,
			ucli\CUCClient::CFGKEY_HTTPONLY	=> true,
			ucli\CUCClient::CFGKEY_STIMEOUT	=> 86400,			//	session timeout, default is 1 day.
		];

		foreach ( $arrConfigData as $sKey => $vValue )
		{
			$bSuccess	= false;
			$cUCli->SetConfig( $sKey, $vValue );
			if ( is_string( $vValue ) )
			{
				$bSuccess = ( 0 == strcmp( $vValue, $cUCli->GetConfig( $sKey ) ) );
			}
			else if ( is_bool( $vValue ) || is_numeric( $vValue ) )
			{
				$bSuccess = ( $vValue == $cUCli->GetConfig( $sKey ) );
			}
			else
			{
				$bSuccess = ( $vValue == $cUCli->GetConfig( $sKey ) );
			}
			$nErrorId	= ( $bSuccess ? 0 : -1 );
			$this->_OutputResult( __FUNCTION__, "SetConfig['$sKey']", $nErrorId, $bSuccess );
		}
	}


	/**
	 * @runInSeparateProcess
	 */
	public function testMakeLogin()
	{
		//
		//      make login
		//
		$cUCli	= ucli\CUCClient::getInstance();

		//	...
		$nLoginTime	= time();
		$nRefreshTime	= $nLoginTime + 60 * 60 * 24;
		$nUpdateTime	= $nRefreshTime;
		$arrData	= Array
		(
			ucli\CUCClient::CKX => Array
			(
				ucli\CUCClient::CKX_UMID	=> '1011301016111816483435812320',
				ucli\CUCClient::CKX_UNICKNAME	=> '李小龙',
				ucli\CUCClient::CKX_UTYPE	=> 0,
				ucli\CUCClient::CKX_UIMGID	=> '159588ac912e08093c37b5064930e6064',
				ucli\CUCClient::CKX_USTATUS	=> ucli\CUCClient::USTATUS_OKAY,
				ucli\CUCClient::CKX_UACT	=> 0,
				ucli\CUCClient::CKX_SRC		=> 'PCWEB',
			),
			ucli\CUCClient::CKT => Array
			(
				ucli\CUCClient::CKT_LOGINTM	=> $nLoginTime,
				ucli\CUCClient::CKT_REFRESHTM	=> $nRefreshTime,
				ucli\CUCClient::CKT_UPDATETM	=> $nUpdateTime,
				ucli\CUCClient::CKT_KPALIVE	=> 1,
				ucli\CUCClient::CKT_SMID	=> '',
			),
		);
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_SEED, self::CONST_SEED );

		$sUMId		= $arrData[ ucli\CUCClient::CKX ][ ucli\CUCClient::CKX_UMID ];
		$sCkString      = '';
		$nErrorId       = $cUCli->MakeLogin( $arrData, true, $sCkString );
		$bSuccess	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'MakeLogin', $nErrorId, $bSuccess );
		echo "\t@ try to make login for user [ $sUMId ]: \r\n";
		if ( ucli\CUCClient::ERR_SUCCESS == $nErrorId )
		{
			echo "\t- successfully.\r\n";
			echo "\t- Cookie string: " . $sCkString . "\r\n";
		}
		else
		{
			echo "\t- failed. error id=" . $nErrorId . "\r\n";
		}
		echo "\r\n";

		//	...
		return true;
	}


	/**
	 * @runInSeparateProcess
	 */
	public function testCheckLogin()
	{
		//
		//	make cookie
		//
		global $_COOKIE;

		if ( ! is_array( $_COOKIE ) )
		{
			$_COOKIE = [];
		}
		$_COOKIE[ ucli\CUCClient::CKX ]	= urldecode( 'mid%253D1011301016111816483435812320%2526nkn%253D%2525R6%25259Q%25258R%2525R5%2525O0%25258S%2525R9%2525OR%252599%2526t%253D0%2526imgid%253D159588np912r08093p37o5064930r6064%2526sts%253D1%2526act%253D0%2526src%253DCPJRO' );
		$_COOKIE[ ucli\CUCClient::CKT ]	= urldecode( 'v%253D1.0.2.1002%2526ltm%253D1466601791%2526rtm%253D1466688191%2526utm%253D1466688191%2526kpa%253D1%2526smid%253D%2526css%253D63pns46482qo51p7615nr1n479rop7o3%2526csc%253D2428648322' );

		//var_dump( urldecode( $_COOKIE[ 'T' ] ) );

		//	...
		$cUCli	= ucli\CUCClient::getInstance();
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_SEED, self::CONST_SEED );

		//	...
		$nErrorId	= $cUCli->CheckLogin();
		$bIsLoggedIn	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'CheckLogin', $nErrorId, $bIsLoggedIn );
		echo "\t@ Check login via cookie: " . ( $bIsLoggedIn ? "successfully" : "failed" ) . "\r\n";
		echo "\r\n";

		//	...
		return true;
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testMakeLoginWithCookieString()
	{
		//
		//      make login
		//
		$cUCli	= ucli\CUCClient::getInstance();

		//
		//	make cookie
		//
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_SEED, self::CONST_SEED );

		//	...
		$sCookieString	= urldecode( 'X=mid%253D1011301016111816483435812320%2526nkn%253D%2525R6%25259Q%25258R%2525R5%2525O0%25258S%2525R9%2525OR%252599%2526t%253D0%2526imgid%253D159588np912r08093p37o5064930r6064%2526sts%253D1%2526act%253D0%2526src%253DCPJRO; T=v%253D1.0.2.1002%2526ltm%253D1466601791%2526rtm%253D1466688191%2526utm%253D1466688191%2526kpa%253D1%2526smid%253D%2526css%253D63pns46482qo51p7615nr1n479rop7o3%2526csc%253D2428648322' );

		$nErrorId	= $cUCli->MakeLoginWithCookieString( $sCookieString );
		$bIsLoggedIn	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'MakeLoginWithCookieString', $nErrorId, $bIsLoggedIn );
		echo "\t@ Make login via cookie string: " . ( $bIsLoggedIn ? "successfully" : "failed" ) . "\r\n";
		echo "\r\n";

		//	...
		return true;
	}



	/**
	 * @runInSeparateProcess
	 */
	public function testMakeLoginAndCheckLoginWithString()
	{

		//
		//      make login
		//
		$cUCli	= ucli\CUCClient::getInstance();

		//	...
		$nLoginTime	= time();
		$nRefreshTime	= $nLoginTime + 60 * 60 * 24;
		$nUpdateTime	= $nRefreshTime;
		$arrData	= Array
		(
			ucli\CUCClient::CKX => Array
			(
				ucli\CUCClient::CKX_UMID	=> '1011301016111816483435812320',
				ucli\CUCClient::CKX_UNICKNAME	=> '李小龙',
				ucli\CUCClient::CKX_UTYPE	=> 0,
				ucli\CUCClient::CKX_UIMGID	=> '159588ac912e08093c37b5064930e6064',
				ucli\CUCClient::CKX_USTATUS	=> ucli\CUCClient::USTATUS_OKAY,
				ucli\CUCClient::CKX_UACT	=> 0,
				ucli\CUCClient::CKX_SRC		=> 'PCWEB',
			),
			ucli\CUCClient::CKT => Array
			(
				ucli\CUCClient::CKT_LOGINTM	=> $nLoginTime,
				ucli\CUCClient::CKT_REFRESHTM	=> $nRefreshTime,
				ucli\CUCClient::CKT_UPDATETM	=> $nUpdateTime,
				ucli\CUCClient::CKT_KPALIVE	=> 1,
				ucli\CUCClient::CKT_SMID	=> '',
			),
		);
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClient::CFGKEY_SEED, self::CONST_SEED );

		$sUMId		= $arrData[ ucli\CUCClient::CKX ][ ucli\CUCClient::CKX_UMID ];
		$sCkString      = '';
		$nErrorId       = $cUCli->MakeLogin( $arrData, true, $sCkString );
		$bSuccess	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'MakeLogin', $nErrorId, $bSuccess );
		echo "\t@ try to make login for user [ $sUMId ]: \r\n";
		if ( ucli\CUCClient::ERR_SUCCESS == $nErrorId )
		{
			echo "\t- successfully.\r\n";
			echo "\t- Cookie string: " . $sCkString . "\r\n";
		}
		else
		{
			echo "\t- failed. error id=" . $nErrorId . "\r\n";
		}
		echo "\r\n";


		//
		//      ...
		//
		$nErrorIdReset	= $cUCli->ResetCookie( $sCkString );
		$nErrorId	= $cUCli->CheckLogin();
		$bResetCookie	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorIdReset );
		$bIsLoggedIn	= ( ucli\CUCClient::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'ResetCookie', $nErrorIdReset, $bResetCookie );
		$this->_OutputResult( __FUNCTION__, 'CheckLogin', $nErrorId, $bIsLoggedIn );
		echo "\t@ Reset cookie via cookie string: " . ( $bResetCookie ? "successfully" : "failed" ) . "\r\n";
		echo "\t@ Check login via cookie string: " . ( $bIsLoggedIn ? "successfully" : "failed" ) . "\r\n";
		echo "\t->IsKeepAlive() = " . $cUCli->IsKeepAlive() . "\r\n";
		echo "\t->IsExistsXT() = " . $cUCli->IsExistsXT() . "\r\n";
		echo "\t->GetCookieString() = " . $cUCli->GetCookieString() . "\r\n";
		echo "\t->GetOriginalXTArray() = ";
		print_r( $cUCli->GetOriginalXTArray() );
		echo "\r\n";
		echo "\t->GetXTArray() = ";
		print_r( $cUCli->GetXTArray() );
		echo "\r\n";

		//
		//	GetXTValue
		//
		$arrXTKeyMap =
		[
			ucli\CUCClient::CKX	=>
			[
				ucli\CUCClient::CKX_UMID,
				ucli\CUCClient::CKX_UNICKNAME,
				ucli\CUCClient::CKX_UTYPE,
				ucli\CUCClient::CKX_UIMGID,
				ucli\CUCClient::CKX_USTATUS,
				ucli\CUCClient::CKX_UACT,
				ucli\CUCClient::CKX_SRC,
			],
			ucli\CUCClient::CKT	=>
			[
				ucli\CUCClient::CKT_VER,
				ucli\CUCClient::CKT_LOGINTM,
				ucli\CUCClient::CKT_REFRESHTM,
				ucli\CUCClient::CKT_UPDATETM,
				ucli\CUCClient::CKT_KPALIVE,
				ucli\CUCClient::CKT_SMID,
				ucli\CUCClient::CKT_CSIGN,
				ucli\CUCClient::CKT_CSRC,
			],
		];
		foreach ( $arrXTKeyMap as $sType => $arrKeyList )
		{
			foreach ( $arrKeyList as $sKey )
			{
				echo "\t->GetXTValue( '$sType', '$sKey' ) = " . $cUCli->GetXTValue( $sType, $sKey ) . "\r\n";
			}
		}

		echo "\r\n";

		//	...
		return true;
	}

	protected function _OutputResult( $sFuncName, $sCallMethod, $nErrorId, $bAssert )
	{
		printf( "\r\n# %s->%s\r\n", $sFuncName, $sCallMethod );
		printf( "# ErrorId : %6d, result : [%s]", $nErrorId, ( $bAssert ? "OK" : "ERROR" ) );
		printf( "\r\n" );

		$this->assertTrue( $bAssert );
	}
}


?>