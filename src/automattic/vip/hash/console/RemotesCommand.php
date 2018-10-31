<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\Remote;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class RemotesCommand extends Command {

	/**
	 * {@inheritDoc}
	 */
	protected function configure() {
		$this->setName( 'remote' )
			->setDescription( "A subcommand for managing remote data sources. This will allow sending, receiving and syncing to remote servers using OAuth1a\n\nExamples:\n\nviphash remote add origin example.com secret key\n\nviphash remote list" )
			->addArgument(
				'subcommand',
				InputArgument::REQUIRED,
				'add, ls, list, rm, remove, or delete'
			)->addArgument(
				'name',
				InputArgument::OPTIONAL,
				'the name of a remote to add'
			)->addArgument(
				'uri',
				InputArgument::OPTIONAL,
				'the uri of the remote to add'
			)->addArgument(
				'secret',
				InputArgument::OPTIONAL,
				'An OAuth1a secret'
			)->addArgument(
				'key',
				InputArgument::OPTIONAL,
				'An OAuth1a key'
			);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$sub_command = $input->getArgument( 'subcommand' );
		$data = new Pdo_Data_Model();
		if ( 'add' === $sub_command ) {
			$this->add_remote( $output, $input, $data );
			return;
		}
		if ( in_array( $sub_command, [ 'ls', 'list' ] ) ) {
			$this->list_remotes( $output, $data );
			return;
		}

		if ( in_array( $sub_command, [ 'rm', 'delete', 'remove' ] ) ) {
			// remove a remote
			$this->remove_remote( $input, $output, $data );
			return;
		}

		throw new \Exception( 'unknown subcommand ' . $sub_command );
	}

	public function add_remote( OutputInterface $output, InputInterface $input, DataModel $data ) {
		$output->writeln( '<info>Beta, add remote method for OAuth1</info>' );
		$name = $input->getArgument( 'name' );
		$uri = $input->getArgument( 'uri' );
		$api_url = '';
		$secret = $input->getArgument( 'secret' );
		$key = $input->getArgument( 'key' );
		$output->writeln( 'key: ' . $key );
		$output->writeln( 'secret: ' . $secret );

		if ( empty( $key ) || empty( $secret ) ) {
			$output->writeln( 'Warning: OAuth1 secret/key pair not passed, you may receive a 401 error' );
		}

		$consumer = new \OAuthConsumer( $key, $secret, null );
		$token = null;

		$auth = new \Requests_Auth_OAuth1( array(
			'consumer' => $consumer,
			'signature_method' => new \OAuthSignatureMethod_HMAC_SHA1(),
			'token' => $token,
		) );

		$output->writeln( 'Interesting information! Lets see if we can find the API' );

		try {
			// First, locate the API
			$api_url = $this->locate_url( $uri );
			$output->writeln( '<info>Success! Found an API at ' . $api_url . ', grabbing OAuth information</info>' );

			$session = new \Requests_Session( $api_url . '/', [],[], ['verify'=>false] );

			$index = $session->get( '' );
			$index_data = json_decode( $index->body );

			if ( empty( $index_data->authentication ) || empty( $index_data->authentication->oauth1 ) ) {
				throw new \Exception( "Could not locate OAuth information; are you sure it's enabled?" );
			}

			// Add authenticator
			$session->auth = $auth;

			$output->writeln( 'Enquiring about a request token...' );

			// Retrieve the request token
			$response = $auth->get_request_token( $session, $index_data->authentication->oauth1->request );
			parse_str( $response->body, $token_args );

			$output->writeln( 'Response recieved, assembling token...' );

			$token = new \OAuthToken( $token_args['oauth_token'], $token_args['oauth_token_secret'] );
			$auth->set_token( $token );

			$output->writeln( 'Building the authorization URL...' );

			// Build the authorization URL
			$authorization = $index_data->authentication->oauth1->authorize;

			$char = '?';
			if ( strpos( $authorization, '?' ) ) {
				$char = '&';
			}
			$authorization .= $char;
			$authorization .= 'oauth_token=' . urlencode( $token_args['oauth_token'] );


			$output->writeln( '<question>In order to continue, a verification token will be needed, Please visit <info>' . $authorization . '</info></question>' );
			$helper = $this->getHelper( 'question' );

			$question = new Question( "What did the site say? ( it should look like a verification token )\n", '' );
			$code = $helper->ask( $input, $output, $question );

			$output->writeln( 'Response:' );
			$output->writeln( $code );
			$output->writeln( 'Converting code into an access token' );

			// Convert request token to access token
			$response = $auth->get_access_token( $session, $index_data->authentication->oauth1->access, $code );
			parse_str( $response->body, $token_args );

			$token = new \OAuthToken( $token_args['oauth_token'], $token_args['oauth_token_secret'] );
			$auth->set_token( $token );

			$output->writeln( 'Token assembled, preparing to save new remote data source' );

			$remote = new Remote( [
				'name' => $name,
				'uri' => $api_url
			]);
			$remote->setOauthDetails( $auth );
			$output->writeln( 'Saving to data store' );
			$result = $data->addRemote( $remote );

			if ( ! $result ) {
				$output->writeln( '<error>Saving the new remote failed</error>' );
				return;
			}

			$output->writeln( '<info>Authorisation Succeeded!</info>' );
			$output->writeln( sprintf( 'Key: %s', $token_args['oauth_token'] ) );
			$output->writeln( sprintf( 'Secret: %s', $token_args['oauth_token_secret'] ) );

		} catch ( \Requests_Exception_HTTP $e ) {
			$output->writeln( '<error>Error: ' . $e->getMessage() . '</error>' );
			$output->writeln( '<error>Error: ' . $e->getType() . ' - ' . $e->getData()->url . ' ' . $e->getData()->body . '</error>' );
			$output->writeln( '<info>Most unfortunate! See you soon :)</info>' );
			return;
		}catch ( \Exception $e ) {
			$output->writeln( '<error>Error: ' . $e->getMessage() . '</error>' );
			$output->writeln( '<info>Most unfortunate! See you soon :)</info>' );
			return;
		}
		$output->writeln( '<info>End of add remote method</info>' );
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
	 * @param OutputInterface $output
	 * @param DataModel       $data
	 */
	public function remove_remote( InputInterface $input, OutputInterface $output, DataModel $data ) {
		//$output->writeln( '<error>Not supported yet</error>' );
		$name = $input->getArgument( 'name' );
		$r = new Remote();
		$r->setName( $name );
		$result = $data->removeRemote( $r );
	}

	protected function locate_url( $raw_url ) {
		// First, locate the API
		$url = '';
		$page = \Requests::head( $raw_url, null, [ 'verify' => false ] );
		$links = $page->headers['Link'];
		if ( empty( $links ) ) {
			throw new \Exception( "Could not locate API; are you sure it's enabled?" );
		}

		$links = $this->parse_links( $links );
		foreach ( $links as $link ) {
			if ( empty( $link['rel'] ) || $link['rel'] !== 'https://api.w.org/' ) {
				continue;
			}

			$url = $link['url'];
		}
		if ( empty( $url ) ) {
			throw new \Exception( "Could not locate API; are you sure it's enabled?" );
		}
		return $url;
	}

	protected function parse_links( $links ) {
		if ( ! is_array( $links ) ) {
			$links = explode( ',', $links );
		}

		$real_links = array();
		foreach ( $links as $link ) {
			$parts = explode( ';', $link );
			$link_vars = array();
			foreach ( $parts as $part ) {
				$part = trim( $part, ' ' );
				if ( ! strpos( $part, '=' ) ) {
					$link_vars['url'] = trim( $part, '<>' );
					continue;
				}

				list( $key, $val ) = explode( '=', $part );
				$real_val = trim( $val, '\'" ' );
				$link_vars[ $key ] = $real_val;
			}

			$real_links[] = $link_vars;
		}

		return $real_links;
	}
}
