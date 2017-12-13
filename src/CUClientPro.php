<?php

namespace dekuan\deuclient;

use dekuan\delib\CLib;


/**
 *	CUClientPro
 */
class CUClientPro
{
	//	statics instance
	protected static $g_cStaticInstance;

	//
	//	keys for cookie
	//
	const CKX               = 'X';
	const CKT               = 'T';

	const CKX_MID		= 'mid';	//	string	- user mid ( a string with length of 32/64 characters )
	const CKX_NICKNAME	= 'nkn';	//	string	- user nick name
	const CKX_TYPE		= 't';		//	int	- user type, values( NORMAL, TEMP, ... )
	const CKX_AVATAR	= 'avatar';	//	string	- the mid of user avatar
	const CKX_STATUS	= 'sts';	//	int	- user status
	const CKX_ACTION	= 'act';	//	int	- user action
	const CKX_SRC		= 'src';	//	string	- the source which a user logged on from
	const CKX_DIGEST	= 'digest';	//	string	- message digest calculation

	const CKT_VER		= 'v';		//	string	- cookie version
	const CKT_LOGIN_TM	= 'ltm';	//	int	- login time, unix time stamp in timezone 0.
	const CKT_REFRESH_TM	= 'rtm';	//	int	- last refresh time
	const CKT_UPDATE_TM	= 'utm';	//	int	- last update time
	const CKT_KP_ALIVE	= 'kpa';	//	int	- keep alive, values( YES, NO )
	const CKT_SS_MID	= 'smid';	//	string	- session mid
	const CKT_CKS_SIGN	= 'css';	//	string	- checksum sign
	const CKT_CKS_CRC	= 'csc';	//	string	- checksum crc

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
	const DEFAULT_SS_TIMEOUT	= 86400;	//	session timeout, default is 1 day.

	//
	//	config values
	//
	const CONFIG_TIME_SECONDS_YEAR	= 365 * 24 * 60 * 60;


	//
	//	error ids
	//
	const ERR_UNKNOWN		= -1;		//	unknown error
	const ERR_SUCCESS		= 0;		//	successfully
	const ERR_FAILURE		= -1000;	//      failed
	const ERR_PARAMETER		= -1001;	//      error in parameter
	const ERR_INVALID_XT_COOKIE	= -1002;	//	invalid XT cookie
	const ERR_INVALID_CRC		= -1003;	//	invalid CRC
	const ERR_INVALID_SIGN		= -1004;	//	invalid sign
	const ERR_LOGIN_TIMEOUT		= -1005;	//	login timeout
	const ERR_BAD_COOKIE		= -1006;	//	bad cookie
	const ERR_ENCRYPT_XT		= -1007;	//      failed to encrypt xt
	const ERR_SET_COOKIE		= -1008;	//      failed to set cookie
	const ERR_PARSE_COOKIE_STRING	= -1009;	//	failed to parse cookie string
	const ERR_RESET_COOKIE		= -1010;	//	failed to reset cookie

	//
	//      user status
	//
	const STATUS_UNVERIFIED		= 0;	//	unverified
	const STATUS_OKAY		= 1;	//	okay
	const STATUS_DELETED		= 2;	//	deleted
	const STATUS_EXPIRED		= 3;	//	expired
	const STATUS_DENIED		= 4;	//	denied
	const STATUS_COMPLETE		= 5;	//	complete
	const STATUS_ABORT		= 6;	//	abort
	const STATUS_PENDING		= 7;	//	pending
	const STATUS_ACCEPTED		= 8;	//	accepted
	const STATUS_REJECTED		= 9;	//	rejected
	const STATUS_ARCHIVED		= 10;	//	archived

	//
	//	cookie information
	//
	const COOKIE_VERSION            = '1.0.1.1000';

	
	//
	//	$_COOKIE or parsed from sCookieString
	//
	protected $m_arrCookie		= [];

	//
	//	configuration
	//
	protected $m_arrCfg		= [];

