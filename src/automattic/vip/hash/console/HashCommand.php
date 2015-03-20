<?php

namespace automattic\vip\hash\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HashCommand extends Command {
	protected function configure() {
		$this->setName( 'hash' )
			->setDescription( 'take a file and generate a hash representing said file' )
			->addArgument(
				'file',
				InputArgument::REQUIRED,
				'The file to be hashed'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$file = $input->getArgument( 'file' );
		$code = php_strip_whitespace( $file );
		$hash = sha1( $code );
		$output->write( $hash );
	}
} 