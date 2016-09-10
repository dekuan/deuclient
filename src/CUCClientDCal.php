<?php

namespace dekuan\deuclient;

use dekuan\delib\CLib;


/**
 *	class of CUCClientDCal
 */
class CUCClientDCal
{
	//	statics
	protected static $mg_cInstanceUCClientDCal;


	//
	//      keys for configuration
	//
	const CFGKEY_DOMAIN     = 'domain';     //      domain that the cookie is available to.
	const CFGKEY_PATH       = 'path';       //      path on the server in which the cookie will be available on.
	const CFGKEY_SEED       = 'seed';       //      seed for making sign
	const CFGKEY_SECURE     = 'secure';     //      indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
	const CFGKEY_HTTPONLY   = 'httponly';   //      when TRUE the cookie will be made accessible only through the HTTP protocol.
	const CFGKEY_STIMEOUT   = 'stimeout';   //      session timeout

	//
	//	keys of Cookie
	//
	const CK_VER		= "dcver";	//	the version of cookie
	const CK_HID		= "dchid";	//	the host id of data
	const CK_TID		= "dctid";	//	the table id of data
	const CK_UMID		= "dcumid";	//	u_mid of user_table
	const CK_UNAME		= "dcunm";	//	u_name of user_table
	const CK_UFULLNAME	= "dcufnm";	//	u_fullname of user_table
	const CK_UIMG		= "dcuimg";	//
	const CK_UACTION	= "dcuac";	//	action of user
	const CK_CLOGINTIME	= "dcclt";	//	time when user log in
	const CK_CSIGNATURE	= "dccsn";	//	signature of cookie
	const CK_CCRC		= "dccrc";	//	crc32 value of cookie
	const CK_CKEEPALIVE	= "dcckl";	//	if keep the user alive
	const CK_ESKIN		= "dcesk";	//	extra - skin
	const CK_ELANG		= "dcelang";	//	extra - lang
	const CK_ESYNCSRV	= "dcesyncsrv";	//	extra - sync srv


	//
	//	default values
	//
	const DEFAULT_DOMAIN            = '.dekuan.org';
	const DEFAULT_PATH	        = '/';
	const DEFAULT_SIGN_SEED	        = '03abafc5ssss-2f15-66ea-bc1f-51805f380f06/9b2331cb-8a9c-4a29-a9ab-25e13359279c';
	const DEFAULT_SESSION_TIMEOUT	= 86400;	//	session timeout
	const DEFAULT_SECURE	        = false;	//	cookie should only be transmitted over a secure HTTPS connection from the client
	const DEFAULT_HTTPONLY	        = true;		//	cookie will be made accessible only through the HTTP protocol
	const DEFAULT_LANG_NAME		= 'usa';	//	default language name


	//
	//	member vars
	//
	var $m_ArrCk		= Array();

	//
	//	configuration
	//
	protected $m_arrCfg	= [];

