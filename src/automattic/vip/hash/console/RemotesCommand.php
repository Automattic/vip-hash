<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemotesCommand extends Command {

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this->setName( 'remote' )
			->setDescription( 'managing remotes' )
			->addArgument(
				'subcommand',
				InputArgument::REQUIRED,
				'add or list'
			)->addArgument(
				'name',
				InputArgument::OPTIONAL,
				'the name of a remote to add'
			)->addArgument(
				'uri',
				InputArgument::OPTIONAL,
				'the uri of the remote to add'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$subcommand = $input->getArgument( 'subcommand' );

		if ( 'add' == $subcommand ) {
			$this->addRemote( $input, $output );
			return;
		} else if ( 'list ' == $subcommand ) {
			$this->listRemotes( $input, $output );
			return;
		}
		throw new \Exception( 'unknown subcommand' );
	}

	protected function listRemotes( InputInterface $input, OutputInterface $output ) {
		$data = new Pdo_Data_Model();
		$result = array();
		$remotes = $data->getRemotes();
		foreach ( $remotes as $remote ) {
			$result[] = array(
				'name' => $remote->getName(),
				'uri' => $remote->getUri(),
				'latest_seen' => $remote->getLatestSeen(),
				'last_sent' => $remote->getLastSent(),
			);
		}
		$json = json_encode( $result, JSON_PRETTY_PRINT );
		$output->writeln( $json );
	}

	protected function addRemote( InputInterface $input, OutputInterface $output ) {
		$name = $input->getArgument( 'name' );
		$uri = $input->getArgument( 'uri' );
		$data = new Pdo_Data_Model();
		$result = $data->addRemote( $name, $uri );
		$output->write( $result );
	}
}
