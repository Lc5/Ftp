<?php

namespace Lc5;

/**
 * Class Ftp
 *
 * A simple object wrapper around native ftp_* functions. Fully unit-tested.
 *
 * @method bool alloc(int $filesize, string &$result = null) — Allocates space for a file to be uploaded
 * @method bool cdup() — Changes to the parent directory
 * @method bool chdir(string $directory) — Changes the current directory on a FTP server
 * @method int chmod(int $mode, string $filename) — Set permissions on a file via FTP
 * @method bool delete(string $path) — Deletes a file on the FTP server
 * @method bool exec(string $command) — Requests execution of a command on the FTP server
 * @method bool fget(resource $handle, string $remote_file, int $mode, int $resumepos = 0) — Downloads a file from the FTP server and saves to an open file
 * @method bool fput(string $remote_file, resource $handle, int $mode, int $startpos = 0) — Uploads from an open file to the FTP server
 * @method mixed get_option(int $option) — Retrieves various runtime behaviours of the current FTP stream
 * @method bool get(string $local_file, string $remote_file, int $mode, int $resumepos = 0) — Downloads a file from the FTP server
 * @method int mdtm(string $remote_file) — Returns the last modified time of the given file
 * @method string mkdir(string $directory) — Creates a directory
 * @method int nb_continue() — Continues retrieving/sending a file (non-blocking)
 * @method int nb_fget(resource $handle, string $remote_file, int $mode, int $resumepos = 0) — Retrieves a file from the FTP server and writes it to an open file (non-blocking)
 * @method int nb_fput(string $remote_file, resource $handle, int $mode, int $startpos = 0) — Stores a file from an open file to the FTP server (non-blocking)
 * @method int nb_get(string $local_file, string $remote_file, int $mode, int $resumepos = 0) — Retrieves a file from the FTP server and writes it to a local file (non-blocking)
 * @method int nb_put(string $remote_file, string $local_file, int $mode, int $startpos = 0) — Stores a file on the FTP server (non-blocking)
 * @method array nlist(string $directory) — Returns a list of files in the given directory
 * @method bool pasv(bool $pasv) — Turns passive mode on or off
 * @method bool put(string $remote_file, string $local_file, int $mode, int $startpos = 0) — Uploads a file to the FTP server
 * @method string pwd() — Returns the current directory name
 * @method array raw(string $command) — Sends an arbitrary command to an FTP server
 * @method mixed rawlist(string $directory, bool $recursive = false) — Returns a detailed list of files in the given directory
 * @method bool rename(string $oldname, string $newname) — Renames a file or a directory on the FTP server
 * @method bool rmdir(string $directory) — Removes a directory
 * @method bool set_option(int $option, mixed $value) — Set miscellaneous runtime FTP options
 * @method bool site(string $command) — Sends a SITE command to the server
 * @method int size(string $remote_file) — Returns the size of the given file
 * @method string systype() — Returns the system type identifier of the remote FTP server
 * 
 * @author Łukasz Krzyszczak <lukasz.krzyszczak@gmail.com>
 */
class Ftp
{
    /** @var resource */
    private $connection;

    /** @var string */
    private $host;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var int */
    private $port;

    /** @var int */
    private $timeout;

    /** @var bool */
    private $ssl;

    /**
     * Sets FTP connection params.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int $port
     * @param int $timeout
     * @param bool $ssl
     * @throws \RuntimeException
     */
    public function __construct($host, $username = 'anonymous', $password = '', $port = 21, $timeout = 90, $ssl = false)
    {
        if (!extension_loaded('ftp')) {
            throw new \RuntimeException('FTP extension is not loaded!');
        }

        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->ssl = $ssl;
    }

    /**
     * Connects and logs in to FTP server.
     *
     * @return resource $connection
     * @throws \RuntimeException
     */
    public function connect()
    {
        if (!$this->connection) {

            if ($this->ssl) {
                $connection = ftp_ssl_connect($this->host, $this->port, $this->timeout);
            } else {
                $connection = ftp_connect($this->host, $this->port, $this->timeout);
            }

            if (!$connection) {
                throw new \RuntimeException("FTP connection to '" . $this->host . ":" . $this->port . "' has failed!");
            }

            if (!ftp_login($connection, $this->username, $this->password)) {
                throw new \RuntimeException("FTP login to '" . $this->host . ":" . $this->port . "' for user '" . $this->username . "' has failed!");
            }

            $this->connection = $connection;
        }

        return $this->connection;
    }

    /**
     * @throws \LogicException
     */
    public function login()
    {
        throw new \LogicException("Method login() shouldn't be called directly! Login process is handled in connect() method.");
    }

    /**
     * Calls native PHP ftp_* functions.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        $functionName = 'ftp_' . $name;

        if (!function_exists($functionName)) {
            throw new \BadMethodCallException("Method $name() doesn't exist!");
        }

        //Set connection resource as the first argument
        array_unshift($arguments, $this->connect());

        return call_user_func_array($functionName, $arguments);
    }

    /**
     * Closes FTP connection.
     *
     * @return bool
     */
    public function close()
    {
        $result = false;

        if ($this->connection && $result = ftp_close($this->connection)) {
            $this->connection = null;
        }

        return $result;
    }

    /**
     * Alias for close().
     */
    public function quit()
    {
        return $this->close();
    }

    /**
     * Closes FTP connection.
     */
    public function __destruct()
    {
        $this->close();
    }
}
