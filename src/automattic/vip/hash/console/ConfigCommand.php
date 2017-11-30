<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\HashRecord;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * {@inheritDoc}
 */
class ConfigCommand extends Command {

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this->setName( 'config' )
			->setDescription( '<info>sets</info> or <info>gets</info> a config value' )
			->addArgument(
				'action',
				InputArgument::REQUIRED,
				'<info>set</info> or <info>get</info>'
			)->addArgument(
				'key',
				InputArgument::REQUIRED,
				'Key value'
			)->addArgument(
				'value',
				InputArgument::OPTIONAL,
				'The new value'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$data = new Pdo_Data_Model();
		$config = $data->config();

		$action = $input->getArgument( 'action' );
		$key = $input->getArgument( 'key' );
		if ( 'get' === $action ) {
			$output->writeln( $config->get( $key ) );
			return true;
		}
		if ( 'set' === $action ) {
			$value = $input->getArgument( 'value' );
			$config->set( $key, $value );
			return true;

		}
		throw new \Exception( 'Unknown action, use <info>set</info> or <info>get</info>' );
	}

}
