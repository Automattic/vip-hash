<?php

namespace automattic\vip\hash;

use Symfony\Component\Console\Application as BaseApplication;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Application extends BaseApplication {

	function __construct() {
		parent::__construct('viphash', '1');
	}

	/**
	 * {@inheritDoc}
	 */
	public function run( InputInterface $input = null, OutputInterface $output = null ) {
		if ( null === $output ) {
			//$styles = Factory::createAdditionalStyles();
			$formatter = new OutputFormatter( null );//, $styles );
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

		//$defaultCommands[] = new ConvertCommand();

		return $defaultCommands;
	}

	/**
	 * Overridden so that the application doesn't expect the command
	 * name to be the first argument.
	 */
	public function getDefinition() {
		$inputDefinition = parent::getDefinition();
		// clear out the normal first argument, which is the command name
		$inputDefinition->setArguments();

		return $inputDefinition;
	}
}