<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 1/7/21
 * Time: 5:57 PM
 */
namespace Amore\GcrmDataExport\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Filesystem\Io\Ftp as FtpAlias;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * FTP Configurations
 *
 * Class Ftp
 */
class Ftp extends FtpAlias
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Ftp constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Return Custom LS
     *
     * @param $grep
     * @return false|mixed
     */
    public function customLs($grep)
    {
        $ls = @ftp_nlist($this->_conn, $this->pwd());
        $list = [];

        foreach ($ls as $file) {
            if (strpos($file, $grep) !== false) {
                $list[] = ['text' => $file, 'id' => $this->pwd() . '/' . $file];
            }
        }
        sort($list);
        return reset($list);
    }

    /**
     * validate FTP Configurations
     *
     * @param array $args
     * @return bool
     * @throws LocalizedException
     */
    public function open(array $args = [])
    {
        $host = $this->scopeConfig->getValue('customscheduledexport/general/ftp_host');
        if (empty($args['host'])) {
            $this->_error = self::ERROR_EMPTY_HOST;
            throw new LocalizedException(new Phrase('The specified host is empty. Set the host and try again.'));
        }

        if (empty($args['port'])) {
            $args['port'] = 21;
        }

        if (empty($args['user'])) {
            $args['user'] = 'anonymous';
            $args['password'] = 'anonymous@noserver.com';
        }

        if (empty($args['password'])) {
            $args['password'] = '';
        }

        if (empty($args['timeout'])) {
            $args['timeout'] = 90;
        }

        if (empty($args['file_mode'])) {
            $args['file_mode'] = FTP_BINARY;
        }

        $this->_config = $args;

        if ($args['host'] == $host) {
            $this->_conn = @ftp_ssl_connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
        } else {
            if (empty($this->_config['ssl'])) {
                $this->_conn = @ftp_connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
            } else {
                $this->_conn = @ftp_ssl_connect($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
            }
        }

        if (!$this->_conn) {
            $this->_error = self::ERROR_INVALID_CONNECTION;
            throw new LocalizedException(
                new Phrase("The FTP connection couldn't be established because of an invalid host or port.")
            );
        }

        if (!@ftp_login($this->_conn, $this->_config['user'], $this->_config['password'])) {
            $this->_error = self::ERROR_INVALID_LOGIN;
            $this->close();
            throw new LocalizedException(new Phrase('The username or password is invalid. Verify both and try again.'));
        }

        if (!empty($this->_config['path'])) {
            if (!@ftp_chdir($this->_conn, $this->_config['path'])) {
                $this->_error = self::ERROR_INVALID_PATH;
                $this->close();
                throw new LocalizedException(new Phrase('The path is invalid. Verify and try again.'));
            }
        }

        if (!empty($this->_config['passive'])) {
            if (!@ftp_pasv($this->_conn, true)) {
                $this->_error = self::ERROR_INVALID_MODE;
                $this->close();
                throw new LocalizedException(new Phrase('The file transfer mode is invalid. Verify and try again.'));
            }
        }

        return true;
    }
}
