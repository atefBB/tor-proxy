<?php

/**
 * Use `TOR` as a proxy.
 *
 * @author Atef Ben Ali <atef.bettaib@gmail.com>
 * @see    https://gist.github.com/atefBB/7a43fe65848c9208b59409c145bfa59f
 */
class TorProxy
{
    /** cURL session */
    private $ch;

    public function __construct()
    {
        $torSocks5Proxy = "socks5://127.0.0.1:9050";

        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($this->ch, CURLOPT_PROXY, $torSocks5Proxy);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
    }

    /**
     * Send request.
     *
     * @param  string $url
     * @param  array|null $postParameter
     * @return mixed
     */
    public function sendRequest($url, $postParameter = null)
    {
        if (sizeof($postParameter) > 0) {
            curl_setopt(
                $this->ch,
                CURLOPT_POSTFIELDS,
                $postParameter
            );
        }

        curl_setopt($this->ch, CURLOPT_URL, $url);

        try {
            // @see https://secure.php.net/manual/en/function.curl-exec.php
            return curl_exec($this->ch);
        } catch (Exception $e) {
            throw new Exception(
                sprintf(
                    "Error while sending request using TOR with message %s", $e->getMessage()
                )
            );
        }
    }

    /**
     * Change proxy identity.
     *
     * @return boolean
     */
    public function changeIdentity()
    {
        try {
            $ip   = '127.0.0.1';
            $port = '9051';

            $fp = fsockopen(
                $ip, $port,
                $error_number,
                $err_string, 30
            );

            if (!$fp) {
                throw new Exception(
                    "Error while changing Tor proxy identity: {$error_number} : {$err_string}"
                );
            } else {
                fputs($fp, "AUTHENTICATE \"" . getenv('TOR_CONTROL_PASSWORD') . "\"\r\n");

                // send the request to for new identity
                fputs($fp, "signal NEWNYM\r\n");
                $received          = fread($fp, 1024);
                list($code, $text) = explode(' ', $received, 2);

                if ($code != '250') {
                    // signal failed
                    throw new Exception("Signaling failed");
                }
            }

            fclose($fp);

            return true;
        } catch (Exception $e) {
            throw new Exception(
                sprintf("Error while changing Tor proxy identity: %s", $e->getMessage())
            );
        }
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}
