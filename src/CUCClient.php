<?php

namespace dekuan\deuclient;

use dekuan\delib\CLib;


/**
 *	CUCClient
 */
class CUCClient
{
	//	statics instance
	protected static $g_cStaticInstance;

	//
	//	keys for cookie
	//
	const CKX               = 'X';
	const CKT               = 'T';

	const CKX_UMID		= 'mid';	//	string	- user mid ( a string with length of 32/64 characters )
	const CKX_UNICKNAME	= 'nkn';	//	string	- user nick name
	const CKX_UTYPE		= 't';		//	int	- user type, values( NORMAL, TEMP, ... )
	const CKX_UIMGID	= 'imgid';	//	string	- the mid of user avatar
	const CKX_USTATUS	= 'sts';	//	int	- user status
	const CKX_UACT		= 'act';	//	int	- user action
	const CKX_SRC		= 'src';	//	string	- the source which a user logged on from

	const CKT_VER		= 'v';		//	string	- cookie version
	const CKT_LOGINTM	= 'ltm';	//	int	- login time, unix time stamp in timezone 0.
	const CKT_REFRESHTM	= 'rtm';	//	int	- last refresh time
	const CKT_UPDATETM	= 'utm';	//	int	- last update time
	const CKT_KPALIVE	= 'kpa';	//	int	- keep alive, values( YES, NO )
	const CKT_SMID		= 'smid';	//	string	- session mid
	const CKT_CSIGN		= 'css';	//	string	- checksum sign
	const CKT_CSRC		= 'csc';	//	string	- checksum crc

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
	//	default values
	//
	const DEFAULT_DOMAIN            = '.dekuan.org';
	const DEFAULT_PATH	        = '/';
	const DEFAULT_SIGN_SEED	        = '03abafc5ssss-2f15-66ea-bc1f-51805f380f06/9b2331cb-8a9c-4a29-a9ab-25e13359279c';
	const DEFAULT_SECURE	        = false;	//	cookie should only be transmitted over a secure HTTPS connection from the client
	const DEFAULT_HTTPONLY	        = true;		//	cookie will be made accessible only through the HTTP protocol

	//
	//	error ids
	//
	const ERR_UNKNOWN               = -1;   //      unknown error
	const ERR_SUCCESS               = 0;    //      successfully
	const ERR_FAILURE               = 1000; //      failed
	const ERR_PARAMETER             = 1001; //      error in parameter
	const ERR_INVALID_XT_COOKIE	= 1002;	//	invalid XT cookie
	const ERR_INVALID_CRC		= 1003;	//	invalid CRC
	const ERR_INVALID_SIGN		= 1004;	//	invalid sign
	const ERR_LOGIN_TIMEOUT		= 1005;	//	login timeout
	const ERR_BAD_COOKIE		= 1006;	//	bad cookie
	const ERR_ENCRYPT_XT            = 1007; //      failed to encrypt xt
	const ERR_SETCOOKIE             = 1008; //      failed to set cookie
	const ERR_PARSE_COOKIE_STRING	= 1009;	//	failed to parse cookie string
	const ERR_RESET_COOKIE		= 1010;	//	failed to reset cookie

	//
	//      user status
	//
	const USTATUS_UNVERIFIED        = 0;	//	unverified
	const USTATUS_OKAY		= 1;	//	okay
	const USTATUS_DELETED		= 2;	//	deleted
	const USTATUS_EXPIRED		= 3;	//	expired
	const USTATUS_DENIED		= 4;	//	denied
	const USTATUS_COMPLETE		= 5;	//	complete
	const USTATUS_ABORT		= 6;	//	abort
	const USTATUS_PENDING		= 7;	//	pending
	const USTATUS_ACCEPTED		= 8;	//	accepted
	const USTATUS_REJECTED		= 9;	//	rejected
	const USTATUS_ARCHIVED		= 10;	//	archived

	//
	//	cookie information
	//
	const COOKIE_VERSION            = '1.0.2.1002';

	//
	//	$_COOKIE or parsed from sCookieString
	//
	protected $m_arrCookie		= [];

	//
	//	configuration
	//
	protected $m_arrCfg		= Array();

	//
	//	cache
	//
	protected $m_bIsLoggedin	= null;

	//
	//	main keys
	//
	protected $m_sMKeyX		= self::CKX;
	protected $m_sMKeyT		= self::CKT;


        //
        //	TODO
	//	1, make user logged out if their session is timeout, via Redis
        //

        public function __construct()
        {
		//
		//	...
		//
		$this->m_arrCookie	= ( is_array( $_COOKIE ) ? $_COOKIE : [] );
                $this->m_arrCfg		=
                [
                        self::CFGKEY_DOMAIN	=> self::DEFAULT_DOMAIN,
                        self::CFGKEY_PATH	=> self::DEFAULT_PATH,
                        self::CFGKEY_SEED	=> self::DEFAULT_SIGN_SEED,	//	seed
                        self::CFGKEY_SECURE	=> self::DEFAULT_SECURE,
                        self::CFGKEY_HTTPONLY	=> self::DEFAULT_HTTPONLY,
                        self::CFGKEY_STIMEOUT	=> 86400,			//	session timeout, default is 1 day.
                ];
        }
        public function __destruct()
        {
        }
        static function getInstance()
        {
                if ( is_null( self::$g_cStaticInstance ) || ! isset( self::$g_cStaticInstance ) )
                {
                        self::$g_cStaticInstance = new self();
                }
                return self::$g_cStaticInstance;
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
                if ( CLib::IsExistingString( $sKey ) && array_key_exists( $sKey, $this->m_arrCfg ) )
                {
                        $this->m_arrCfg[ $sKey ] = $vValue;
                        return true;
                }
                else
                {
                        return false;
                }
        }

