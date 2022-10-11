<?php

class DebugFileLogger extends FileLogger
{
	static private $_instance = null;

	static public function getInstance () {

		if (! self::$_instance) {
			self::$_instance = new self(AbstractLoggerCore::DEBUG);
			self::$_instance->setFilename(_PS_ROOT_DIR_.'/log/maxipromodebug.log');
			self::$_instance->log('\n************************************************************\n');
		}

		return self::$_instance;
	}

}