	//
	protected $m_arrSupportedLangList =
	[
		'usa'	=> [ 0 => 'United States',	1 => 'English',		'deftz' => 'timezone_america_new_york',	'ua' => [ 'en', 'en-us' ] ],
		'kor'	=> [ 0 => '대한민국',		1 => '한국어',		'deftz' => 'timezone_asia_seoul',	'ua' => [ 'ko' ]  ],
		'jpn'	=> [ 0 => '日本',		1 => '日本語',		'deftz' => 'timezone_asia_tokyo',	'ua' => [ 'ja' ] ],
		//	'fra'	=> [ 0 => 'France',	1 => 'Français',	'deftz' => '',				'ua' => [ 'fr', 'fr-be', 'fr-ch', 'fr-ca', 'fr-lu' ] ],
		'chs'	=> [ 0 => '中国大陆',		1 => '简体中文',	'deftz' => 'timezone_asia_shanghai',	'ua' => [ 'zh-cn', 'zh' ] ],
		//	'zhh'	=> [ 0 => '中國香港',	1 => '繁體中文',	'deftz' => 'timezone_asia_hong_kong',	'ua' => [ 'zh-hk', 'zh-sg' ] ],
		'cht'	=> [ 0 => '中華臺灣',		1 => '繁體中文',	'deftz' => 'timezone_asia_taipei',	'ua' => [ 'zh-tw', 'zh-hk', 'zh-sg' ] ],

		//	'deu'	=> [ 0 => 'Deutschland',	1 => 'Deutsch',		'deftz' => '', 'ua' => Array( 'de', 'de-ch', 'de-at', 'de-lu', 'de-li' ) ],
		//	'ita'	=> [ 0 => 'Italia',	1 => 'Italiano',	'deftz' => '', 'ua' => Array( 'it', 'it-ch' ) ],
		//	'esn'	=> [ 0 => 'España',	1 => 'Español',		'deftz' => '', 'ua' => Array( 'es', 'es-mx', 'es-cr', 'es-do', 'es-co', 'es-ar', 'es-cl', 'es-py', 'es-sv', 'es-ni', 'es-gt', 'es-pa', 'es-ve', 'es-pe', 'es-ec', 'es-uy', 'es-bo', 'es-hn', 'es-pr' ) ],
		//	'ptg'	=> [ 0 => 'Brasil',	1 => 'Português',	'deftz' => '', 'ua' => Array( 'pt' ) ],
		//	'plk'	=> [ 0 => 'Polska',	1 => 'Polski',		'deftz' => '', 'ua' => Array( 'pl' ) ],
		//	'vit'	=> [ 0 => 'Vietnam',	1 => 'Việt Nam',	'deftz' => '', 'ua' => Array( 'vi' ) ],
		//	'tha'	=> [ 0 => 'ประเทศไทย',	1 => 'ภาษาไทย',		'deftz' => '', 'ua' => Array( 'th' ) ],
		//	'rus'	=> [ 0 => 'Россия',	1 => 'Русский',		'deftz' => '', 'ua' => Array( 'ru', 'ru-mo' ) ],
	];

	//	...
	var $m_bIsLogin		= -1;


	public function __construct()
	{
		$this->m_ArrCk	= $this->GetCookieArray();
		$this->m_arrCfg	=
		[
			self::CFGKEY_DOMAIN	=> self::DEFAULT_DOMAIN,
			self::CFGKEY_PATH	=> self::DEFAULT_PATH,
			self::CFGKEY_SEED	=> self::DEFAULT_SIGN_SEED,		//	seed
			self::CFGKEY_SECURE	=> self::DEFAULT_SECURE,
			self::CFGKEY_HTTPONLY	=> self::DEFAULT_HTTPONLY,
			self::CFGKEY_STIMEOUT	=> self::DEFAULT_SESSION_TIMEOUT,	//	session timeout, default is 1 day.
		];
	}
	public function __destruct()
	{
	}
	static function getInstance()
	{
		if ( is_null( self::$mg_cInstanceUCClientDCal ) || ! isset( self::$mg_cInstanceUCClientDCal ) )
		{
			self::$mg_cInstanceUCClientDCal = new self();
		}
		return self::$mg_cInstanceUCClientDCal;
	}


	//
	//	configuration
	//
	public function GetConfig( $sKey = '' )
	{
		if ( CLib::IsExistingString( $sKey ) &&
			array_key_exists( $sKey, $this->m_arrCfg ) )
		{
			return $this->m_arrCfg[ $sKey ];
		}
		else
		{
			return $this->m_arrCfg;
		}
	}
	public function SetConfig( $sKey, $vValue )
	{
		if ( CLib::IsExistingString( $sKey ) &&
			array_key_exists( $sKey, $this->m_arrCfg ) )
		{
			$this->m_arrCfg[ $sKey ] = $vValue;
			return true;
		}
		else
		{
			return false;
		}
	}