	public function SetMainKeyName()
	{

	}


        //
        //	make user login
        //
        public function MakeLogin( $arrData, $bKeepAlive = false, & $sCkString = '' )
        {
                //
                //	arrData		- [in] Array
                //	(
                //		'X'	=> Array
                //		(
                //			'mid'	=> '101101aaefe12342aaefe12342aaefe12342',
                //			'nkn'	=> '',
                //			't'	=> 0,
                //			'imgid'	=> '',
                //			'act'	=> 0,
                //			'src'	=> '',
                //		)
                //		'T'	=> Array
                //		(
                //			'v'	=> '',
                //			'ltm'	=> 0,
                //			'rtm'	=> 0,
                //			'utm'	=> 0,
                //			'kpa'	=> 1,
                //			...
                //		)
                //	)
                //	bKeepAlive	- [in] keep alive
                //      sCkString       - [out] a string contains the full XT cookie
                //	RETURN		- self::ERR_SUCCESS successfully, otherwise error id
                //
		if ( ! CLib::IsArrayWithKeys( $arrData, [ $this->m_sMKeyX, $this->m_sMKeyT ] ) )
		{
			return self::ERR_PARAMETER;
		}
		if ( ! CLib::IsArrayWithKeys( $arrData[ $this->m_sMKeyX ] ) ||
			! CLib::IsArrayWithKeys( $arrData[ $this->m_sMKeyT ] ) )
		{
			return self::ERR_PARAMETER;
		}
		if ( ! array_key_exists( self::CKX_UMID, $arrData[ $this->m_sMKeyX ] ) ||
			! array_key_exists( self::CKX_UTYPE, $arrData[ $this->m_sMKeyX ] ) ||
			! array_key_exists( self::CKX_USTATUS, $arrData[ $this->m_sMKeyX ] ) ||
			! array_key_exists( self::CKX_UACT, $arrData[ $this->m_sMKeyX ] ) ||
			! array_key_exists( self::CKT_LOGINTM, $arrData[ $this->m_sMKeyT ] ) ||
			! array_key_exists( self::CKT_REFRESHTM, $arrData[ $this->m_sMKeyT ] ) ||
			! array_key_exists( self::CKT_UPDATETM, $arrData[ $this->m_sMKeyT ] ) )
		{
			return self::ERR_PARAMETER;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		//
		//      make signature and crc checksum
		//
		$arrData[ $this->m_sMKeyT ][ self::CKT_KPALIVE ]      = ( $bKeepAlive ? 1 : 0 );
		$arrData[ $this->m_sMKeyT ][ self::CKT_VER ]          = self::COOKIE_VERSION;
		$arrData[ $this->m_sMKeyT ][ self::CKT_CSIGN ]	= $this->GetSignData( $arrData );
		$arrData[ $this->m_sMKeyT ][ self::CKT_CSRC ]         = $this->GetCRCData( $arrData );

		//	...
		$arrEncryptedCk = $this->_EncryptXTArray( $arrData );
		if ( $arrEncryptedCk &&
			is_array( $arrEncryptedCk ) &&
			array_key_exists( $this->m_sMKeyX, $arrEncryptedCk ) &&
			array_key_exists( $this->m_sMKeyT, $arrEncryptedCk ) )
		{
			if ( $this->_SetCookieForLogin( $arrEncryptedCk, $bKeepAlive, $sCkString ) )
			{
				$nRet = self::ERR_SUCCESS;
			}
			else
			{
				$nRet = self::ERR_SETCOOKIE;
			}
		}
		else
		{
			$nRet = self::ERR_ENCRYPT_XT;
		}

		//	...
		return $nRet;
	}
	public function MakeLoginWithCookieString( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return self::ERR_PARAMETER;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		//	...
		$arrEncryptedCk	= $this->_GetEncryptedXTArrayFromString( $sCkString );
		$nErrorId	= $this->_CheckEncryptedXTArray( $arrEncryptedCk );
		if ( self::ERR_SUCCESS == $nErrorId )
		{
			$arrCookie = $this->_DecryptXTArray( $arrEncryptedCk );
			if ( CLib::IsArrayWithKeys( $arrCookie, $this->m_sMKeyT ) &&
				CLib::IsArrayWithKeys( $arrCookie[ $this->m_sMKeyT ], self::CKT_KPALIVE ) )
			{
				if ( self::ERR_SUCCESS == $this->ResetCookie( $sCkString ) )
				{
					$bKeepAlive = boolval( $this->_GetSafeVal( self::CKT_KPALIVE, $arrCookie[ $this->m_sMKeyT ], 0 ) );
					if ( $this->_SetCookieForLogin( $arrEncryptedCk, $bKeepAlive ) )
					{
						$nRet = self::ERR_SUCCESS;
					}
					else
					{
						$nRet = self::ERR_SETCOOKIE;
					}
				}
				else
				{
					$nRet = self::ERR_RESET_COOKIE;
				}
			}
			else
			{
				$nRet = self::ERR_INVALID_XT_COOKIE;
			}
		}
		else
		{
			$nRet = $nErrorId;
		}

		return $nRet;
	}

	//
	//	log out
	//
        public function Logout()
        {
                return ( $this->_SetCookieForLogout() ? self::ERR_SUCCESS : self::ERR_FAILURE );
        }

        //
        //	log in
        //
        public function CheckLogin()
        {
                //	...
                $nRet = self::ERR_UNKNOWN;

                //	...
                if ( null !== $this->m_bIsLoggedin )
                {
                        //	have already checked
                        return ( $this->m_bIsLoggedin ? self::ERR_SUCCESS : self::ERR_FAILURE );
                }

                //	...
                if ( $this->_IsExistsXT() )
		{
			if ( $this->_IsValidSign() )
			{
				if ( $this->_IsValidCRC() )
				{
					if ( ! $this->_IsSessionTimeout() )
					{
						$nRet = self::ERR_SUCCESS;
					}
					else
					{
						//      Session is timeout
						$nRet = self::ERR_LOGIN_TIMEOUT;
					}
				}
				else
				{
					$nRet = self::ERR_INVALID_CRC;
				}
			}
			else
			{
				//      invalid sign
				$nRet = self::ERR_INVALID_SIGN;
			}
                }
                else
                {
                        //      cookie is not exists
                        $nRet = self::ERR_BAD_COOKIE;
                }

                //	push to cache
                $this->m_bIsLoggedin = ( self::ERR_SUCCESS == $nRet );

                //	...
                return $nRet;
        }

	//
	//	reset cookie via cookie string
	//
	public function ResetCookie( $sCkString )
	{
		return $this->_ResetCookieByCookieString( $sCkString );
	}

        public function IsExistsXT()
        {
                return $this->_IsExistsXT();
        }

        public function GetCookieString()
        {
		$sRet	= '';

                $arrDecryptedXT = $this->GetOriginalXTArray();
		if ( is_array( $arrDecryptedXT ) &&
			array_key_exists( $this->m_sMKeyX, $arrDecryptedXT ) &&
			array_key_exists( $this->m_sMKeyT, $arrDecryptedXT ) )
		{
			$sRet = http_build_query
			(
				[
					$this->m_sMKeyX => $arrDecryptedXT[ $this->m_sMKeyX ],
					$this->m_sMKeyT => $arrDecryptedXT[ $this->m_sMKeyT ]
				],
				'', '; '
			);
		}

		return $sRet;
        }

        public function GetOriginalXTArray()
        {
		return Array
		(
			$this->m_sMKeyX => $this->_GetSafeVal( $this->m_sMKeyX, $this->m_arrCookie, '' ),
			$this->m_sMKeyT => $this->_GetSafeVal( $this->m_sMKeyT, $this->m_arrCookie, '' ),
		);
	}

	public function GetXTArray()
	{
		return $this->_DecryptXTArray( $this->GetOriginalXTArray() );
        }

	public function GetXTValue( $sNode, $sKey )
	{
		//
		//	sNode	- values( 'X', 'T' )
		//	sKey	- keys
		//	RETURN	- ...
		//
		if ( ! CLib::IsExistingString( $sNode ) ||
			! CLib::IsExistingString( $sKey ) )
		{
			return null;
		}

		//	...
		$vRet = null;

		//	...
		$arrData = $this->GetXTArray();
		if ( is_array( $arrData ) )
		{
			if ( array_key_exists( $sNode, $arrData ) &&
				is_array( $arrData[ $sNode ] ) &&
				array_key_exists( $sKey, $arrData[ $sNode ] ) )
			{
				$vRet = $arrData[ $sNode ][ $sKey ];
			}
		}

		return $vRet;
	}

        public function IsKeepAlive()
        {
                $bRet = false;

                //	...
                $nKeepAlive = $this->GetXTValue( $this->m_sMKeyT, self::CKT_KPALIVE );
                if ( is_numeric( $nKeepAlive ) )
                {
                        $bRet = ( 1 == intval( $nKeepAlive ) );
                }

                return $bRet;
        }

        //	build signature
        public function GetSignData( $arrData )
        {
                $sRet = '';

                //	...
                $sString = $this->_GetDigestSource( $arrData );
                if ( CLib::IsExistingString( $sString ) )
                {
                        $sData	= mb_strtolower( trim( $sString . "-" . $this->m_arrCfg[ self::CFGKEY_SEED ] ) );
                        $sRet	= md5( $sData );
                }

                //	...
                return $sRet;
        }

        //      build crc checksum
        public function GetCRCData( $arrData )
        {
                $nRet = 0;

                //	...
                $sString = $this->_GetDigestSource( $arrData );
                if ( CLib::IsExistingString( $sString ) )
                {
                        $sData	= mb_strtolower( trim( $sString . "-" . $this->m_arrCfg[ self::CFGKEY_SEED ] ) );
                        $nRet	= abs( crc32( $sData ) );
                }

                //	...
                return $nRet;
        }



        ////////////////////////////////////////////////////////////////////////////////
        //	protected
        //
	protected function _CheckCookieString( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return self::ERR_PARAMETER;
		}
		return $this->_CheckEncryptedXTArray( $this->_GetEncryptedXTArrayFromString( $sCkString ) );
	}

