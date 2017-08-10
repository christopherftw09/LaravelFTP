<?php

namespace LaravelFTP;

use Exception;

/**
 * Laravel FTP Class
 * Based on CodeIgniter's FTP Class.
 *
 * @license	https://choosealicense.com/licenses/mit/	MIT License
 */
class FTP {
	protected $mode;
	protected $connection;

	/**
	 * Class Constructor
	 *
	 * @throws	Exception
	 * @return	bool
	 */
	public function __construct($hostname, $username, $password, $port = 21, $passive = true)
	{
		// Connecting to the ftp server.
		$this->connection = @ftp_connect($hostname, $port);
		if(!$this->_is_conn()) throw new Exception('Unable to establish a connection with the FTP server.');

		// Logging into ftp server.
		if(!@ftp_login($this->connection, $username, $password)) throw new Exception('Login failed.');

		// set passive mode if needed
		ftp_pasv($this->connection, $passive);
		return true;
	}

	/**
	 * Class Deconstructor
	 *
	 * @return	bool
	 */
	function __destruct()
	{
		if(!$this->_is_conn()) return false;

		return @ftp_close($this->connection);
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
	 * Change the directory
	 *
	 * @param	string	$path
	 * @return	bool
	 */
	public function change_dir($path)
	{
		if(!$this->_is_conn()) return false;

		return @ftp_chdir($this->connection, $path);
	}

	/**
	 * Create a directory
	 *
	 * @param	string	$path
	 * @param	int	$permissions
	 * @return	bool
	 */
	public function mkdir($path, $permissions = NULL)
	{
		if(!$this->_is_conn()) return false;

		if(!@ftp_mkdir($this->connection, $path)) return false;

		if(isset($permissions)) $this->chmod($path, (int)$permissions);

		return true;
	}

	// upload
	// download

	/**
	 * Renames a file or directory
	 *
	 * @param	string	$oldname
	 * @param	string	$newname
	 *
	 * @return	bool
	 */
	public function rename($old_name, $new_name)
	{
		if(!$this->_is_conn()) return false;

		return @ftp_rename($this->connection, $old_name, $new_name);
	}

	// move($old, $new)

	/**
	 * Delete a file
	 *
	 * @param	string	$file
	 * @return	bool
	 */
	public function delete_file($file)
	{
		if(!$this->_is_conn()) return false;

		return @ftp_delete($this->connection, $file);
	}

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

		$files = $this->list_files($filepath);
		if($files->isNotEmpty())
		{
			foreach($files as $file)
			{
				// If we can't delete then it's probably an directory, so we will recursively call delete_dir().
				if(!preg_match('#/\.\.?$#', $file) && !@ftp_delete($this->connection, $file)) $this->delete_dir($file);
			}
		}
		return @ftp_rmdir($this->connection, $filepath);
	}

	/**
	 * Set file permissions
	 *
	 * @param	string	$path	File path
	 * @param	int	$perm	Permissions
	 * @return	bool
	 */
	public function chmod($path, $perm)
	{
		if(!$this->_is_conn()) return false;

		return @ftp_chmod($this->connection, $perm, $path);
	}

	/**
	 * Listing the files in a specified directory.
	 *
	 * @param	string	$path
	 * @param	bool	$detailed
	 * @return	Illuminate\Support\Collection Instance
	 */
	public function list_files($path, $detailed = false)
	{
		if(!$this->_is_conn()) return false;

		if($detailed) return collect(ftp_rawlist($this->connection, $path));

		return collect(ftp_nlist($this->connection, $path));
	}

	// public function mirror($locpath, $rempath)

	// protected function _getext($file)
	// protected function _settype($ext)

	// function read_file($filepath)
	// function save_file($filepath, $content)

	function size($file)
	{
		return @ftp_size($this->connection, $file);
	}

	function time($file)
	{
		return @ftp_mdtm($this->connection, $file);
	}
}
?>
