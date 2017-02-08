<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\HashRecord;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\Remote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
			)->addArgument(
				'option',
				InputArgument::OPTIONAL,
				'an additional option'
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

		$output->writeln( '<comment>Synchronising hashes with ' . $remote->getName() . ' - ' . $remote->getUri().'</comment>' );

		$output->writeln( '<comment>Fetching new remote hashes</comment>' );
		$hashes = $this->fetchHashes( $remote );
		if ( false === $hashes ) {
			$output->writeln( '<error>Fetching hashes failed</error>' );
			return;
		}
		if ( count( $hashes ) == 0 ) {
			$output->writeln( '<info>No new hashes available</info>' );
		}


		$output->writeln( '<comment>Sending new local hashes</comment>' );
		$success = $this->sendHashes( $remote, $output, $data );
		if ( false === $success ) {
			$output->writeln( '<error>Sending new local hashes failed</error>' );

			return;
		}

		if ( ! empty( $hashes ) ) {
			$output->writeln( '<comment>Saving the new hashes</comment>' );
			$this->saveHashes( $hashes, $output, $data );
		}

		$output->writeln( '<comment>Updating remote record</comment>' );
		$latest_hash = $data->getNewestSeenHash();
		$remote->setLatestSeen( $latest_hash['seen'] );
		$remote->setLastSent( time() );
		$saved = $remote->save( $data );
		$message = '<error>Failed to save remote record</error>';
		if ( $saved ) {
			$message = '<info>Saved remote record</info>';
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

		$response = \Request::get( $remote->getUri() . 'hashes?since=' . $i_saw );
		if ( 200 !== $response->status_code ) {
			echo "Problem response code? ".$response->status_code."\n";
			return false;
		}
		$new_items = json_encode( $response->body );
		return $new_items;

		/**
		 * @var: $response \GuzzleHttp\Message\ResponseInterface
		 */
		$response = $client->get( $remote->getUri() . 'hashes?since=' . $i_saw );
		if ( 200 !== $response->getStatusCode() ) {
			echo "Problem response code? ".$response->getStatusCode()."\n";
			return false;
		}
		$new_items = $response->json();
		return $new_items;
	}

	/**
	 * @param array           $hashes
	 * @param OutputInterface $output
	 * @param DataModel       $data
	 */
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
	 * @param Remote          $remote
	 *
	 * @param OutputInterface $output
	 *
	 * @param DataModel       $data
	 *
	 * @return bool
	 */
	protected function sendHashes( Remote $remote, OutputInterface $output, DataModel $data ) {
		$i_sent = $remote->getLastSent();

		$send_data = $data->getHashesSeenAfter( $i_sent );
		if ( empty( $send_data ) ) {
			$output->writeln( '<info>No hashes to send</info>' );
			return true;
		}
		$output->writeln( '<info>Hashes to send: '. count( $send_data ).'</info>' );

		// don't send a request with thousands of hashes all at once,
		// some servers have request size limits
		$chunks = array( $send_data );
		if ( count( $send_data ) > 500 ) {
			$chunks = array_chunk( $send_data, 500 );
		}
		$counter = 0;
		foreach ( $chunks as $chunk ) {
			$counter ++;
			$output->writeln( '<info>Sending chunk : '. $counter .' of '. count( $chunks ).'</info>' );
			$sent = $this->sendHashChunk( $chunk, $remote, $output );
			// if something went wrong, don't continue sending chunks
			if ( ! $sent ) {
				$output->writeln( '<error>Chunk '. $counter .' of '. count( $chunks ) .' failed to send</error>' );
				return false;
			}
		}
		return true;
	}

	/**
	 * @param array           $data
	 * @param Remote          $remote
	 * @param OutputInterface $output
	 *
	 * @return bool
	 */
	protected function sendHashChunk( array $data, Remote $remote, OutputInterface $output ) {
		$client = new Client();
		$send_data = json_encode( $data );
		try {
			/**
			 * @var: $response \GuzzleHttp\Message\ResponseInterface
			 */
			$response = $client->post( $remote->getUri() . 'hashes', [
				'body' => [
					'data' => $send_data,
				],
			] );
			// @TODO: do something with the response
			//$json = $response->json();
			if ( $response->getStatusCode() != 200 ) {
				echo 'Problem response code? '.$response->getStatusCode()."--\n";
				echo $response->getBody()->getContents().'|';
				return false;
			}
			$remote->setLastSent( time() );
		} catch ( ServerException $e) {
			$output->writeln( 'Guzzle ServerException: ' . $e->getResponse() );
			$output->writeln( $e->getMessage() );
			return false;
		} catch ( ParseException $e ) {
			$output->writeln( 'Guzzle JSON issue: ' . $e->getResponse() );
			return false;
		}
		return true;
	}
}