	protected function _CheckEncryptedXTArray( $arrEncryptedCookie )
	{
		if ( ! is_array( $arrEncryptedCookie ) ||
			! array_key_exists( $this->m_sMKeyX, $arrEncryptedCookie ) ||
			! array_key_exists( $this->m_sMKeyT, $arrEncryptedCookie ) )
		{
			return self::ERR_INVALID_XT_COOKIE;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		if ( $this->_IsExistsXT( $arrEncryptedCookie ) )
		{
			if ( $this->_IsValidSign( $arrEncryptedCookie ) )
			{
				if ( $this->_IsValidCRC( $arrEncryptedCookie ) )
				{
					$nRet = self::ERR_SUCCESS;
				}
				else
				{
					//	invalid crc
					$nRet = self::ERR_INVALID_CRC;
				}
			}
			else
			{
				//      invalid sign
				$nRet = self::ERR_INVALID_SIGN;
			}
		}
		else
		{
			//      cookie is not exists
			$nRet = self::ERR_BAD_COOKIE;
		}

		return $nRet;
	}

	protected function _GetEncryptedXTArrayFromString( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return [];
		}

		//	...
		$arrRet	= [];
		$sX	= '';
		$sT	= '';

		//
		//	parse X, T from string
		//
		$arrData = explode( '; ', $sCkString );
		if ( is_array( $arrData ) && count( $arrData ) > 1 )
		{
			parse_str( $arrData[ 0 ], $arrCk0 );
			parse_str( $arrData[ 1 ], $arrCk1 );

			if ( is_array( $arrCk0 ) )
			{
				if ( empty( $sX ) && array_key_exists( $this->m_sMKeyX, $arrCk0 ) )
				{
					$sX = $arrCk0[ $this->m_sMKeyX ];
				}
				else if ( empty( $sT ) && array_key_exists( $this->m_sMKeyT, $arrCk0 ) )
				{
					$sT = $arrCk0[ $this->m_sMKeyT ];
				}
			}
			if ( is_array( $arrCk1 ) )
			{
				if ( empty( $sX ) && array_key_exists( $this->m_sMKeyX, $arrCk1 ) )
				{
					$sX = $arrCk1[ $this->m_sMKeyX ];
				}
				else if ( empty( $sT ) && array_key_exists( $this->m_sMKeyT, $arrCk1 ) )
				{
					$sT = $arrCk1[ $this->m_sMKeyT ];
				}
			}

			//
			//	put the values to cookie
			//
			if ( is_string( $sX ) && strlen( $sX ) &&
				is_string( $sT ) && strlen( $sT ) )
			{
				$arrRet[ $this->m_sMKeyX ]	= $sX;
				$arrRet[ $this->m_sMKeyT ]	= $sT;
			}
		}

		return $arrRet;
	}