	//
	//	cache
	//
	protected $m_bIsLoggedIn	= null;
	
	


        public function __construct()
        {
		$this->m_arrCookie	= ( is_array( $_COOKIE ) ? $_COOKIE : [] );
                $this->m_arrCfg		=
                [
                        self::CFGKEY_DOMAIN	=> self::DEFAULT_DOMAIN,
                        self::CFGKEY_PATH	=> self::DEFAULT_PATH,
                        self::CFGKEY_SEED	=> self::DEFAULT_SIGN_SEED,	//	seed
                        self::CFGKEY_SECURE	=> self::DEFAULT_SECURE,
                        self::CFGKEY_HTTPONLY	=> self::DEFAULT_HTTPONLY,
                        self::CFGKEY_STIMEOUT	=> self::DEFAULT_SS_TIMEOUT,	//	session timeout, default is 1 day.
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

        //
        //	make user login
        //
        public function MakeLogin( $vData, $bKeepAlive = false, & $sCkString = '' )
        {
		if ( CLib::IsArrayWithKeys( $vData, [ self::CKX, self::CKT ] ) )
		{
			return $this->_MakeLoginByData( $vData, $bKeepAlive, $sCkString );
		}
		else
		{
			return $this->_MakeLoginByCookieString( $vData, $bKeepAlive );
		}
	}
	public function MakeLoginWithCookieString( $sCkString )
	{

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
                if ( null !== $this->m_bIsLoggedIn )
                {
                        //	have already checked
                        return ( $this->m_bIsLoggedIn ? self::ERR_SUCCESS : self::ERR_FAILURE );
                }

                //	...
                if ( $this->IsExistsXT() )
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
                $this->m_bIsLoggedIn = ( self::ERR_SUCCESS == $nRet );

                //	...
                return $nRet;
        }

	//
	//	reset cookie via cookie string
	//
	public function ResetCookie( $sCkString )
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
		$arrEncryptedCookie	= $this->GetEncryptedXTArray( $sCkString );
		$nErrorId		= $this->_CheckEncryptedXTArray( $arrEncryptedCookie );
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

        public function IsExistsXT( $arrCk = null )
        {
		if ( null == $arrCk )
		{
			$arrCk = $this->m_arrCookie;
		}
		return CLib::IsArrayWithKeys( $arrCk, [ self::CKX, self::CKT ] );
        }

        public function GetCookieString()
        {
		$sRet		= '';
                $arrDecryptedXT	= $this->GetEncryptedXTArray();
		if ( CLib::IsArrayWithKeys( $arrDecryptedXT, [ self::CKX, self::CKT ] ) )
		{
			$sRet = http_build_query
			(
				[
					self::CKX => $arrDecryptedXT[ self::CKX ],
					self::CKT => $arrDecryptedXT[ self::CKT ]
				],
				'', '; '
			);
		}

		return $sRet;
        }

	public function GetEncryptedXTArray( $sCkString = null )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			//
			//	get XT array from cookie
			//
			return Array
			(
				self::CKX => $this->_GetSafeVal( self::CKX, $this->m_arrCookie, '' ),
				self::CKT => $this->_GetSafeVal( self::CKT, $this->m_arrCookie, '' ),
			);
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
				if ( empty( $sX ) && array_key_exists( self::CKX, $arrCk0 ) )
				{
					$sX = $arrCk0[ self::CKX ];
				}
				else if ( empty( $sT ) && array_key_exists( self::CKT, $arrCk0 ) )
				{
					$sT = $arrCk0[ self::CKT ];
				}
			}
			if ( is_array( $arrCk1 ) )
			{
				if ( empty( $sX ) && array_key_exists( self::CKX, $arrCk1 ) )
				{
					$sX = $arrCk1[ self::CKX ];
				}
				else if ( empty( $sT ) && array_key_exists( self::CKT, $arrCk1 ) )
				{
					$sT = $arrCk1[ self::CKT ];
				}
			}

			//
			//	put the values to cookie
			//
			if ( is_string( $sX ) && strlen( $sX ) &&
				is_string( $sT ) && strlen( $sT ) )
			{
				$arrRet[ self::CKX ]	= $sX;
				$arrRet[ self::CKT ]	= $sT;
			}
		}

