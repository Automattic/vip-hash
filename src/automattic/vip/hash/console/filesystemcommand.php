<?php

namespace automattic\vip\hash\console;

use Symfony\Component\Console\Command\Command;

abstract class FileSystemCommand extends Command {

	public $allowed_file_types = array(
		'php',
		'php5',
		'js',
		'html',
		'htm',
		'twig',
		'po',
		'pot',
	);
}