	//
	//	detect by cookie if current user already logged in
	//
	public function IsLogin()
	{
		$bLogin	= false;

		//	...
		if ( -1 != $this->m_bIsLogin )
		{
			//	have already checked via this process
			return $this->m_bIsLogin ? true : false;
		}

		//	...
		if ( $this->_IsExistUCookie() )
		{
			if ( $this->_IsLoginTimeout() )
			{
				if ( $this->_IsValidSign() )
				{
					if ( $this->_IsValidCRC() )
					{
						$bLogin = true;
					}
					else
					{
						//echo "! _IsValidCRC()";
					}
				}
				else
				{
					//echo "! _IsValidSign()";
				}
			}
			else
			{
				//echo "! _IsLoginTimeout()";
			}
		}
		else
		{
			//echo "! _IsExistUCookie()";
		}

		$this->m_bIsLogin = $bLogin ? 1 : 0;

		return $bLogin;
	}
	public function Logout()
	{
		foreach( $this->m_ArrCk as $key => $val )
		{
			$this->_SetCookieByName( $key, '', true, true );
		}
	}

	public function GetCookieArray()
	{
		$ArrSafeCk = Array
		(
			self::CK_VER		=> CLib::GetVal( $_COOKIE, self::CK_VER, false, "" ),
			self::CK_HID		=> intval( CLib::GetVal( $_COOKIE, self::CK_HID, true, -1 ) ),
			self::CK_TID		=> intval( CLib::GetVal( $_COOKIE, self::CK_TID, true, -1 ) ),
			self::CK_UMID		=> CLib::GetVal( $_COOKIE, self::CK_UMID, false, "" ),
			self::CK_UFULLNAME	=> CLib::GetVal( $_COOKIE, self::CK_UFULLNAME, false, "" ),
			self::CK_UIMG		=> CLib::GetVal( $_COOKIE, self::CK_UIMG, false, "" ),
			self::CK_UACTION	=> intval( CLib::GetVal( $_COOKIE, self::CK_UACTION, true, -1 ) ),
			self::CK_CLOGINTIME	=> intval( CLib::GetVal( $_COOKIE, self::CK_CLOGINTIME, true, -1 ) ),
			self::CK_CSIGNATURE	=> CLib::GetVal( $_COOKIE, self::CK_CSIGNATURE, false, "" ),
			self::CK_CCRC		=> intval( CLib::GetVal( $_COOKIE, self::CK_CCRC, true, -1 ) ),
			self::CK_CKEEPALIVE	=> intval( CLib::GetVal( $_COOKIE, self::CK_CKEEPALIVE, true, -1 ) ),
			self::CK_ESKIN		=> CLib::GetVal( $_COOKIE, self::CK_ESKIN, false, "" ),
			self::CK_ELANG		=> CLib::GetVal( $_COOKIE, self::CK_ELANG, false, "" ),
			self::CK_ESYNCSRV	=> CLib::GetVal( $_COOKIE, self::CK_ESYNCSRV, true, -1 ),
		);

		if ( ! is_array( $this->m_ArrCk ) )
		{
			$this->m_ArrCk = Array();
		}
		foreach ( $ArrSafeCk as $sKey => $sVal )
		{
			$this->m_ArrCk[ $sKey ] = $sVal;
		}

		return $this->m_ArrCk;
	}
	public function UpdateCookieArray()
	{
		$this->GetCookieArray();
	}
	public function SetCookieArrayWithArray( $arrCookie )
	{
		if ( ! CLib::IsArrayWithKeys( $arrCookie ) )
		{
			return false;
		}

		foreach ( $arrCookie as $sKey => $sVal )
		{
			$this->SetCookieArray( $sKey, $sVal );
		}

		return true;
	}
	public function SetCookieArray( $sKey, $sVal )
	{
		if ( ! CLib::IsExistingString( $sKey ) )
		{
			return false;
		}
		if ( ! CLib::IsExistingString( $sVal ) || ! is_numeric( $sVal ) )
		{
			return false;
		}

		$bRet	= false;
		$sKey	= strtolower( trim( $sKey ) );
		if ( ! empty( $sKey ) )
		{
			$bRet = true;

			if ( ! is_array( $_COOKIE ) )
			{
				$_COOKIE = Array();
			}
			$_COOKIE[ $sKey ] = $sVal;
		}

		return $bRet;
	}
	public function GetCookieString()
	{
		$ArrCookie = $this->GetCookieArray();
		foreach ( $ArrCookie as $sKey => $sVal )
		{
			if ( 0 == strlen( $sVal ) )
			{
				//	remove the item with empty value
				unset( $ArrCookie[ $sKey ] );
			}
		}
		return http_build_query( $ArrCookie, '', '; ' );
	}

