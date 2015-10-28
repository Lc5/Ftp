Ftp
===

A simple object wrapper around native ftp_* functions. Fully unit-tested.

Example usage:

```php
use Lc5\Ftp;

$ftp = new Ftp('ftp.example.com', 'username', 'password');

//Save remote.txt to local.txt
$ftp->get('local.txt', 'remote.txt', FTP_ASCII);

//Actually you don't have to explicitly call close()
//It will get called automatically as a part of the __destruct() method
$ftp->close();

//For anonymous login you only need to pass the host address
$ftp = new Ftp('ftp.example.com');
$ftp->pasv(true);

//Get list of files in current directory and print them
$files = $ftp->rawlist('.');

foreach ($files as $file) {
    echo $file . PHP_EOL;
}
```
