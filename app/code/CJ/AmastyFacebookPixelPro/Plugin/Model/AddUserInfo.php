<?php
declare(strict_types=1);

namespace CJ\AmastyFacebookPixelPro\Plugin\Model;

use CJ\AmastyFacebookPixelPro\Model\FaceBookData;

class AddUserInfo
{

    /**
     * @var FaceBookData
     */
    private $faceBookData;

    /**
     * @param FaceBookData $faceBookData
     */
    public function __construct(
        FaceBookData $faceBookData
    ) {
        $this->faceBookData = $faceBookData;
    }

    /**
     * Add more user data
     *
     * @param \Amasty\FacebookPixelPro\Model\UserInfoProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetUserInfo(
        \Amasty\FacebookPixelPro\Model\UserInfoProvider $subject,
        $result
    ) {
        $result['fbp'] = $this->faceBookData->getFbp();
        $result['fbc'] = $this->faceBookData->getFbc();
        $customerSession = $this->faceBookData->getCustomerSession();
        if ($customerSession->isLoggedIn()) {
            $result['external_id'] = hash('sha256', (string)$customerSession->getCustomerId());
            $result['ph'] = hash('sha256', (string)$customerSession->getCustomer()->getMobileNumber());
        }
        return $result;
    }
}
