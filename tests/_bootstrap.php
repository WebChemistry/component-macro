<?php

require __DIR__ . '/../vendor/autoload.php';

function dd($msg) {
	\Codeception\Util\Debug::debug($msg);
}
