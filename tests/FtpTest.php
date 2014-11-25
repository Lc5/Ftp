<?php

namespace Lc5;

/**
 * Class FtpTest
 *
 * @author Łukasz Krzyszczak <lukasz.krzyszczak@gmail.com>
 */
class FtpTest extends \PHPUnit_Framework_TestCase
{
    /** @var Ftp */
    private $ftp;

    protected function setUp()
    {
        $this->ftp = new Ftp('ftp.example.com', 'username', 'password');
    }

    public function testFtpConnects()
    {
        $this->assertTrue($this->ftp->connect());
    }

    public function testFtpGetsFile()
    {
        $result = $this->ftp->get('local.txt', 'remote.txt', FTP_ASCII);

        $this->assertEquals('ftp_get', $result['callback']);
        $this->assertTrue($result['params'][0]);
        $this->assertEquals('local.txt', $result['params'][1]);
        $this->assertEquals('remote.txt', $result['params'][2]);
        $this->assertEquals(FTP_ASCII, $result['params'][3]);
    }

    public function testFtpClosesConnection()
    {
        $this->ftp->connect();
        $this->assertTrue($this->ftp->close());

        $this->ftp->connect();
        $this->assertTrue($this->ftp->quit());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage FTP connection to 'invalid.example.com:21' has failed!
     */
    public function testFtpInvalidHostThrowsException()
    {
        $ftp =  new Ftp('invalid.example.com', 'username', 'password');
        $ftp->connect();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage FTP login to 'ftp.example.com:21' for user 'invalidUsername' has failed!
     */
    public function testFtpInvalidLoginThrowsException()
    {
        $ftp =  new Ftp('ftp.example.com', 'invalidUsername', 'invalidPassword');
        $ftp->connect();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Method nonExistentMethod() doesn't exist!
     */
    public function testFtpNonExistentMethodCalledThrowsException()
    {
        $this->ftp->nonExistentMethod();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Method login() shouldn't be called directly! Login process is handled in connect() method.
     */
    public function testFtpLoginThrowsException()
    {
        $this->ftp->login();
    }

    protected function tearDown()
    {
        $this->ftp = null;
    }
}

//Mock some internal PHP functions

/**
 * Returns an array of arguments with which was called.
 *
 * @param callable $callback
 * @param array $params
 * @return array
 */
function call_user_func_array(callable $callback , array $params)
{
    return [
        'callback' => $callback,
        'params' => $params
    ];
}

/**
 * Mocks FTP connection.
 *
 * @param string $host
 * @param int $port
 * @param int $timeout
 * @return bool
 */
function ftp_connect($host, $port = 21, $timeout = 90)
{
    return $host == 'ftp.example.com' &&
           $port == '21';
}

/**
 * Mocks FTP login process.
 *
 * @param resource $connection
 * @param string $username
 * @param string $password
 * @return bool
 */
function ftp_login($connection, $username, $password)
{
    return $connection &&
           $username == 'username' &&
           $password == 'password';
}

/**
 * Mocks FTP connection closing.
 *
 * @param resource $connection
 * @return bool
 */
function ftp_close($connection)
{
    return true;
}