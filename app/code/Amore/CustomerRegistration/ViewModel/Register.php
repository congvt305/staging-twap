<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 19
 * Time: 오후 5:00
 *
 * PHP version 7.3.18
 *
 * @category PHP_FILE
 * @package  Eguana
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 */

namespace Amore\CustomerRegistration\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * It will use for the register step during registration
 * Class Register
 *
 * @category PHP_FILE
 * @package  Amore\CustomerRegistration\ViewModel
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 */
class Register implements ArgumentInterface
{

    /**
     * Http
     *
     * @var Http
     */
    private $request;

    /**
     * Register constructor.
     *
     * @param Http $request request
     */
    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    /**
     * Get the referrer code
     * It will return the referrer code from the get parameter
     *
     * @return mixed
     */
    public function getReferrerCode()
    {
        return $this->request->getParam('referrer_code', '');
    }

    /**
     * Get the favorite store
     * It will return the favorite store from the get parameter
     *
     * @return mixed
     */
    public function getFavoriteStore()
    {
        return $this->request->getParam('favorite_store', '');
    }
}