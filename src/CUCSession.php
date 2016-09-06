<?php

namespace dekuan\deuclient;

/**
 *      class of CUCSession
 */
class CUCSession
{
        //	statics
        protected static $g_cStaticInstance;

        //      error id
        const ERR_SESSION_PWDCHGED	= 2000;	//	password was changed
        const ERR_SESSION_INVALID	= 2001;	//	invalid checksum
        const ERR_SESSION_BADSIGN	= 2002;	//	bad sign


        public function __construct()
        {
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
        //      Save session to remote redis server
        //      by the key u_mid
        //
        public function SaveSession( $arrData )
        {
                return true;
        }

        //
        //	check session from remote redis server
        //      by key u_mid
        //
        public function IsValidSession()
        {
                return true;
        }

}





?>