	protected function _ResetCookieByCookieString( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return self::ERR_PARAMETER;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		//
		//	parse X, T from string
		//
		$arrEncryptedCookie = $this->_GetEncryptedXTArrayFromString( $sCkString );
		$nErrorId = $this->_CheckEncryptedXTArray( $arrEncryptedCookie );
		if ( self::ERR_SUCCESS == $nErrorId )
		{
			//
			//	we checked the cookie from string is okay.
			//
			if ( self::ERR_SUCCESS ==
				$this->_ResetCookieByEncryptedXTArray( $arrEncryptedCookie ) )
			{
				$nRet = self::ERR_SUCCESS;
			}
			else
			{
				$nRet = self::ERR_RESET_COOKIE;
			}
		}
		else
		{
			$nRet = $nErrorId;
		}

		return $nRet;
	}

	protected function _ResetCookieByEncryptedXTArray( $arrEncryptedCk )
	{
		if ( ! CLib::IsArrayWithKeys( $arrEncryptedCk, [ $this->m_sMKeyX, $this->m_sMKeyT ] ) )
		{
			return self::ERR_INVALID_XT_COOKIE;
		}

		//
		//	set XT values to member variable
		//
		if ( ! is_array( $this->m_arrCookie ) )
		{
			$this->m_arrCookie = [];
		}
		$this->m_arrCookie[ $this->m_sMKeyX ]	= $arrEncryptedCk[ $this->m_sMKeyX ];
		$this->m_arrCookie[ $this->m_sMKeyT ]	= $arrEncryptedCk[ $this->m_sMKeyT ];

		return self::ERR_SUCCESS;
	}

