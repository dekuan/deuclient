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
require_once( dirname( __DIR__ ) . "/src/CUCClientDCal.php" );
require_once( dirname( __DIR__ ) . "/src/CUCSession.php" );
require_once( dirname( __DIR__ ) . "/vendor/dekuan/delib/src/CLib.php" );

use dekuan\deuclient\CUCClientDCal;



/**
 * Created by PhpStorm.
 * User: liuqixing
 * Date: 9/11/16
 * Time: 10:27 AM
 */
class CTestUCClientDCal extends PHPUnit_Framework_TestCase
{
	public function testForIsLogin()
	{
		$cCUCClientDCal	= new CUCClientDCal();

		$cCUCClientDCal->SetConfig( CUCClientDCal::CFGKEY_DOMAIN,	'.desktopcal.com' );
		$cCUCClientDCal->SetConfig( CUCClientDCal::CFGKEY_SEED,		'sdsdsdsdq3e913-498234' );

		$_COOKIE	=
		[
			'dcelang'	=> 'usa',
			'dcver'		=> 1010,
			'dchid'		=> 1,
			'dctid'		=> 1,
			'dcumid'	=> '101115adc205e3cc47cecddd187a54b30a06e3',
			'dcufnm'	=> '%E5%88%98%E5%85%B6%E6%98%9F',
			'dcuac'		=> 3,
			'dcclt'		=> 1473426626,
			'dccsn'		=> 'e40bc6c2fc9e9100d76616a0ce3476f1',
			'dccrc'		=> 1602249063,
			'dcckl'		=> 1,
			'dcesk'		=> 'default',
			'dcesyncsrv'	=> 12
		];

		$cCUCClientDCal->InitCookieArray();
		$bIsLoggedIn	= $cCUCClientDCal->IsLogin();

		echo "\r\n";
		printf( "testForIsLogin :: Is Logged in = %s ", $bIsLoggedIn ? "TRUE" : "FALSE" );
		echo "\r\n";

	}
}