	//
	//	get user info from cookie
	//
	public function IsKeepAlive()
	{
		return intval( $this->m_ArrCk[ self::CK_CKEEPALIVE ] ) ? true : false;
	}
	public function SetKeepAlive( $bKeepAlive )
	{
		$this->m_ArrCk[ self::CK_CKEEPALIVE ] = ( $bKeepAlive ? '1' : '0' );
	}

	public function GetHostId()
	{
		return $this->m_ArrCk[ self::CK_HID ];
	}
	public function SetHostId( $nHId )
	{
		$this->m_ArrCk[ self::CK_HID ] = ( is_numeric( $nHId ) ? $nHId : 0 );
	}

	public function GetTableId()
	{
		return $this->m_ArrCk[ self::CK_TID ];
	}
	public function SetTableId( $nTId )
	{
		$this->m_ArrCk[ self::CK_TID ] = ( is_numeric( $nTId ) ? $nTId : 0 );
	}

	public function GetUserMId()
	{
		return $this->m_ArrCk[ self::CK_UMID ];
	}
	public function SetUserMId( $sUMId )
	{
		$this->m_ArrCk[ self::CK_UMID ] = ( is_string( $sUMId ) ? $sUMId : "" );
	}

	public function GetUserAction()
	{
		return $this->m_ArrCk[ self::CK_UACTION ];
	}
	public function SetUserAction( $nAction )
	{
		$this->m_ArrCk[ self::CK_UACTION ] = ( is_numeric( $nAction ) ? $nAction : -1 );
	}

	public function GetUserFullName()
	{
		return $this->m_ArrCk[ self::CK_UFULLNAME ];
	}
	public function SetUserFullName( $sUserNickname )
	{
		$this->m_ArrCk[ self::CK_UFULLNAME ] = trim( $sUserNickname );
	}

	public function GetUserLang()
	{
		if ( array_key_exists( self::CK_ELANG, $this->m_ArrCk ) &&
			! empty( $this->m_ArrCk[ self::CK_ELANG ] ) )
		{
			$sRet = $this->m_ArrCk[ self::CK_ELANG ];
		}
		else
		{
			$sRet = $this->_GetBrowserLang();
			$this->SetUserLang( $sRet, true );
		}

		//	verify
		$sRet = strtolower( trim( $sRet ) );
		if ( ! $this->IsSupportedLang( $sRet ) )
		{
			$sRet = "usa";
		}

		return $sRet;
	}
	public function IsSupportedLang( $sLang )
	{
		if ( ! CLib::IsExistingString( $sLang ) )
		{
			return false;
		}

		//	...
		$sLang = strtolower( trim( $sLang ) );
		if ( empty( $sLang ) )
		{
			return false;
		}

		return array_key_exists( $sLang, $this->m_arrSupportedLangList );
	}
	public function SetUserLang( $sUserLang, $bSaveToCookie = false )
	{
		if ( ! CLib::IsExistingString( $sUserLang ) || ! is_bool( $bSaveToCookie ) )
		{
			return false;
		}

		$this->m_ArrCk[ self::CK_ELANG ] = trim( $sUserLang );
		if ( $bSaveToCookie )
		{
			$this->_SetCookieByName( self::CK_ELANG, trim( $sUserLang ), true );
		}

		return true;
	}

	public function GetUserSkin()
	{
		return ( isset( $this->m_ArrCk[ self::CK_ESKIN ] ) ? $this->m_ArrCk[ self::CK_ESKIN ] : '' );
	}
	public function SetUserSkin( $sUserSkin )
	{
		if ( ! CLib::IsExistingString( $sUserSkin ) )
		{
			return false;
		}

		$this->m_ArrCk[ self::CK_ESKIN ] = trim( $sUserSkin );
		return true;
	}


