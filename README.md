# cURL Connections with Tor

Inspired by [sxss](https://gist.github.com/sxss/acfdce73976f219a6695).

Install Apache, PHP, CURL & Tor with apt-get:

```bash
sudo apt-get install -y apache2 php5 php5-curl tor
```

Tor creates a proxy on your machine with port 9050 for SOCKS5 connections.

Use the Proxy class for a GET request:

```php
$proxy = new TorProxy();
$proxy->changeIdentity();
echo $proxy->curl( "http://check.torproject.org" );
```

Use the Proxy class for a POST request:

```php
$proxy = new TorProxy();
$parameters = array(
	'parameter1' => 'value1',
	'parameter2' => 'value2'
);
echo $proxy->curl("http://check.torproject.org", $parameters);
```
