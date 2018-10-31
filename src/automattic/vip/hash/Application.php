<?php

namespace automattic\vip\hash;

use automattic\vip\hash\console\ConfigCommand;
use automattic\vip\hash\console\GetCommand;
use automattic\vip\hash\console\HashCommand;
use automattic\vip\hash\console\MarkCommand;
use automattic\vip\hash\console\RemotesCommand;
use automattic\vip\hash\console\ScanCommand;
use automattic\vip\hash\console\SyncCommand;
use automattic\vip\hash\console\StatusCommand;
use automattic\vip\hash\console\UpgradeCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class Application extends BaseApplication {

    const VERSION = '@package_version@';
    const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';
    const RELEASE_DATE = '@release_date@';

	/**
	 * {@inheritDoc}
	 */
	function __construct() {
		parent::__construct( 'viphash', self::VERSION.' '.self::RELEASE_DATE );
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

		$defaultCommands[] = new ConfigCommand();
		$defaultCommands[] = new HashCommand();
		$defaultCommands[] = new MarkCommand();
		$defaultCommands[] = new GetCommand();
		$defaultCommands[] = new ScanCommand();
		$defaultCommands[] = new RemotesCommand();
		$defaultCommands[] = new SyncCommand();
		$defaultCommands[] = new StatusCommand();
		$defaultCommands[] = new UpgradeCommand();

		return $defaultCommands;
	}

}