	//	get signature
	public function GetSignData( $sCkVer, $nLoginTime, $nCkHId, $nCkTId, $nCkUMId, $sCkUAction )
	{
		$sString = $sCkVer . "-" .
			strval( $nLoginTime ) . "-" .
			strval( $nCkHId ) . "-" .
			strval( $nCkTId ) . "-" .
			strval( $nCkUMId ) . "-" .
			$sCkUAction;
		$sData		= mb_strtolower( trim( $sString . "" . $this->m_arrCfg[ self::CFGKEY_SEED ] ) );
		return md5( $sData );
	}
	public function GetCRCData( $sCkVer, $nLoginTime, $nCkHId, $nCkTId, $nCkUMId, $sCkUAction )
	{
		$sString	= $sCkVer . "-" .
			strval( $nLoginTime ) . "-" .
			strval( $nCkHId ) . "-" .
			strval( $nCkTId ) . "-" .
			strval( $nCkUMId ) . "-" .
			$sCkUAction;
		$sData		= mb_strtolower( trim( $sString . "" . $this->m_arrCfg[ self::CFGKEY_SEED ] ) );
		return abs( crc32( $sData ) );
	}

	//	set data to cookie in batch
	public function SetDataToCookie( $ArrInfo, $bKeepAlive )
	{
		if ( CLib::IsArrayWithKeys( $ArrInfo ) )
		{
			foreach( $ArrInfo as $sKey => $sVal )
			{
				//	update cookie array in memory
				$this->SetCookieArray( $sKey, $sVal );

				//	send cookie to the client
				$this->_SetCookieByName( $sKey, $sVal, $bKeepAlive, false );
			}
		}
	}


	////////////////////////////////////////////////////////////
	//	private
	////////////////////////////////////////////////////////////

	private function _GetDefaultLang()
	{
		return self::DEFAULT_LANG_NAME;
	}
	private function _GetBrowserLang()
	{
		$sRet		= $this->_GetDefaultLang();
		$bMatched	= false;
		$ArrBAcptLangs	= $this->_GetBrowserAcceptLanguageList();

		if ( CLib::IsArrayWithKeys( $ArrBAcptLangs ) )
		{
			foreach ( $this->m_arrSupportedLangList as $sCode => $Arr )
			{
				if ( array_key_exists( 'ua', $Arr ) && is_array( $Arr['ua'] ) )
				{
					foreach ( $Arr['ua'] as $item )
					{
						$item = strtolower( trim( $item ) );
						if ( in_array( $item, $ArrBAcptLangs ) )
						{
							$bMatched = true;
							$sRet = $sCode;
							break;
						}
					}
				}
				if ( $bMatched )
				{
					break;
				}
			}
		}

		return $sRet;
	}
	private function _GetBrowserAcceptLanguageList()
	{
		//	Chrome:
		//	Accept-Language:en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4,ja;q=0.2,zh-TW;q=0.2,de;q=0.2,es;q=0.2,fr;q=0.2,it;q=0.2,ko;q=0.2,pl;q=0.2,pt-PT;q=0.2,pt;q=0.2,ru;q=0.2,th;q=0.2,vi;q=0.2
		//	IE: zh-CN
		//	FireFox: en-US,en;q=0.5
		$ArrRet	= Array();
		$sLang	= isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "";
		$sLang	= strtolower( trim( $sLang ) );		//	"en-us,en;q=0.5"

		if ( ! empty( $sLang ) )
		{
			if ( strstr( $sLang, ';' ) || strstr( $sLang, ',' ) )
			{
				$ArrTmp	= @ preg_split( "/[,;]+/", $sLang, -1, PREG_SPLIT_NO_EMPTY );
				if ( CLib::IsArrayWithKeys( $ArrTmp ) )
				{
					foreach ( $ArrTmp as $sLangItem )
					{
						if ( 0 != strncasecmp( $sLangItem, 'q=', 2 ) )
						{
							$ArrRet[] = strtolower( trim( $sLangItem ) );
						}
					}
				}
			}
			else
			{
				$ArrRet[] = $sLang;
			}
		}

		return $ArrRet;
	}

