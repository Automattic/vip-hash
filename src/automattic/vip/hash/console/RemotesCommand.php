<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\Remote;
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
		$sub_command = $input->getArgument( 'subcommand' );
		$data = new Pdo_Data_Model();
		if ( 'add' == $sub_command ) {
			$name = $input->getArgument( 'name' );
			$uri = $input->getArgument( 'uri' );
			$remote = new Remote();
			$remote->setName( $name );
			$remote->setUri( $uri );
			$result = $remote->save( $data );
			$output->write( $result );
			return;
		} else if ( 'list' == $sub_command ) {
			$result = $this->listRemotes( $data );
			$json = json_encode( $result, JSON_PRETTY_PRINT );
			$output->writeln( $json );
			return;
		}
		throw new \Exception( 'unknown subcommand' );
	}

	protected function listRemotes( DataModel $data_model ) {
		$result = array();
		$remotes = $data_model->getRemotes();
		foreach ( $remotes as $remote ) {
			$result[] = array(
				'name' => $remote->getName(),
				'uri' => $remote->getUri(),
				'latest_seen' => $remote->getLatestSeen(),
				'last_sent' => $remote->getLastSent(),
			);
		}
		return $result;
	}
}
