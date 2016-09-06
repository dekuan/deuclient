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
require_once( dirname( __DIR__ ) . "/src/CUCClientAuthor.php" );
require_once( dirname( __DIR__ ) . "/src/CUCSession.php" );
require_once( dirname( __DIR__ ) . "/vendor/xscn/xslib/src/CLib.php" );

use xscn\xsuclient as ucli;




/**
 * Created by PhpStorm.
 * User: xing
 * Date: 6/22/16
 * Time: 8:31 PM
 */
class CTestUCClientAuthor extends PHPUnit_Framework_TestCase
{
	const CONST_SEED	= '8ccf23adb-8815-46ea-ees1f-198sdcsf380f04/83221234cb-5af5c-1234f-88acd-fff45sdsda2ddd';

	public function testConstVariables()
	{
		$cUCli		= ucli\CUCClient::getInstance();
		$cUCliAuthor	= ucli\CUCClientAuthor::getInstance();




		//
		//	...
		//
		echo "\r\n";
		printf( "CUCClient::CKT\t\t: \"%s\"\r\n", ucli\CUCClient::CKT );
		printf( "CUCClient::CKX\t\t: \"%s\"\r\n", ucli\CUCClient::CKX );
		printf( "CUCClientAuthor::CKT\t: \"%s\"\r\n", ucli\CUCClientAuthor::CKT );
		printf( "CUCClientAuthor::CKX\t: \"%s\"\r\n", ucli\CUCClientAuthor::CKX );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testForConfig()
	{
		$cUCli0	= ucli\CUCClient::getInstance();
		$cUCli	= ucli\CUCClientAuthor::getInstance();

		$arrConfigData	=
			[
				ucli\CUCClientAuthor::CFGKEY_DOMAIN	=> '.xs.cn',
				ucli\CUCClientAuthor::CFGKEY_PATH	=> '/',
				ucli\CUCClientAuthor::CFGKEY_SEED	=> 'my-random-seed-string',	//	seed
				ucli\CUCClientAuthor::CFGKEY_SECURE	=> false,
				ucli\CUCClientAuthor::CFGKEY_HTTPONLY	=> true,
				ucli\CUCClientAuthor::CFGKEY_STIMEOUT	=> 86400,			//	session timeout, default is 1 day.
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
	 * remove the line above, if you debug in xdebug
	 */
	public function testMakeLogin()
	{
		//
		//      make login
		//
		$cUCli0	= ucli\CUCClient::getInstance();
		$cUCli	= ucli\CUCClientAuthor::getInstance();

		//	...
		$nLoginTime	= time();
		$nRefreshTime	= $nLoginTime + 60 * 60 * 24;
		$nUpdateTime	= $nRefreshTime;
		$arrData	= Array
		(
			ucli\CUCClientAuthor::CKX => Array
			(
				ucli\CUCClientAuthor::CKX_UMID		=> '1011301016111816483435812320',
				ucli\CUCClientAuthor::CKX_UNICKNAME	=> '李小龙',
				ucli\CUCClientAuthor::CKX_UTYPE		=> 0,
				ucli\CUCClientAuthor::CKX_UIMGID	=> '159588ac912e08093c37b5064930e6064',
				ucli\CUCClientAuthor::CKX_USTATUS	=> ucli\CUCClientAuthor::USTATUS_OKAY,
				ucli\CUCClientAuthor::CKX_UACT		=> 0,
				ucli\CUCClientAuthor::CKX_SRC		=> 'PCWEB',
			),
			ucli\CUCClientAuthor::CKT => Array
			(
				ucli\CUCClientAuthor::CKT_LOGINTM	=> $nLoginTime,
				ucli\CUCClientAuthor::CKT_REFRESHTM	=> $nRefreshTime,
				ucli\CUCClientAuthor::CKT_UPDATETM	=> $nUpdateTime,
				ucli\CUCClientAuthor::CKT_KPALIVE	=> 1,
				ucli\CUCClientAuthor::CKT_SMID		=> '',
			),
		);
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_SEED, self::CONST_SEED );

		$sUMId		= $arrData[ ucli\CUCClientAuthor::CKX ][ ucli\CUCClientAuthor::CKX_UMID ];
		$sCkString      = '';
		$nErrorId       = $cUCli->MakeLogin( $arrData, true, $sCkString );
		$bSuccess	= ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'MakeLogin', $nErrorId, $bSuccess );
		echo "\t@ try to make login for user [ $sUMId ]: \r\n";
		if ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId )
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
		$_COOKIE[ ucli\CUCClientAuthor::CKX ]	= urldecode( 'mid%253D1011301016111816483435812320%2526nkn%253D%2525R6%25259Q%25258R%2525R5%2525O0%25258S%2525R9%2525OR%252599%2526t%253D0%2526imgid%253D159588np912r08093p37o5064930r6064%2526sts%253D1%2526act%253D0%2526src%253DCPJRO' );
		$_COOKIE[ ucli\CUCClientAuthor::CKT ]	= urldecode( 'v%253D1.0.2.1002%2526ltm%253D1466603202%2526rtm%253D1466689602%2526utm%253D1466689602%2526kpa%253D1%2526smid%253D%2526css%253Dn25n4n9o8900no8n6p6q3292sp95o5rq%2526csc%253D145660380' );

		//var_dump( urldecode( $_COOKIE[ 'T' ] ) );

		//	...
		$cUCli0	= ucli\CUCClient::getInstance();
		$cUCli	= ucli\CUCClientAuthor::getInstance();
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_SEED, self::CONST_SEED );