		return $arrRet;
	}
	
	public function GetXTArray()
	{
		return $this->_DecryptXTArray( $this->GetEncryptedXTArray() );
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
                $nKeepAlive = $this->GetXTValue( self::CKT, self::CKT_KP_ALIVE );
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

	protected function _MakeLoginByData( $arrData, $bKeepAlive = false, & $sCkString = '' )
	{
		//
		//	arrData		- [in] Array
		//	(
		//		'X'	=> Array
		//		(
		//			'mid'		=> '101101aaefe12342aaefe12342aaefe12342',
		//			'nkn'		=> '',
		//			't'		=> 0,
		//			'imgid'		=> '',
		//			'act'		=> 0,
		//			'src'		=> '',
		//			'digest'	=> '',
		//		)
		//		'T'	=> Array
		//		(
		//			'v'		=> '',
		//			'ltm'		=> 0,
		//			'rtm'		=> 0,
		//			'utm'		=> 0,
		//			'kpa'		=> 1,
		//			...
		//		)
		//	)
		//	bKeepAlive	- [in] keep alive
		//      sCkString       - [out] a string contains the full XT cookie
		//	RETURN		- self::ERR_SUCCESS successfully, otherwise error id
		//
		if ( ! CLib::IsArrayWithKeys( $arrData, [ self::CKX, self::CKT ] ) )
		{
			return self::ERR_PARAMETER;
		}
		if ( ! CLib::IsArrayWithKeys( $arrData[ self::CKX ] ) ||
			! CLib::IsArrayWithKeys( $arrData[ self::CKT ] ) )
		{
			return self::ERR_PARAMETER;
		}
		if ( ! array_key_exists( self::CKX_MID, $arrData[ self::CKX ] ) ||
			! array_key_exists( self::CKX_TYPE, $arrData[ self::CKX ] ) ||
			! array_key_exists( self::CKX_STATUS, $arrData[ self::CKX ] ) ||
			! array_key_exists( self::CKX_ACTION, $arrData[ self::CKX ] ) ||
			! array_key_exists( self::CKT_LOGIN_TM, $arrData[ self::CKT ] ) ||
			! array_key_exists( self::CKT_REFRESH_TM, $arrData[ self::CKT ] ) ||
			! array_key_exists( self::CKT_UPDATE_TM, $arrData[ self::CKT ] ) )
		{
			return self::ERR_PARAMETER;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		//
		//      make signature and crc checksum
		//
		$arrData[ self::CKT ][ self::CKT_KP_ALIVE ]	= ( $bKeepAlive ? 1 : 0 );
		$arrData[ self::CKT ][ self::CKT_VER ]		= self::COOKIE_VERSION;
		$arrData[ self::CKT ][ self::CKT_CKS_SIGN ]	= $this->GetSignData( $arrData );
		$arrData[ self::CKT ][ self::CKT_CKS_CRC ]	= $this->GetCRCData( $arrData );

		//	...
		$arrEncryptedCk = $this->_EncryptXTArray( $arrData );
		if ( CLib::IsArrayWithKeys( $arrEncryptedCk, [ self::CKX, self::CKT ] ) )
		{
			if ( $this->_SetCookieForLogin( $arrEncryptedCk, $bKeepAlive, $sCkString ) )
			{
				$nRet = self::ERR_SUCCESS;
			}
			else
			{
				$nRet = self::ERR_SET_COOKIE;
			}
		}
		else
		{
			$nRet = self::ERR_ENCRYPT_XT;
		}

		//	...
		return $nRet;
	}
	protected function _MakeLoginByCookieString( $sCookieString, $bKeepAlive = false )
	{
		if ( ! CLib::IsExistingString( $sCookieString ) )
		{
			return self::ERR_PARAMETER;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		//	...
		$bKeepAlive	= boolval( $bKeepAlive );
		$arrEncryptedCk	= $this->GetEncryptedXTArray( $sCookieString );
		$nErrorId	= $this->_CheckEncryptedXTArray( $arrEncryptedCk );

		if ( self::ERR_SUCCESS == $nErrorId )
		{
			$arrCookie = $this->_DecryptXTArray( $arrEncryptedCk );
			if ( CLib::IsArrayWithKeys( $arrCookie, self::CKT ) &&
				CLib::IsArrayWithKeys( $arrCookie[ self::CKT ], self::CKT_KP_ALIVE ) )
			{
				if ( self::ERR_SUCCESS == $this->ResetCookie( $sCookieString ) )
				{
					if ( $this->_SetCookieForLogin( $arrEncryptedCk, $bKeepAlive ) )
					{
						$nRet = self::ERR_SUCCESS;
					}
					else
					{
						$nRet = self::ERR_SET_COOKIE;
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


	protected function _CheckCookieString( $sCkString )
	{
		if ( ! CLib::IsExistingString( $sCkString ) )
		{
			return self::ERR_PARAMETER;
		}
		return $this->_CheckEncryptedXTArray( $this->GetEncryptedXTArray( $sCkString ) );
	}

	protected function _CheckEncryptedXTArray( $arrEncryptedCookie )
	{
		if ( ! is_array( $arrEncryptedCookie ) ||
			! array_key_exists( self::CKX, $arrEncryptedCookie ) ||
			! array_key_exists( self::CKT, $arrEncryptedCookie ) )
		{
			return self::ERR_INVALID_XT_COOKIE;
		}

		//	...
		$nRet = self::ERR_UNKNOWN;

		if ( $this->IsExistsXT( $arrEncryptedCookie ) )
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


	protected function _ResetCookieByEncryptedXTArray( $arrEncryptedCk )
	{
		if ( ! CLib::IsArrayWithKeys( $arrEncryptedCk, [ self::CKX, self::CKT ] ) )
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
		$this->m_arrCookie[ self::CKX ]	= $arrEncryptedCk[ self::CKX ];
		$this->m_arrCookie[ self::CKT ]	= $arrEncryptedCk[ self::CKT ];

		return self::ERR_SUCCESS;
	}

        protected function _GetSafeVal( $sKey, $arrData, $vDefault = null )
        {
                if ( ! CLib::IsArrayWithKeys( $arrData ) )
                {
                        return $vDefault;
                }
                if ( ! CLib::IsExistingString( $sKey ) )
                {
                        return $vDefault;
                }

                return array_key_exists( $sKey, $arrData ) ? $arrData[ $sKey ] : $vDefault;
        }


	//
	//	check XT array simply
	//
	protected function _IsValidXTSimple( $arrCk )
	{
		return ( CLib::IsArrayWithKeys( $arrCk, [ self::CKX, self::CKT ] ) &&
			is_array( $arrCk[ self::CKX ] ) &&
			is_array( $arrCk[ self::CKT ] ) );
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

		if ( CLib::IsArrayWithKeys( $arrCk, [ self::CKX, self::CKT ] ) )
		{
			if ( is_array( $arrCk[ self::CKX ] ) && is_array( $arrCk[ self::CKT ] ) )
			{
				if ( count( $arrCk[ self::CKX ] ) && count( $arrCk[ self::CKT ] ) )
				{
					if ( array_key_exists( self::CKX_MID, $arrCk[ self::CKX ] ) &&
						array_key_exists( self::CKX_TYPE, $arrCk[ self::CKX ] ) &&
						array_key_exists( self::CKX_STATUS, $arrCk[ self::CKX ] ) &&
						array_key_exists( self::CKX_ACTION, $arrCk[ self::CKX ] ) )
					{
						if ( array_key_exists( self::CKT_VER, $arrCk[ self::CKT ] ) &&
							array_key_exists( self::CKT_LOGIN_TM, $arrCk[ self::CKT ] ) &&
							array_key_exists( self::CKT_REFRESH_TM, $arrCk[ self::CKT ] ) &&
							array_key_exists( self::CKT_UPDATE_TM, $arrCk[ self::CKT ] ) &&
							array_key_exists( self::CKT_KP_ALIVE, $arrCk[ self::CKT ] ) )
						{
							$bRet = true;	
						}
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

		if ( CLib::IsArrayWithKeys( $arrCk, [ self::CKX, self::CKT ] ) )
		{
			if ( is_array( $arrCk[ self::CKX ] ) &&
				is_array( $arrCk[ self::CKT ] ) )
			{
				$sSignDataNow	= $this->GetSignData( $arrCk );
				$sSignDataCk	= $this->_GetSafeVal( self::CKT_CKS_SIGN, $arrCk[ self::CKT ], '' );
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
			$nCRCDataCk	= $this->_GetSafeVal( self::CKT_CKS_CRC, $arrCk[ self::CKT ], 0 );

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
		//	Check session via service if T->CKT_REFRESH_TM is timeout
		//
		$nRefreshTime = $this->GetXTValue( self::CKT, self::CKT_REFRESH_TM );
		if ( time() - $nRefreshTime > 0 )
		{
			//	refresh time is timeout, it's time to check via Redis
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
				$nLoginTime = $this->GetXTValue( self::CKT, self::CKT_LOGIN_TM );
				if ( is_numeric( $nLoginTime ) )
				{
					//
					//	escaped time in seconds after user logged in
					//      the default timeout is 1 day.
					//
					$fTerm	= floatval( time() - floatval( $nLoginTime ) );
					$bRet	= ( $fTerm > $this->m_arrCfg[ self::CFGKEY_STIMEOUT ] );
				}
				else
				{
					//
					//      login time is invalid
					//      So, we marked this session as timeout
					//
					$bRet = true;
				}
			}
		}
		else
		{
			//
			//      session is timeout.
			//      So, we marked this session as timeout
			//
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
			self::CKX_MID		=> $this->_GetSafeVal( self::CKX_MID, $arrData[ self::CKX ], '' ),
			self::CKX_NICKNAME	=> $this->_GetSafeVal( self::CKX_NICKNAME, $arrData[ self::CKX ], '' ),
			self::CKX_TYPE		=> $this->_GetSafeVal( self::CKX_TYPE, $arrData[ self::CKX ], 0 ),
			self::CKX_AVATAR	=> $this->_GetSafeVal( self::CKX_AVATAR, $arrData[ self::CKX ], '' ),
			self::CKX_STATUS	=> $this->_GetSafeVal( self::CKX_STATUS, $arrData[ self::CKX ], 0 ),
			self::CKX_ACTION	=> $this->_GetSafeVal( self::CKX_ACTION, $arrData[ self::CKX ], 0 ),
			self::CKX_SRC		=> $this->_GetSafeVal( self::CKX_SRC, $arrData[ self::CKX ], '' ),
			self::CKX_DIGEST	=> $this->_GetSafeVal( self::CKX_DIGEST, $arrData[ self::CKX ], '' ),
		);
		$arrT = Array
		(
			self::CKT_VER		=> $this->_GetSafeVal( self::CKT_VER, $arrData[ self::CKT ], '' ),
			self::CKT_LOGIN_TM	=> $this->_GetSafeVal( self::CKT_LOGIN_TM, $arrData[ self::CKT ], 0 ),
			self::CKT_REFRESH_TM	=> $this->_GetSafeVal( self::CKT_REFRESH_TM, $arrData[ self::CKT ], 0 ),
			self::CKT_UPDATE_TM	=> $this->_GetSafeVal( self::CKT_UPDATE_TM, $arrData[ self::CKT ], 0 ),
			self::CKT_KP_ALIVE	=> $this->_GetSafeVal( self::CKT_KP_ALIVE, $arrData[ self::CKT ], 0 ),
			self::CKT_SS_MID	=> $this->_GetSafeVal( self::CKT_SS_MID, $arrData[ self::CKT ], '' ),
			self::CKT_CKS_SIGN	=> $this->_GetSafeVal( self::CKT_CKS_SIGN, $arrData[ self::CKT ], '' ),
			self::CKT_CKS_CRC	=> $this->_GetSafeVal( self::CKT_CKS_CRC, $arrData[ self::CKT ], '' ),
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
			self::CKX	=> $this->_BuildQuery( $arrX ),
			self::CKT	=> $this->_BuildQuery( $arrT ),
		);
	}

	protected function _DecryptXTArray( $arrData )
	{
		if ( ! is_array( $arrData ) ||
			! array_key_exists( self::CKX, $arrData ) || ! array_key_exists( self::CKT, $arrData ) ||
			empty( $arrData[ self::CKX ] ) || empty( $arrData[ self::CKT ] ) )
		{
			return null;
		}

		//      ...
		$sX	= rawurldecode( $this->_GetSafeVal( self::CKX, $arrData, '' ) );
		$sT	= rawurldecode( $this->_GetSafeVal( self::CKT, $arrData, '' ) );
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
				if ( $this->_IsValidXTOverall( Array( self::CKX => $arrPX, self::CKT => $arrPT ) ) )
				{
					$arrX = Array
					(
						self::CKX_MID		=> $this->_GetSafeVal( self::CKX_MID, $arrPX, '' ),
						self::CKX_NICKNAME	=> $this->_GetSafeVal( self::CKX_NICKNAME, $arrPX, '' ),
						self::CKX_TYPE		=> $this->_GetSafeVal( self::CKX_TYPE, $arrPX, 0 ),
						self::CKX_AVATAR	=> $this->_GetSafeVal( self::CKX_AVATAR, $arrPX, '' ),
						self::CKX_STATUS	=> $this->_GetSafeVal( self::CKX_STATUS, $arrPX, 0 ),
						self::CKX_ACTION	=> $this->_GetSafeVal( self::CKX_ACTION, $arrPX, 0 ),
						self::CKX_SRC		=> $this->_GetSafeVal( self::CKX_SRC, $arrPX, '' ),
						self::CKX_DIGEST	=> $this->_GetSafeVal( self::CKX_DIGEST, $arrPX, '' ),
					);
					$arrT = Array
					(
						self::CKT_VER		=> $this->_GetSafeVal( self::CKT_VER, $arrPT, '' ),
						self::CKT_LOGIN_TM	=> $this->_GetSafeVal( self::CKT_LOGIN_TM, $arrPT, 0 ),
						self::CKT_REFRESH_TM	=> $this->_GetSafeVal( self::CKT_REFRESH_TM, $arrPT, 0 ),
						self::CKT_UPDATE_TM	=> $this->_GetSafeVal( self::CKT_UPDATE_TM, $arrPT, 0 ),
						self::CKT_KP_ALIVE	=> $this->_GetSafeVal( self::CKT_KP_ALIVE, $arrPT, 0 ),
						self::CKT_SS_MID	=> $this->_GetSafeVal( self::CKT_SS_MID, $arrPT, '' ),
						self::CKT_CKS_SIGN	=> $this->_GetSafeVal( self::CKT_CKS_SIGN, $arrPT, '' ),
						self::CKT_CKS_CRC	=> $this->_GetSafeVal( self::CKT_CKS_CRC, $arrPT, 0 ),
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
					$arrX[ self::CKX_TYPE ]		= intval( $arrX[ self::CKX_TYPE ] );
					$arrX[ self::CKX_STATUS ]	= intval( $arrX[ self::CKX_STATUS ] );
					$arrX[ self::CKX_ACTION ]	= intval( $arrX[ self::CKX_ACTION ] );

					$arrT[ self::CKT_LOGIN_TM ]	= intval( $arrT[ self::CKT_LOGIN_TM ] );
					$arrT[ self::CKT_REFRESH_TM ]	= intval( $arrT[ self::CKT_REFRESH_TM ] );
					$arrT[ self::CKT_UPDATE_TM ]	= intval( $arrT[ self::CKT_UPDATE_TM ] );
					$arrT[ self::CKT_KP_ALIVE ]	= intval( $arrT[ self::CKT_KP_ALIVE ] );
					$arrT[ self::CKT_CKS_CRC ]	= intval( $arrT[ self::CKT_CKS_CRC ] );
				}
			}
		}
		catch ( \Exception $e )
		{
		}

		//	...
		return Array
		(
			self::CKX	=> $arrX,
			self::CKT	=> $arrT,
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
        protected function _EncryptBase64( $vData )
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
                        ! array_key_exists( self::CKX, $arrCookie ) ||
                        ! array_key_exists( self::CKT, $arrCookie ) ||
                        ! is_string( $arrCookie[ self::CKX ] ) ||
                        ! is_string( $arrCookie[ self::CKT ] ) )
                {
                        return false;
                }

                //
                //	set the expire date as 1 year.
                //	the browser will keep this cookie for 1 year.
                //
                $tmExpire = ( $bKeepAlive ? ( time() + self::CONFIG_TIME_SECONDS_YEAR ) : 0 );
                return $this->_SetCookie( $arrCookie, $tmExpire, $sCkString );
        }
        protected function _SetCookieForLogout()
        {
                $arrCookie	= Array( self::CKX => '', self::CKT => '' );
                $tmExpire	= time() - self::CONFIG_TIME_SECONDS_YEAR;
                return $this->_SetCookie( $arrCookie, $tmExpire );
        }
        protected function _SetCookie( $arrCookie, $tmExpire, & $sCkString = '' )
        {
                if ( ! is_array( $arrCookie ) ||
                        ! array_key_exists( self::CKX, $arrCookie ) ||
                        ! array_key_exists( self::CKT, $arrCookie ) ||
                        ! is_string( $arrCookie[ self::CKX ] ) ||
                        ! is_string( $arrCookie[ self::CKT ] ) )
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
                $sXValue        = rawurlencode( $arrCookie[ self::CKX ] );
                $sTValue        = rawurlencode( $arrCookie[ self::CKT ] );
                $sCkString      = http_build_query( Array( self::CKX => $sXValue, self::CKT => $sTValue ), '', '; ' );

                //	...
                if ( $this->m_arrCfg[ self::CFGKEY_HTTPONLY ] && $this->_IsSupportedSetHttpOnly() )
                {
                        setcookie( self::CKX, $sXValue, $tmExpire, $sPath, $sDomain, $this->m_arrCfg[ self::CFGKEY_SECURE ], true );
                        setcookie( self::CKT, $sTValue, $tmExpire, $sPath, $sDomain, $this->m_arrCfg[ self::CFGKEY_SECURE ], true );
                }
                else
                {
                        setcookie( self::CKX, $sXValue, $tmExpire, $sPath, $sDomain );
                        setcookie( self::CKT, $sTValue, $tmExpire, $sPath, $sDomain );
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
		strval( $this->_GetSafeVal( self::CKX_MID, $arrData[ self::CKX ], '' ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKX_TYPE, $arrData[ self::CKX ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKX_STATUS, $arrData[ self::CKX ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKX_ACTION, $arrData[ self::CKX ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKX_SRC, $arrData[ self::CKX ], '' ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKX_DIGEST, $arrData[ self::CKX ], '' ) ) . "-" .
		"---" .
		strval( $this->_GetSafeVal( self::CKT_VER, $arrData[ self::CKT ], '' ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKT_LOGIN_TM, $arrData[ self::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKT_REFRESH_TM, $arrData[ self::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKT_UPDATE_TM, $arrData[ self::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKT_KP_ALIVE, $arrData[ self::CKT ], 0 ) ) . "-" .
		strval( $this->_GetSafeVal( self::CKT_SS_MID, $arrData[ self::CKT ], '' ) );

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