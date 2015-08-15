<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

class SyncCommand extends Command {

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this->setName( 'sync' )
			->setDescription( 'synchronise with a remote' )
			->addArgument(
				'remote',
				InputArgument::REQUIRED,
				'the name of a remote source'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$remote_name = $input->getArgument( 'remote' );
		if ( empty( $remote_name ) ) {
			throw new \Exception( 'Missing remote name' );
		}
		$data = new DataModel();
		$remote = $data->getRemote( $remote_name );
		if ( !$remote ) {
			throw new \Exception( 'There was an issue trying to get the remotes information, does this remote name exist?' );
		}

		$output->writeln( "Synchronising hashes with " . $remote['name'] . " - " . $remote['uri'] );

		$output->writeln( "Sending hashes" );
		$this->sendHashes( $remote, $output );
		$output->writeln( "Fetching new hashes" );
		$this->fetchHashes( $remote, $output );

		$output->writeln( "Synchronised hashes with " . $remote['name'] . " - " . $remote['uri'] );
	}

	/**
	 * @param                 $remote
	 * @param OutputInterface $output
	 */
	protected function fetchHashes( $remote, OutputInterface $output  ) {
		$i_saw = $remote['latest_seen'];

		$client = new Client();
		$data = new DataModel();

		/**
		 * Finish by retrieving the data from the remote end that we don't have
		 */

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$response = $client->get(  $remote['uri'] . 'hash/seen/since/' . $i_saw );
		$new_items = $response->json();

		if ( !empty( $new_items ) ) {

			$output->writeln( "Hashes recieved: " . count( $new_items ) );

			foreach ( $new_items as $item ) {
				// process each item and save
				$data->markHash( $item['hash'], $item['user'], $item['status'], $item['notes'], $item['date'] );
			}
		} else {
			$output->writeln( "No new hashes recieved" );
		}
	}

	/**
	 * @param                 $remote
	 *
	 * @param OutputInterface $output
	 *
	 * @throws \Exception
	 */
	protected function sendHashes( $remote, OutputInterface $output  ) {
		$i_sent = $remote['last_sent'];

		$client = new Client();
		$data = new DataModel();

		$send_data = $data->getHashesSeenAfter( $i_sent );
		if ( !empty( $send_data ) ) {
			$output->writeln( "Hashes to send: ". count( $send_data ) );
			$send_data = json_encode( $send_data );

			try {
				/** @noinspection PhpVoidFunctionResultUsedInspection */
				$response = $client->post( $remote['uri'] . 'hash', [
					'body' => [
						'data' => $send_data
					]
				] );
				$json     = $response->json();
			} catch (\GuzzleHttp\Exception\ServerException $e) {
				$output->writeln( 'Guzzle ServerException: ' . $e->getResponse() );
				return;
			} catch ( \GuzzleHttp\Exception\ParseException $e ) {
				$output->writeln( 'Guzzle JSON issue: ' . $response->getBody() );
				return;
			}
		} else {
			$output->writeln( "No hashes to send" );
		}
	}
}