		//	...
		$nErrorId	= $cUCli->CheckLogin();
		$bIsLoggedIn	= ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId );
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
		$cUCli0	= ucli\CUCClient::getInstance();
		$cUCli	= ucli\CUCClientAuthor::getInstance();

		//
		//	make cookie
		//
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_SEED, self::CONST_SEED );

		//	...
		$sCookieString	= urldecode( 'XAU=mid%253D1011301016111816483435812320%2526nkn%253D%2525R6%25259Q%25258R%2525R5%2525O0%25258S%2525R9%2525OR%252599%2526t%253D0%2526imgid%253D159588np912r08093p37o5064930r6064%2526sts%253D1%2526act%253D0%2526src%253DCPJRO; TAU=v%253D1.0.2.1002%2526ltm%253D1466603202%2526rtm%253D1466689602%2526utm%253D1466689602%2526kpa%253D1%2526smid%253D%2526css%253Dn25n4n9o8900no8n6p6q3292sp95o5rq%2526csc%253D145660380' );

		$nErrorId	= $cUCli->MakeLoginWithCookieString( $sCookieString );
		$bIsLoggedIn	= ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId );
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
		$cUCli0	= ucli\CUCClient::getInstance();
		$cUCli	= ucli\CUCClientAuthor::getInstance();

		//	...
		$nLoginTime	= time();
		$nRefreshTime	= $nLoginTime + 60 * 60 * 24;
		$nUpdateTime	= $nRefreshTime;
		$arrData	= Array
		(
			ucli\CUCClientAuthor::CKX => Array
			(
				ucli\CUCClientAuthor::CKX_UMID		=> '1011301016111816483435812320',
				ucli\CUCClientAuthor::CKX_UNICKNAME	=> '李小龙',
				ucli\CUCClientAuthor::CKX_UTYPE		=> 0,
				ucli\CUCClientAuthor::CKX_UIMGID	=> '159588ac912e08093c37b5064930e6064',
				ucli\CUCClientAuthor::CKX_USTATUS	=> ucli\CUCClientAuthor::USTATUS_OKAY,
				ucli\CUCClientAuthor::CKX_UACT		=> 0,
				ucli\CUCClientAuthor::CKX_SRC		=> 'PCWEB',
			),
			ucli\CUCClientAuthor::CKT => Array
			(
				ucli\CUCClientAuthor::CKT_LOGINTM	=> $nLoginTime,
				ucli\CUCClientAuthor::CKT_REFRESHTM	=> $nRefreshTime,
				ucli\CUCClientAuthor::CKT_UPDATETM	=> $nUpdateTime,
				ucli\CUCClientAuthor::CKT_KPALIVE	=> 1,
				ucli\CUCClientAuthor::CKT_SMID		=> '',
			),
		);
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_DOMAIN, '.xs.cn' );
		$cUCli->SetConfig( ucli\CUCClientAuthor::CFGKEY_SEED, self::CONST_SEED );

		$sUMId		= $arrData[ ucli\CUCClientAuthor::CKX ][ ucli\CUCClientAuthor::CKX_UMID ];
		$sCkString      = '';
		$nErrorId       = $cUCli->MakeLogin( $arrData, true, $sCkString );
		$bSuccess	= ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId );
		$this->_OutputResult( __FUNCTION__, 'MakeLogin', $nErrorId, $bSuccess );
		echo "\t@ try to make login for user [ $sUMId ]: \r\n";
		if ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId )
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
		$bResetCookie	= ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorIdReset );
		$bIsLoggedIn	= ( ucli\CUCClientAuthor::ERR_SUCCESS == $nErrorId );
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
				ucli\CUCClientAuthor::CKX	=>
					[
						ucli\CUCClientAuthor::CKX_UMID,
						ucli\CUCClientAuthor::CKX_UNICKNAME,
						ucli\CUCClientAuthor::CKX_UTYPE,
						ucli\CUCClientAuthor::CKX_UIMGID,
						ucli\CUCClientAuthor::CKX_USTATUS,
						ucli\CUCClientAuthor::CKX_UACT,
						ucli\CUCClientAuthor::CKX_SRC,
					],
				ucli\CUCClientAuthor::CKT	=>
					[
						ucli\CUCClientAuthor::CKT_VER,
						ucli\CUCClientAuthor::CKT_LOGINTM,
						ucli\CUCClientAuthor::CKT_REFRESHTM,
						ucli\CUCClientAuthor::CKT_UPDATETM,
						ucli\CUCClientAuthor::CKT_KPALIVE,
						ucli\CUCClientAuthor::CKT_SMID,
						ucli\CUCClientAuthor::CKT_CSIGN,
						ucli\CUCClientAuthor::CKT_CSRC,
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
