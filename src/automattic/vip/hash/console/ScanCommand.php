<?php

namespace automattic\vip\hash\console;

use automattic\vip\hash\Pdo_Data_Model;
use SplFileInfo;
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
				InputArgument::OPTIONAL,
				'A file hash to find, or a file to be hashed. Assumes hash if the given value is not a locatable file'
			)->addArgument(
				'format',
				InputArgument::OPTIONAL,
				'The format to output, json by default, can additionally specify markdown'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$format = $input->getArgument( 'format' );
		$folder = $input->getArgument( 'folder' );
		if ( empty( $folder ) || $folder === '.' ) {
			$folder = getcwd();
		} elseif ( $folder === '..' ) {
			$folder = dirname( getcwd() );
		}
		if ( empty( $format ) ) {
			$format = 'markdown';
		}
		if ( ! in_array( $format, array( 'json', 'markdown' ) ) ) {
			throw new \Exception( 'Unknown format' );
		}
		$data_model = new Pdo_Data_Model();
		$fileinfo = new SplFileInfo( $folder );
		$data = $this->processNode( $fileinfo, $data_model );
		if ( 'json' == $format ) {
			$json = json_encode( $data, JSON_PRETTY_PRINT );
			$output->writeln( $json );
		} else if ( 'markdown' == $format ) {
			if ( empty( $data ) ) {
				$output->writeln("<info>There are no reviewable files to scan for. The tools scan/status commands look for these file extensions:</info>");
				$output->writeln("<info>".implode(', ', FileSystemCommand::$allowed_file_types )."</info>");
				return;
			}
			$markdown = '# Feedback'.PHP_EOL.PHP_EOL;
			$markdown .= '## General Feedback'.PHP_EOL.PHP_EOL;
			$markdown .= '@TODO: General notes'.PHP_EOL.PHP_EOL;

			$markdown .= '## Line by Line Feedback'.PHP_EOL.PHP_EOL;
			$markdown .= 'Note: Issues may appear more than once in a file, a search replace in your editor will reveal all instances of an issue and their line numbers'.PHP_EOL.PHP_EOL;
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
		/*if ( ! empty( $md ) ) {
			$md .= PHP_EOL;
		}*/
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
				if ( ! empty( $hash['human_note'] ) ) {
					$notes .= PHP_EOL.$hash['human_note'].':';
				} else {
					$notes .= PHP_EOL.'@TODO: Notes';
				}
				$notes .= PHP_EOL.PHP_EOL.'```'.PHP_EOL.$hash['notes'].PHP_EOL.'```'.PHP_EOL;
			}
		}
		if ( ! empty( $notes ) ) {
			$md .= '### '.$node['file'].PHP_EOL.$notes.PHP_EOL;
		}
		return $md;
	}

	/*public function processFile( $file, DataModel $data_model ) {
		$data = array();

		// only process the file types we're interested in
		$info = pathinfo( $file );
		if ( isset( $info['extension'] ) ) {
			if ( ! in_array( $info['extension'], self::$allowed_file_types ) ) {
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
	}*/
}
