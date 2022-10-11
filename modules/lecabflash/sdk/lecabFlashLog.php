<?php

class LecabFlashLog
{
    const DEBUG = 1;
    const INFO = 2;
    const WARNING = 5;
    const ERROR = 6;
    const FATAL = 9;

    protected static $handler = null;
    private static $level = null;
    
    private static $severity_codes = array(
        'DEBUG'=>   self::DEBUG,
        'INFO'=>    self::INFO,
        'WARNING'=> self::WARNING,
        'ERROR'=>   self::ERROR,
        'FATAL'=>   self::FATAL,
    );

   
    public static function init($severity,$handler) {
        self::$handler = $handler;
        self::$level = self::$severity_codes[$severity];
    }


    private static function _log($severity, $msg)
    {
        try {
            if (self::$handler) {
                if (self::$level) {
                    $level = self::$severity_codes[$severity];
                    if ($level >= self::$level) {
                        if (is_object($msg))
                            $msg = (array)$msg;
                        if (is_array($msg))
                            $msg = print_r($msg,true);
                        if (is_string($msg)) {
                            call_user_func( self::$handler, $severity, $msg );   
                        } 
                    }
                }
            }
        } catch(Exception $e){
            // skip
        }
    }

	public static function debug($msg)
	{
        self::_log('DEBUG',$msg);
	}

	public static function info($msg)
	{
        self::_log('INFO',$msg);
	}

	public static function warning($msg)
	{
        self::_log('WARNING',$msg);
	}

	public static function error($msg)
	{
        self::_log('ERROR',$msg);
	}

	public static function fatal($msg)
	{
        self::_log('FATAL',$msg);
	}

}