	//	设置指定 name 的 Cookie 的值
	//	set cookie value by name
	private function _SetCookieByName( $sCkName, $sCkValue, $bKeepAlive = false, $bLogout = false )
	{
		if ( $bLogout )
		{
			//
			//	for Logout
			//
			$tmExpire = time() - 365*24*60*60;
			setcookie( $sCkName, $sCkValue, $tmExpire, '/', $this->m_arrCfg[ self::CFGKEY_DOMAIN ] );
		}
		else
		{
			//
			//	for login
			//
			if ( $bKeepAlive )
			{
				//	set the expire date as 1 year.
				//	the browser will keep this cookie for 1 year.
				$tmExpire = time() + 365*24*60*60;
				//setcookie( $sCkName, $sCkValue, $tmExpire, '/', $this->m_arrCfg[ self::CFGKEY_DOMAIN ] );
				setcookie( $sCkName, $sCkValue, $tmExpire, '/', $this->m_arrCfg[ self::CFGKEY_DOMAIN ] );
			}
			else
			{
				setcookie( $sCkName, $sCkValue, 0, '/', $this->m_arrCfg[ self::CFGKEY_DOMAIN ] );
			}
		}
	}
	private function _IsExistUCookie()
	{
		$bRet	= false;

		if ( ! empty( $this->m_ArrCk[ self::CK_VER ] ) &&
			$this->m_ArrCk[ self::CK_HID ] > 0 &&
			$this->m_ArrCk[ self::CK_TID ] >= 0 &&
			! empty( $this->m_ArrCk[ self::CK_UMID ] ) )
		{
			if ( $this->m_ArrCk[ self::CK_CLOGINTIME ] > 0 &&
				$this->m_ArrCk[ self::CK_CCRC ] > 0 &&
				( ! empty( $this->m_ArrCk[ self::CK_CSIGNATURE ] ) ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}

	//	if login info has timeout
	private function _IsLoginTimeout()
	{
		if ( 1 == intval( $this->m_ArrCk[ self::CK_CKEEPALIVE ] ) )
		{
			//
			//	return true if user set to keep alive
			//
			return true;
		}

		//	检查是否超时，如果用户没有设置“保持登录”的话，就最长保留 12 小时
		$term = floatval( ( time() - $this->m_ArrCk[ self::CK_CLOGINTIME ] ) );
		return ( $term <= $this->m_arrCfg[ self::CFGKEY_STIMEOUT ] );
	}

	//	check signature
	private function _IsValidSign()
	{
		$bRet	= false;

		if ( ! empty( $this->m_ArrCk[ self::CK_CSIGNATURE ] ) )
		{
			$sSignData = $this->GetSignData
			(
				$this->m_ArrCk[ self::CK_VER ],
				$this->m_ArrCk[ self::CK_CLOGINTIME ],
				$this->m_ArrCk[ self::CK_HID ],
				$this->m_ArrCk[ self::CK_TID ],
				$this->m_ArrCk[ self::CK_UMID ],
				$this->m_ArrCk[ self::CK_UACTION ]
			);
			if ( 0 == strcasecmp( $sSignData, $this->m_ArrCk[ self::CK_CSIGNATURE ] ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}
	//	check CRC
	private function _IsValidCRC()
	{
		$bRet	= false;

		if ( ! empty( $this->m_ArrCk[ self::CK_CCRC ] ) )
		{
			$sSignData = $this->GetCRCData
			(
				$this->m_ArrCk[ self::CK_VER ],
				$this->m_ArrCk[ self::CK_CLOGINTIME ],
				$this->m_ArrCk[ self::CK_HID ],
				$this->m_ArrCk[ self::CK_TID ],
				$this->m_ArrCk[ self::CK_UMID ],
				$this->m_ArrCk[ self::CK_UACTION ]
			);
			if ( 0 == strcasecmp( $sSignData, $this->m_ArrCk[ self::CK_CCRC ] ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}
}