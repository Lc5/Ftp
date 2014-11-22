Ftp
===

A simple object wrapper around PHP ftp_* functions. Fully unit-tested.

Example usage:

```php
use Lc5\Ftp;

$ftp = new Ftp('ftp.example.com', 'username', 'password');
$ftp->get('local.txt', 'remote.txt', FTP_ASCII);
$ftp->close();
```
You don't have to call close() method if you don't want to. It will get called automatically as a part of the __destruct() method.

For anonymous login just call:

```php
$ftp = new Ftp('ftp.example.com');
```