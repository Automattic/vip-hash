<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\HashRecord;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\Remote;
use GuzzleHttp\Exception\ParseException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;

/**
 * Handle syncing hashes with a remote source
 */
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
		$data = new Pdo_Data_Model();
		$remote = $data->getRemote( $remote_name );
		if ( ! $remote ) {
			throw new \Exception( 'There was an issue trying to get the remotes information, does this remote name exist?' );
		}

		$output->writeln( 'Synchronising hashes with ' . $remote->getName() . ' - ' . $remote->getUri() );

		$output->writeln( 'Fetching new remote hashes' );
		$hashes = $this->fetchHashes( $remote );

		$output->writeln( 'Sending new local hashes' );
		$this->sendHashes( $remote, $output, $data );

		$this->saveHashes( $hashes, $output, $data );

		$output->writeln( 'Updating remote record' );
		$latest_hash = $data->getNewestSeenHash();
		$remote->setLatestSeen( $latest_hash['seen'] );
		$remote->setLastSent( time() );
		$saved = $remote->save( $data );
		$message = 'Failed to save remote record';
		if ( $saved ) {
			$message = 'Saved remote record';
		}
		$output->writeln( $message );

		$output->writeln( 'Synchronised hashes with ' . $remote->getName() . ' - ' . $remote->getUri() );
	}

	/**
	 * @param Remote $remote
	 *
	 * @return mixed
	 *
	 */
	protected function fetchHashes( Remote $remote ) {
		$i_saw = $remote->getLatestSeen();

		$client = new Client();

		/**
		 * Finish by retrieving the data from the remote end that we don't have
		 */

		/** @noinspection PhpVoidFunctionResultUsedInspection */
		$response = $client->get( $remote->getUri() . 'hash/seen/since/' . $i_saw );
		$new_items = $response->json();
		return $new_items;
	}

	protected function saveHashes( array $hashes, OutputInterface $output, DataModel $data ) {
		if ( empty( $hashes ) ) {
			$output->writeln( 'No new hashes recieved' );
			return;
		}
		$output->writeln( 'Saving '. count( $hashes ). ' new hashes' );
		foreach ( $hashes as $item ) {
			// process each item and save
			$hash = new HashRecord();
			$hash->setHash( $item['hash'] );
			$hash->setUsername( $item['user'] );
			$hash->setStatus( $item['status'] );
			$hash->setNote( $item['notes'] );
			$hash->setHumanNote( $item['human_note'] );
			$hash->setDate( $item['date'] );
			$data->saveHash( $hash );
		}

	}

	/**
	 * @param                 $remote
	 *
	 * @param OutputInterface $output
	 *
	 * @throws \Exception
	 */
	protected function sendHashes( Remote $remote, OutputInterface $output, DataModel $data ) {
		$i_sent = $remote->getLastSent();

		$send_data = $data->getHashesSeenAfter( $i_sent );
		if ( empty( $send_data ) ) {
			$output->writeln( 'No hashes to send' );
		}
		$output->writeln( 'Hashes to send: '. count( $send_data ) );

		// don't send a request with thousands of hashes all at once,
		// some servers have request size limits
		$chunks = array( $send_data );
		if ( count( $send_data ) > 500 ) {
			$chunks = array_chunk( $send_data, 500 );
		}
		$counter = 0;
		foreach ( $chunks as $chunk ) {
			$counter ++;
			$output->writeln( 'Sending chunk : '. $counter .' of '. count( $chunks ) );
			$sent = $this->sendHashChunk( $chunk, $remote, $output );
			// if something went wrong, don't continue sending chunks
			if ( ! $sent ) {
				break;
			}
		}
	}

	protected function sendHashChunk( array $data, Remote $remote, OutputInterface $output ) {
		$client = new Client();
		$send_data = json_encode( $data );
		try {
			/** @noinspection PhpVoidFunctionResultUsedInspection */
			$response = $client->post( $remote->getUri() . 'hash', [
				'body' => [
					'data' => $send_data,
				],
			] );
			// @TODO: do something with the response
			//$json = $response->json();
			$remote->setLastSent( time() );
		} catch ( ServerException $e) {
			$output->writeln( 'Guzzle ServerException: ' . $e->getResponse() );
			return false;
		} catch ( ParseException $e ) {
			$output->writeln( 'Guzzle JSON issue: ' . $response->getBody() );
			return false;
		}
		return true;
	}
}
