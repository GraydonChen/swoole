<?php

class SwooleLib {

	public static function LoadLibClass($className, $module) {
		include_once dirname(__FILE__) . '/' . $module . '/' . $className . '.php';
	}

}

function Trace() {
	foreach (func_get_args() as $arg) {
		var_dump($arg);
	}
}

SwooleLib::LoadLibClass('SwooleEncryptDecrypt', 'Protocols/BYPack');
SwooleLib::LoadLibClass('SocketPackage', 'Protocols/BYPack');
SwooleLib::LoadLibClass('ReadPackageExt', 'Protocols/BYPack');
SwooleLib::LoadLibClass('WritePackage', 'Protocols/BYPack');
SwooleLib::LoadLibClass('IpcPackage', 'Protocols');
SwooleLib::LoadLibClass('ByPackClient', 'Protocols/BYPack');

