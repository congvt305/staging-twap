<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 10/10/20
 * Time: 1:49 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Test\Integration\Controller\SelectCvs;

use Eguana\GWLogistics\Model\Carrier\CvsStorePickup;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class IndexTest
 * @see \Eguana\GWLogistics\Controller\SelectCvs\Index
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutSession = $this->_objectManager->get(CheckoutSession::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown():void
    {
        $this->_objectManager->removeSharedInstance(CheckoutSession::class);
        parent::tearDown();
    }

    /**
     * @dataProvider missingParametersDataProvider
     * @param string|null $cvsType
     */
    public function testIncorrectParameters(?string $cvsType): void
    {
        $errorHtml = '<script>window.close();</script>';
        $this->prepareRequest($cvsType);
        $this->dispatch('eguana_gwlogistics/selectcvs/index');
        $this->assertContains($errorHtml, $this->getResponse()->getBody());

    }

    /**
     * @dataProvider correctParametersDataProvider
     * @param string|null $cvsType
     */
    public function testCorrectResponse(?string $cvsType, string $expectedHtmlString): void
    {
        $this->prepareRequest($cvsType);
        $this->dispatch('eguana_gwlogistics/selectcvs/index');
        $this->assertContains($expectedHtmlString, $this->getResponse()->getBody());

    }

    /**
     * @param string|null $cvsType
     */
    private function prepareRequest(?string $cvsType): void
    {
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue([
            'cvs_type' => $cvsType,
        ]);
    }

    /**
     * @return array
     */
    public function missingParametersDataProvider(): array
    {
        return [
            'missing_cvs_type' => [
                'cvs_type' => null,
            ],
            'wrong_cvs_type' => [
                'cvs_type' => 'UNIART',
            ]
        ];
    }

    /**
     * @return array|array[]
     */
    public function correctParametersDataProvider(): array
    {
        return [
            'cvs_type_unimart' => [
                'cvs_type' => CvsStorePickup::SEVEN_ELEVEN_CODE,
                'expected_html_string' => CvsStorePickup::SEVEN_ELEVEN_CODE
            ],
            'cvs_type_fami' => [
                'cvs_type' => CvsStorePickup::FAMILY_MART_CODE,
                'expected_html_string' => CvsStorePickup::FAMILY_MART_CODE
            ]
        ];
    }

}