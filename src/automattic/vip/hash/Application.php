<?php

namespace automattic\vip\hash;

use automattic\vip\hash\console\GetCommand;
use automattic\vip\hash\console\HashCommand;
use automattic\vip\hash\console\MarkCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Application extends BaseApplication {

	/**
	 * {@inheritDoc}
	 */
	function __construct() {
		parent::__construct('viphash', '1');
	}

	/**
	 * {@inheritDoc}
	 */
	public function run( InputInterface $input = null, OutputInterface $output = null ) {
		if ( null === $output ) {
			$formatter = new OutputFormatter( null );
			$output = new ConsoleOutput( ConsoleOutput::VERBOSITY_NORMAL, null, $formatter );
		}

		return parent::run( $input, $output );
	}

	/**
	 * Gets the default commands that should always be available.
	 *
	 * @return array An array of default Command instances
	 */
	protected function getDefaultCommands() {
		// Keep the core default commands to have the HelpCommand
		// which is used when using the --help option
		$defaultCommands = parent::getDefaultCommands();

		$defaultCommands[] = new HashCommand();
		$defaultCommands[] = new MarkCommand();
		$defaultCommands[] = new GetCommand();

		return $defaultCommands;
	}

}