<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\DataModel;
use automattic\vip\hash\Pdo_Data_Model;
use automattic\vip\hash\console\FileSystemCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ScanCommand
 * @package automattic\vip\hash\console
 */
class ScanCommand extends FileSystemCommand {

	protected function configure() {
		$this->setName( 'scan' )
			->setDescription( 'take a folder and generate a json response detailing the files inside' )
			->addArgument(
				'folder',
				InputArgument::REQUIRED,
				'A file hash to find, or a file to be hashed. Assumes hash if the given value is not a locatable file'
			)->addArgument(
				'format',
				InputArgument::OPTIONAL,
				'The format to output, json by default, can additionally specify markdown'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$folder = $input->getArgument( 'folder' );
		$format = $input->getArgument( 'format' );
		if ( empty( $folder ) ) {
			throw new \Exception( 'Empty folder parameter' );
		}
		if ( empty( $format ) ) {
			$format = 'json';
		}
		if ( ! in_array( $format, array( 'json', 'markdown' ) ) ) {
			throw new \Exception( 'Unknown format' );
		}
		$data_model = new Pdo_Data_Model();
		$data = $this->processNode( $folder, $data_model );
		if ( 'json' == $format ) {
			$json = json_encode( $data, JSON_PRETTY_PRINT );
			$output->writeln( $json );
		} else if ( 'markdown' == $format ) {
			$markdown = '';
			$markdown .= $this->displayMarkdown( $data );

			// use relative paths rather than full paths
			if ( '.' != $folder ) {
				$markdown = str_replace( $folder, '', $markdown );
			}

			$output->writeln( $markdown );
		}
	}

	private function displayMarkdown( array $node ) {
		$md = '? unknown entry in data structure'.PHP_EOL;
		if ( ! empty( $node['folder'] ) ) {
			$md = $this->displayFolderMarkdown( $node );
		} else if ( ! empty( $node['file'] ) ) {
			$md = $this->displayFileMarkdown( $node );//
		}
		if ( ! empty( $md ) ) {
			$md .= PHP_EOL;
		}
		return $md;
	}

	private function displayFolderMarkdown( array $node ) {
		$md = '';
		if ( empty( $node['contents'] ) ) {
			return $md;
		}
		foreach ( $node['contents'] as $subnode ) {
			$md .= $this->displayMarkdown( $subnode );
		}
		return $md;
	}

	private function displayFileMarkdown( array $node ) {
		$md = '';
		if ( empty( $node['hashes'] ) ) {
			return '';
		}

		$notes = '';
		foreach ( $node['hashes'] as $hash ) {
			if ( 'false' == $hash['status']  ) {
				$notes .= PHP_EOL.'Notes';
				$notes .= PHP_EOL.PHP_EOL.'```'.PHP_EOL.$hash['notes'].PHP_EOL.'```'.PHP_EOL;
			}
		}
		if ( ! empty( $notes ) ) {
			$md .= '## '.$node['file'].PHP_EOL.$notes;
		}
		return $md;
	}

	/**
	 * @param $file
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function processNode( $file, DataModel $data_model ) {

		foreach ( $this->skip_folders as $skip ) {
			if ( substr( $file, strlen( $skip ) * -1 ) === $skip ) {
				return null;
			}
		}

		$data = array();
		if ( is_dir( $file ) ) {
			$data = $this->processFolder( $file, $data_model );
			return $data;
		}

		$data = $this->processFile( $file, $data_model );
		return $data;
	}

	public function processFolder( $file, DataModel $data_model ) {
		$data = array();
		$unfiltered_folders = scandir( $file );
		$folders = array_diff( $unfiltered_folders, array( '..', '.' ) );
		if ( empty( $folders ) ) {
			return null;
		}
		$contents = array();
		foreach ( $folders as $found_file ) {
			$result = $this->processNode( $file . DIRECTORY_SEPARATOR . $found_file, $data_model );
			if ( ! empty( $result ) && ( null != $result ) ) {
				if ( is_dir( $file . DIRECTORY_SEPARATOR . $found_file ) ) {
					$contents[] = $result;
					continue;
				}
				$r = array(
					'file' => $file . DIRECTORY_SEPARATOR . $found_file,
					'hashes' => $result,
				);

				$contents[] = $r;
			}
		}
		$data = array(
			'folder'   => $file,
			'contents' => $contents,
		);
		return $data;
	}


	public function processFile( $file, DataModel $data_model ) {
		$data = array();

		// only process the file types we're interested in
		$info = pathinfo( $file );
		if ( isset( $info['extension'] ) ) {
			if ( ! in_array( $info['extension'], $this->allowed_file_types ) ) {
				return null;
			}
		}

		try {
			$hash = $data_model->hashFile( $file );
		} catch ( \Exception $e ) {
			$data = array(
				array(
					'hash' => 'empty',
					'status' => 'unknown',
					'file' => $file,
				),
			);
			return $data;
		}
		try {
			$data = $data_model->getHashStatusAllUsers( $hash );
		} catch ( \Exception $e ) {
			$data = array(
				array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file,
				),
			);
			return $data;
		}
		if ( empty( $data ) ) {
			$data = array(
				array(
					'hash' => $hash,
					'status' => 'unknown',
					'file' => $file,
				),
			);
		}

		return $data;
	}
}
