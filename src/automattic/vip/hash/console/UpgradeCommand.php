<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\Pdo_Data_Model;
use cli\Tree;
use cli\tree\Markdown;
use SplFileInfo;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScanCommand
 * @package automattic\vip\hash\console
 */
class UpgradeCommand extends FileSystemCommand {

	/**
	 *
	 */
	protected function configure() {
		$this->setName( 'upgrade' )
			->setDescription( 'upgrades the data storage' );
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$output->writeln( 'Beginning upgrade' );
		$data_model = new Pdo_Data_Model();
		$result = $data_model->copy_and_upgrade();
		if ( ! $result ) {
			$output->writeln( '<error>Operation failed</error>' );
			return 1;
		}
		$output->writeln( '<info>Operation completed</info>' );
	}

}
