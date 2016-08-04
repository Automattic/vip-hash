<?php

namespace automattic\vip\hash\console;

use Symfony\Component\Console\Command\Command;

abstract class FileSystemCommand extends Command {

	public static $allowed_file_types = array(
		'php',
		'php5',
		'js',
		'html',
		'htm',
		'twig',
		'po',
		'pot',
		'jss',
		'jsx',
		'mustache',
		'handlebars',
		'diff',
		'patch',
	);

	public static $skip_folders = array(
		'.git',
		'.svn',
		'.idea',
	);
}

