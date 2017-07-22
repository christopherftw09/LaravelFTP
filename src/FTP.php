<?php

namespace LaravelFTP;

use Exception;

/**
 * FTP Class
 * Laravel FTP Class - Based on CodeIgniter's FTP Class.
 * 
 * @license	http://opensource.org/licenses/MIT	MIT License
 */
class FTP {
	protected $mode;
	protected $connection;
	
	/**
	 * Class Constructor
	 * 
	 * @param	array	$config
	 * 
	 * @throws	\Exception
	 * 
	 * @return	bool
	 */
	public function __construct($hostname, $username, $password, $port = 21, $passive = true, $mode = FTP_ASCII)
	{
		$this->connection = @ftp_connect($hostname, $port);
		try {
			ftp_login($this->connection, $username, $password);
			
			// set passive mode if needed
			ftp_pasv($this->connection, $passive);
		}
		catch(\Exception $e) {
			throw new Exception($e->getMessage());
		}
		return true;
	}
	
	/**
	 * Connection Checker
	 * 
	 * Validates the ftp connection.
	 * 
	 * @return	bool
	 */
	protected function _is_conn()
	{
		return is_resource($this->connection);
	}
	
	/**
	 * Change directory
	 *
	 * @param	string	$path
	 * @return	bool
	 */
	public function change_dir($path)
	{
		if(!$this->_is_conn()) return false;
		
		return @ftp_chdir($this->connection, $path);
	}
	
	// create directory
	// upload
	// download
	// rename
	// move($old_file, $new_file)
	// delete_file($filepath)
	// delete_dir($filepath)
	// chmod($path, $perm)
	
	/**
	 * Delete Directory
	 * 
	 * deletes a folder and then recursively deletes everything (including sub-folders)
	 * contained within it.
	 * 
	 * @param	string	$filepath
	 * @return	bool
	 */
	public function delete_dir($filepath)
	{
		if(!$this->_is_conn()) return false;
		
		// Add a trailing slash to the file path if needed
		$filepath = preg_replace('/(.+?)\/*$/', '\\1/', $filepath);
		
		$list = $this->list_files($filepath);
		if($list->isNotEmpty())
		{
			foreach($list as $file)
			{
				// If we can't delete then it's probably an directory, so we will recursively call delete_dir().
				if(!preg_match('#/\.\.?$#', $file) && !@ftp_delete($this->connection, $file))
				{
					$this->delete_dir($file);
				}
			}
		}
		return @ftp_rmdir($this->connection, $filepath);
	}
	
	/**
	 * Listing the files in a specified directory.
	 * 
	 * @param	string	$path
	 * @return	array
	 */
	public function list_files($path)
	{
		return $this->_is_conn()?collect(ftp_nlist($this->connection, $path)):false;
	}
	
	// public function mirror($locpath, $rempath)
	// protected function _getext($filename)
	// protected function _settype($ext)
	
	// public function close()
	// protected function _error($line)
	
	// function read_file($filepath)
	// function save_file($filepath, $content)
}
?>
