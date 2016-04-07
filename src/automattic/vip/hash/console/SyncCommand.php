<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Remote;
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
		if ( ! $remote ) {
			throw new \Exception( 'There was an issue trying to get the remotes information, does this remote name exist?' );
		}

		$output->writeln( 'Synchronising hashes with ' . $remote->getName() . ' - ' . $remote->getUri() );

		$output->writeln( 'Fetching new remote hashes' );
		$hashes = $this->fetchHashes( $remote );

		$output->writeln( 'Sending new local hashes' );
		$this->sendHashes( $remote, $output );

		if ( ! empty( $hashes ) ) {
			$output->writeln( 'Saving ". count( $hashes ). " new hashes' );
			$this->saveHashes( $hashes, $output );
		} else {
			$output->writeln( 'No new hashes recieved' );
		}

		$output->writeln( 'Updating remote record' );
		$latest_hash = $data->getNewestSeenHash();
		$remote->setLatestSeen( $latest_hash['seen'] );
		$remote->setLastSent( time() );
		$saved = $remote->save( $data );
		if ( $saved ) {
			$output->writeln( 'Saved remote record' );
		} else {
			$output->writeln( 'Failed to save remote record' );
		}

		$output->writeln( 'Synchronised hashes with ' . $remote->getName() . ' - ' . $remote->getUri() );
	}

	/**
	 * @param                 $remote
	 * @param OutputInterface $output
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

	protected function saveHashes( array $hashes ) {
		if ( ! empty( $hashes ) ) {
			$data = new DataModel();
			foreach ( $hashes as $item ) {
				// process each item and save
				$data->markHash( $item['hash'], $item['user'], $item['status'], $item['notes'], $item['date'] );
			}
		}

	}

	/**
	 * @param                 $remote
	 *
	 * @param OutputInterface $output
	 *
	 * @throws \Exception
	 */
	protected function sendHashes( Remote $remote, OutputInterface $output ) {
		$i_sent = $remote->getLastSent();

		$data = new DataModel();

		$send_data = $data->getHashesSeenAfter( $i_sent );
		if ( ! empty( $send_data ) ) {
			$output->writeln( 'Hashes to send: '. count( $send_data ) );

			// don't send a request with thousands of hashes all at once,
			// some servers have request size limits
			if ( count( $send_data > 500 ) ) {
				$chunks = array_chunk( $send_data, 500 );
			} else {
				$chunks = array( $send_data );
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
		} else {
			$output->writeln( 'No hashes to send' );
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
		} catch (\GuzzleHttp\Exception\ServerException $e) {
			$output->writeln( 'Guzzle ServerException: ' . $e->getResponse() );
			return false;
		} catch ( \GuzzleHttp\Exception\ParseException $e ) {
			$output->writeln( 'Guzzle JSON issue: ' . $response->getBody() );
			return false;
		}
		return true;
	}
}