        protected function _GetSafeVal( $sKey, $arrData, $vDefault = null )
        {
                if ( ! CLib::IsArrayWithKeys( $arrData ) )
                {
                        return $vDefault;
                }
                if ( ! $sKey || empty( $sKey ) )
                {
                        return $vDefault;
                }

                return array_key_exists( $sKey, $arrData ) ? $arrData[ $sKey ] : $vDefault;
        }
	protected function _IsExistsXT( $arrCk = null )
	{
		if ( null == $arrCk )
		{
			$arrCk = $this->m_arrCookie;
		}
		return CLib::IsArrayWithKeys( $arrCk, [ $this->m_sMKeyX, $this->m_sMKeyT ] );
	}

	//
	//	check XT array simply
	//
	protected function _IsValidXTSimple( $arrCk )
	{
		return ( CLib::IsArrayWithKeys( $arrCk, [ $this->m_sMKeyX, $this->m_sMKeyT ] ) &&
			is_array( $arrCk[ $this->m_sMKeyX ] ) &&
			is_array( $arrCk[ $this->m_sMKeyT ] ) );
	}

	//
	//	check XT array in details
	//
	protected function _IsValidXTOverall( $arrCk = null )
        {
		$bRet = false;

		if ( null == $arrCk )
		{
			$arrCk = $this->m_arrCookie;
		}

		if ( CLib::IsArrayWithKeys( $arrCk, [ $this->m_sMKeyX, $this->m_sMKeyT ] ) )
		{
			if ( is_array( $arrCk[ $this->m_sMKeyX ] ) && is_array( $arrCk[ $this->m_sMKeyT ] ) )
			{
				if ( count( $arrCk[ $this->m_sMKeyX ] ) && count( $arrCk[ $this->m_sMKeyT ] ) )
				{
					if ( array_key_exists( self::CKX_UMID, $arrCk[ $this->m_sMKeyX ] ) &&
						array_key_exists( self::CKX_UTYPE, $arrCk[ $this->m_sMKeyX ] ) &&
						array_key_exists( self::CKX_USTATUS, $arrCk[ $this->m_sMKeyX ] ) &&
						array_key_exists( self::CKX_UACT, $arrCk[ $this->m_sMKeyX ] ) &&
						array_key_exists( self::CKT_VER, $arrCk[ $this->m_sMKeyT ] ) &&
						array_key_exists( self::CKT_LOGINTM, $arrCk[ $this->m_sMKeyT ] ) &&
						array_key_exists( self::CKT_REFRESHTM, $arrCk[ $this->m_sMKeyT ] ) &&
						array_key_exists( self::CKT_UPDATETM, $arrCk[ $this->m_sMKeyT ] ) &&
						array_key_exists( self::CKT_KPALIVE, $arrCk[ $this->m_sMKeyT ] ) )
					{
						$bRet = true;
					}
				}
			}
		}

		return $bRet;
        }

	//	check signature
	protected function _IsValidSign( $arrEncryptedCk = null )
	{
		$bRet	= false;
		$arrCk	= [];

		if ( null !== $arrEncryptedCk )
		{
			$arrCk = $this->_DecryptXTArray( $arrEncryptedCk );
		}
		else
		{
			$arrCk = $this->GetXTArray();
		}

		if ( is_array( $arrCk ) )
		{
			if ( array_key_exists( $this->m_sMKeyX, $arrCk ) && is_array( $arrCk[ $this->m_sMKeyX ] ) &&
				array_key_exists( $this->m_sMKeyT, $arrCk ) && is_array( $arrCk[ $this->m_sMKeyT ] ) )
			{
				$sSignDataNow	= $this->GetSignData( $arrCk );
				$sSignDataCk	= $this->_GetSafeVal( self::CKT_CSIGN, $arrCk[ $this->m_sMKeyT ], '' );
				if ( CLib::IsExistingString( $sSignDataNow ) &&
					CLib::IsExistingString( $sSignDataCk ) &&
					CLib::IsCaseSameString( $sSignDataNow, $sSignDataCk ) )
				{
					$bRet = true;
				}
			}
		}

		return $bRet;
	}

