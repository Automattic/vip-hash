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
			->setDescription( 'Managing remotes' )
			->addArgument(
				'subcommand',
				InputArgument::REQUIRED,
				'add, auth, list or rm'
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
		if ( 'add' === $sub_command ) {
			$this->add_remote( $output, $data );
			return;
		}
		if ( 'list' === $sub_command ) {
			$this->list_remotes( $output, $data );
			return;
		}
		if ( 'auth' == $sub_command ) {
			// authenticate a remote
			$this->authenticate( $input, $output, $data );
			return;
		}

		if ( 'rm' == $sub_command ) {
			// remove a remote
			$this->remove_remote( $output, $data );
			return;
		}

		throw new \Exception( 'unknown subcommand '.$sub_command );
	}

	public function add_remote( OutputInterface $output, DataModel $data ) {
		$name = $input->getArgument( 'name' );
		$uri = $input->getArgument( 'uri' );
		$remote = new Remote();
		$remote->setName( $name );
		$remote->setUri( $uri );
		$result = $remote->save( $data );
		if ( !$result ) {
			$output->writeln( "<error>Saving the new entry failed</error>");
		}
	}

	public function list_remotes( OutputInterface $output, DataModel $data ) {
		$result = $this->get_remotes( $data );
		$json = json_encode( $result, JSON_PRETTY_PRINT );
		$output->writeln( $json );
	}

	public function get_remotes( DataModel $data_model ) {
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

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param DataModel       $data
	 */
	public function authenticate( InputInterface $input, OutputInterface $output, DataModel $data ) {
		$output->writeln( "<error>Not supported yet</error>" );
	}

	/**
	 * @param OutputInterface $output
	 * @param DataModel       $data
	 */
	public function remove_remote( OutputInterface $output, DataModel $data ) {
		$output->writeln( "<error>Not supported yet</error>" );
	}
}
