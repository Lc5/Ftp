<?php

namespace Lc5;

/**
 * Class Ftp
 *
 * A simple object wrapper around native ftp_* functions. Fully unit-tested.
 *
 * Example usage:
 *
 *  <code>
 *  use Lc5\Ftp;
 *
 *  $ftp = new Ftp('ftp.example.com', 'username', 'password');
 *
 *  //Save remote.txt to local.txt
 *  $ftp->get('local.txt', 'remote.txt', FTP_ASCII);
 *
 *  //Actually you don't have to explicitly call close()
 *  //It will get called automatically as a part of the __destruct() method
 *  $ftp->close();
 *
 *  //For anonymous login you only need to pass the host address
 *  $ftp = new Ftp('ftp.example.com');
 *  $ftp->pasv(true);
 *
 *  //Get list of files in current directory and print them
 *  $files = $ftp->rawlist('.');
 *
 *  foreach ($files as $file) {
 *      echo $file . PHP_EOL;
 *  }
 *  </code>
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

    /**
     * Sets FTP connection params.
     *
     * @param string $host
     * @param string $username
     * @param string $password
     * @param int $port
     * @param int $timeout
     */
    public function __construct($host, $username = 'anonymous', $password = '', $port = 21, $timeout = 90)
    {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port     = $port;
        $this->timeout  = $timeout;
    }

    /**
     * Connects and logs in to FTP server.
     *
     * @return resource $connection
     * @throws \RuntimeException
     */
    public function connect()
    {
        if ($this->connection === null) {

            if (!$connection = ftp_connect($this->host, $this->port, $this->timeout)) {
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