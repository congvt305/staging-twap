<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 19
 * Time: 오후 5:00
 */

namespace Amore\CustomerRegistration\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * It will use for the register step during registration
 * Class Register
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
