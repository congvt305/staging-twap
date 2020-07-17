<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/16/20
 * Time: 5:42 PM
 */

namespace Eguana\CustomerRefund\Model;


class Cryptographer
{
    /**
     * @var \Eguana\CustomerRefund\Helper\Data
     */
    private $dataHelper;

    private $cipher = 'aes-256-cbc';

    public function __construct(\Eguana\CustomerRefund\Helper\Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    public function encode(string $plainText): array
    {
        $cipher = $this->cipher;
        $key = $this->getKey();

        if (in_array($cipher, openssl_get_cipher_methods()))
        {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $base64Iv = base64_encode($iv);
            $ciphertext = openssl_encrypt($plainText, $cipher, $key, $options=0, $iv);
        }
        return ['encrypted' => $ciphertext, 'base64iv' => $base64Iv] ;
    }

    public function decode($cipherText, $base64Iv): string
    {
        $cipher = $this->cipher;
        $key = $this->getKey();
        $iv = base64_decode($base64Iv);
        $plaintext = openssl_decrypt($cipherText, $cipher, $key, $options=0, $iv);
        return $plaintext;
    }

    public function getKey()
    {
        $key = $this->dataHelper->getEncryptionKey();
        $key =  hash('sha256', $key);
        return $key;
    }

}