	//	check CRC
	protected function _IsValidCRC( $arrEncryptedCk = null )
	{
		//	...
		$bRet	= false;
		$arrCk	= [];

		if ( null !== $arrEncryptedCk )
		{
			$arrCk = $this->_DecryptXTArray( $arrEncryptedCk );
		}
		else
		{
			$arrCk = $this->GetXTArray();
		}

		if ( $this->_IsValidXTSimple( $arrCk ) )
		{
			$nCRCDataNow	= $this->GetCRCData( $arrCk );
			$nCRCDataCk	= $this->_GetSafeVal( self::CKT_CSRC, $arrCk[ $this->m_sMKeyT ], 0 );

			if ( is_numeric( $nCRCDataNow ) &&
				is_numeric( $nCRCDataCk ) &&
				$nCRCDataNow === $nCRCDataCk )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}

	//	if login info has timeout
	protected function _IsSessionTimeout()
	{
		//      ...
		$bRet = false;

		//      ...
		$bValidSession  = true;
		$cSession	= new CUCSession();

		//
		//	Check session via Redis service if T->CKT_REFRESHTM is overdue
		//
		$nRefreshTime = $this->GetXTValue( $this->m_sMKeyT, self::CKT_REFRESHTM );
		if ( time() - $nRefreshTime > 0 )
		{
			//	refresh time is overdue, it's time to check via Redis
			//	...
			$bValidSession = $cSession->IsValidSession();
		}

		//      ...
		if ( $bValidSession )
		{
			if ( $this->IsKeepAlive() )
			{
				//
				//	return false if user set to keep alive
				//
				$bRet = false;
			}
			else
			{
				//	...
				$nLoginTime = $this->GetXTValue( $this->m_sMKeyT, self::CKT_LOGINTM );
				if ( is_numeric( $nLoginTime ) )
				{
					//	escaped time in seconds after user logged in
					//      the default timeout is 1 day.
					$term = floatval( time() - floatval( $nLoginTime ) );
					$bRet = ( $term > $this->m_arrCfg[ self::CFGKEY_STIMEOUT ] );
				}
				else
				{
					//      login time is invalid
					//      So, we marked this session as timeout
					$bRet = true;
				}
			}
		}
		else
		{
			//      session is timeout.
			//      So, we marked this session as timeout
			$bRet = true;
		}

		return $bRet;
	}

	protected function _EncryptXTArray( $arrData )
	{
		if ( ! $this->_IsValidXTOverall( $arrData ) )
		{
			return null;
		}

		//	...
		$arrX = Array
		(
			self::CKX_UMID		=> $this->_GetSafeVal( self::CKX_UMID, $arrData[ $this->m_sMKeyX ], '' ),
			self::CKX_UNICKNAME	=> $this->_GetSafeVal( self::CKX_UNICKNAME, $arrData[ $this->m_sMKeyX ], '' ),
			self::CKX_UTYPE		=> $this->_GetSafeVal( self::CKX_UTYPE, $arrData[ $this->m_sMKeyX ], 0 ),
			self::CKX_UIMGID	=> $this->_GetSafeVal( self::CKX_UIMGID, $arrData[ $this->m_sMKeyX ], '' ),
			self::CKX_USTATUS	=> $this->_GetSafeVal( self::CKX_USTATUS, $arrData[ $this->m_sMKeyX ], 0 ),
			self::CKX_UACT		=> $this->_GetSafeVal( self::CKX_UACT, $arrData[ $this->m_sMKeyX ], 0 ),
			self::CKX_SRC		=> $this->_GetSafeVal( self::CKX_SRC, $arrData[ $this->m_sMKeyX ], '' ),
		);
		$arrT = Array
		(
			self::CKT_VER		=> $this->_GetSafeVal( self::CKT_VER, $arrData[ $this->m_sMKeyT ], '' ),
			self::CKT_LOGINTM	=> $this->_GetSafeVal( self::CKT_LOGINTM, $arrData[ $this->m_sMKeyT ], 0 ),
			self::CKT_REFRESHTM	=> $this->_GetSafeVal( self::CKT_REFRESHTM, $arrData[ $this->m_sMKeyT ], 0 ),
			self::CKT_UPDATETM	=> $this->_GetSafeVal( self::CKT_UPDATETM, $arrData[ $this->m_sMKeyT ], 0 ),
			self::CKT_KPALIVE	=> $this->_GetSafeVal( self::CKT_KPALIVE, $arrData[ $this->m_sMKeyT ], 0 ),
			self::CKT_SMID		=> $this->_GetSafeVal( self::CKT_SMID, $arrData[ $this->m_sMKeyT ], '' ),
			self::CKT_CSIGN		=> $this->_GetSafeVal( self::CKT_CSIGN, $arrData[ $this->m_sMKeyT ], '' ),
			self::CKT_CSRC		=> $this->_GetSafeVal( self::CKT_CSRC, $arrData[ $this->m_sMKeyT ], '' ),
		);

		foreach ( $arrX as $sKey => $sVal )
		{
			$arrX[ $sKey ]	= $this->_Encrypt( $sVal );
		}
		foreach ( $arrT as $sKey => $sVal )
		{
			$arrT[ $sKey ]	= $this->_Encrypt( $sVal );
		}

		//	...
		return Array
		(
			$this->m_sMKeyX	=> $this->_BuildQuery( $arrX ),
			$this->m_sMKeyT	=> $this->_BuildQuery( $arrT ),
		);
	}

	protected function _DecryptXTArray( $arrData )
	{
		if ( ! is_array( $arrData ) ||
			! array_key_exists( $this->m_sMKeyX, $arrData ) || ! array_key_exists( $this->m_sMKeyT, $arrData ) ||
			empty( $arrData[ $this->m_sMKeyX ] ) || empty( $arrData[ $this->m_sMKeyT ] ) )
		{
			return null;
		}

		//      ...
		$sX	= rawurldecode( $this->_GetSafeVal( $this->m_sMKeyX, $arrData, '' ) );
		$sT	= rawurldecode( $this->_GetSafeVal( $this->m_sMKeyT, $arrData, '' ) );
		$arrX	= Array();
		$arrT	= Array();

		try
		{
			//      parse string to array
			parse_str( $sX, $arrPX );
			parse_str( $sT, $arrPT );

			if ( is_array( $arrPX ) && count( $arrPX ) &&
				is_array( $arrPT ) && count( $arrPT ) )
			{
				if ( $this->_IsValidXTOverall( Array( $this->m_sMKeyX => $arrPX, $this->m_sMKeyT => $arrPT ) ) )
				{
					$arrX = Array
					(
						self::CKX_UMID		=> $this->_GetSafeVal( self::CKX_UMID, $arrPX, '' ),
						self::CKX_UNICKNAME	=> $this->_GetSafeVal( self::CKX_UNICKNAME, $arrPX, '' ),
						self::CKX_UTYPE		=> $this->_GetSafeVal( self::CKX_UTYPE, $arrPX, 0 ),
						self::CKX_UIMGID	=> $this->_GetSafeVal( self::CKX_UIMGID, $arrPX, '' ),
						self::CKX_USTATUS	=> $this->_GetSafeVal( self::CKX_USTATUS, $arrPX, 0 ),
						self::CKX_UACT		=> $this->_GetSafeVal( self::CKX_UACT, $arrPX, 0 ),
						self::CKX_SRC		=> $this->_GetSafeVal( self::CKX_SRC, $arrPX, '' ),
					);
					$arrT = Array
					(
						self::CKT_VER		=> $this->_GetSafeVal( self::CKT_VER, $arrPT, '' ),
						self::CKT_LOGINTM	=> $this->_GetSafeVal( self::CKT_LOGINTM, $arrPT, 0 ),
						self::CKT_REFRESHTM	=> $this->_GetSafeVal( self::CKT_REFRESHTM, $arrPT, 0 ),
						self::CKT_UPDATETM	=> $this->_GetSafeVal( self::CKT_UPDATETM, $arrPT, 0 ),
						self::CKT_KPALIVE	=> $this->_GetSafeVal( self::CKT_KPALIVE, $arrPT, 0 ),
						self::CKT_SMID		=> $this->_GetSafeVal( self::CKT_SMID, $arrPT, '' ),
						self::CKT_CSIGN		=> $this->_GetSafeVal( self::CKT_CSIGN, $arrPT, '' ),
						self::CKT_CSRC		=> $this->_GetSafeVal( self::CKT_CSRC, $arrPT, 0 ),
					);

					unset( $arrPX );
					unset( $arrPT );

					foreach ( $arrX as $sKey => $sVal )
					{
						$arrX[ $sKey ]	= $this->_Decrypt( $sVal );
					}
					foreach ( $arrT as $sKey => $sVal )
					{
						$arrT[ $sKey ]	= $this->_Decrypt( $sVal );
					}

					//
					//      type converting
					//
					$arrX[ self::CKX_UTYPE ]        = intval( $arrX[ self::CKX_UTYPE ] );
					$arrX[ self::CKX_USTATUS ]      = intval( $arrX[ self::CKX_USTATUS ] );
					$arrX[ self::CKX_UACT ]         = intval( $arrX[ self::CKX_UACT ] );

					$arrT[ self::CKT_LOGINTM ]      = intval( $arrT[ self::CKT_LOGINTM ] );
					$arrT[ self::CKT_REFRESHTM ]    = intval( $arrT[ self::CKT_REFRESHTM ] );
					$arrT[ self::CKT_UPDATETM ]     = intval( $arrT[ self::CKT_UPDATETM ] );
					$arrT[ self::CKT_KPALIVE ]      = intval( $arrT[ self::CKT_KPALIVE ] );
					$arrT[ self::CKT_CSRC ]         = intval( $arrT[ self::CKT_CSRC ] );
				}
			}
		}
		catch ( \Exception $e )
		{
		}

		//	...
		return Array
		(
			$this->m_sMKeyX	=> $arrX,
			$this->m_sMKeyT	=> $arrT,
		);
	}

	protected function _Decrypt( $vData )
	{
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
                $sString = strval( $vData );
                return rawurldecode( str_rot13( $sString ) );
        }
        protected function _Encrypt( $vData )
        {
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
                $sString = strval( $vData );
                return str_rot13( rawurlencode( $sString ) );
        }
        protected function _DecryptBase64( $vData )
        {
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
                $sString = strval( $vData );
                return rawurldecode( base64_decode( str_rot13( $sString ) ) );
        }
        protected function _EcnryptBase64( $vData )
        {
		if ( ! is_string( $vData ) && ! is_numeric( $vData ) )
		{
			return '';
		}
                $sString = strval( $vData );
                return str_rot13( base64_encode( rawurlencode( $sString ) ) );
        }

        protected function _SetCookieForLogin( $arrCookie, $bKeepAlive, & $sCkString = '' )
        {
                if ( ! is_array( $arrCookie ) ||
                        ! array_key_exists( $this->m_sMKeyX, $arrCookie ) ||
                        ! array_key_exists( $this->m_sMKeyT, $arrCookie ) ||
                        ! is_string( $arrCookie[ $this->m_sMKeyX ] ) ||
                        ! is_string( $arrCookie[ $this->m_sMKeyT ] ) )
                {
                        return false;
                }

                //	...
                //	set the expire date as 1 year.
                //	the browser will keep this cookie for 1 year.
                //
                $tmExpire = ( $bKeepAlive ? ( time() + 365 * 24 * 60 * 60 ) : 0 );
                return $this->_SetCookie( $arrCookie, $tmExpire, $sCkString );
        }
        protected function _SetCookieForLogout()
        {
                $arrCookie	= Array( $this->m_sMKeyX => '', $this->m_sMKeyT => '' );
                $tmExpire	= time() - 365 * 24 * 60 * 60;
                return $this->_SetCookie( $arrCookie, $tmExpire );
        }
        protected function _SetCookie( $arrCookie, $tmExpire, & $sCkString = '' )
        {
                if ( ! is_array( $arrCookie ) ||
                        ! array_key_exists( $this->m_sMKeyX, $arrCookie ) ||
                        ! array_key_exists( $this->m_sMKeyT, $arrCookie ) ||
                        ! is_string( $arrCookie[ $this->m_sMKeyX ] ) ||
                        ! is_string( $arrCookie[ $this->m_sMKeyT ] ) )
                {
                        return false;
                }
		if ( ! is_numeric( $tmExpire ) )
		{
			return false;
		}

                //	...
                $sDomain	= $this->_GetCookieDomain();
                $sPath		= $this->m_arrCfg[ self::CFGKEY_PATH ];
                $sXValue        = rawurlencode( $arrCookie[ $this->m_sMKeyX ] );
                $sTValue        = rawurlencode( $arrCookie[ $this->m_sMKeyT ] );
                $sCkString      = http_build_query( Array( $this->m_sMKeyX => $sXValue, $this->m_sMKeyT => $sTValue ), '', '; ' );

                //	...
                if ( $this->m_arrCfg[ self::CFGKEY_HTTPONLY ] && $this->_IsSupportedSetHttpOnly() )
                {
                        setcookie( $this->m_sMKeyX, $sXValue, $tmExpire, $sPath, $sDomain, $this->m_arrCfg[ self::CFGKEY_SECURE ], true );
                        setcookie( $this->m_sMKeyT, $sTValue, $tmExpire, $sPath, $sDomain, $this->m_arrCfg[ self::CFGKEY_SECURE ], true );
                }
                else
                {
                        setcookie( $this->m_sMKeyX, $sXValue, $tmExpire, $sPath, $sDomain );
                        setcookie( $this->m_sMKeyT, $sTValue, $tmExpire, $sPath, $sDomain );
                }

                return true;
        }

        protected function _GetDigestSource( $arrData )
        {
		if ( ! $this->_IsValidXTSimple( $arrData ) )
		{
			return '';
		}

                //
                //	prevent all of the following fields from tampering
                //
                $sRet = "" .
                        $this->_GetSafeVal( self::CKX_UMID, $arrData[ $this->m_sMKeyX ], '' ) . "-" .
                        strval( $this->_GetSafeVal( self::CKX_UTYPE, $arrData[ $this->m_sMKeyX ], 0 ) ) . "-" .
                        strval( $this->_GetSafeVal( self::CKX_USTATUS, $arrData[ $this->m_sMKeyX ], 0 ) ) . "-" .
                        strval( $this->_GetSafeVal( self::CKX_UACT, $arrData[ $this->m_sMKeyX ], 0 ) ) . "-" .
                        $this->_GetSafeVal( self::CKX_SRC, $arrData[ $this->m_sMKeyX ], '' ) .
                        "---" .
                        $this->_GetSafeVal( self::CKT_VER, $arrData[ $this->m_sMKeyT ], '' ) . "-" .
                        strval( $this->_GetSafeVal( self::CKT_LOGINTM, $arrData[ $this->m_sMKeyT ], 0 ) ) . "-" .
                        strval( $this->_GetSafeVal( self::CKT_REFRESHTM, $arrData[ $this->m_sMKeyT ], 0 ) ) . "-" .
                        strval( $this->_GetSafeVal( self::CKT_UPDATETM, $arrData[ $this->m_sMKeyT ], 0 ) ) . "-" .
                        strval( $this->_GetSafeVal( self::CKT_KPALIVE, $arrData[ $this->m_sMKeyT ], 0 ) ) . "-" .
                        $this->_GetSafeVal( self::CKT_SMID, $arrData[ $this->m_sMKeyT ], '' );

                //	...
                return $sRet;
        }

        protected function _BuildQuery( $arrParams )
        {
                $sRet = '';
                $arrPairs       = Array();

                if ( is_array( $arrParams ) )
                {
                        foreach ( $arrParams as $key => $value )
                        {
                                $arrPairs[] = $key . '=' . $value;
                        }
                        $sRet = implode( '&', $arrPairs );
                }
                else
                {
                        $sRet = $arrParams;
                }

                return $sRet;
        }
        protected function _GetCookieDomain()
        {
                return ( array_key_exists( self::CFGKEY_DOMAIN, $this->m_arrCfg ) ? $this->m_arrCfg[ self::CFGKEY_DOMAIN ] : '' );
        }

        protected function _IsSupportedSetHttpOnly()
        {
                //	set http-only for cookie is supported
                return version_compare( phpversion(), '5.2.0', '>=' );
        }

}

?>