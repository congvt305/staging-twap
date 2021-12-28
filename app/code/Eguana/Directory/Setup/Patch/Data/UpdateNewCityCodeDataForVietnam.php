<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eguana\Directory\Setup\Patch\Data;

use Eguana\Directory\Setup\RegionCityZipcodeDataInstaller;
use Eguana\Directory\Setup\RegionCityZipcodeDataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class UpdateNewCityCodeDataForVietnam implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var RegionCityZipcodeDataInstallerFactory
     */
    private $regionCityZipcodeDataInstallerFactory;

    /**
     * AddNewDataForVietnam constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RegionCityZipcodeDataInstallerFactory $regionCityZipcodeDataInstallerFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RegionCityZipcodeDataInstallerFactory $regionCityZipcodeDataInstallerFactory

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->regionCityZipcodeDataInstallerFactory = $regionCityZipcodeDataInstallerFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /** @var RegionCityZipcodeDataInstaller $regionCityDataInstaller */
        $regionCityDataInstaller = $this->regionCityZipcodeDataInstallerFactory->create();
        $regionCityDataInstaller->updateGhnCodeForCity($this->moduleDataSetup->getConnection(), $this->getDataForVietnam());
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [\Eguana\Directory\Setup\Patch\Data\AddNewDataForVietnam::class];
    }

    /**
     * @return array[]
     */
    public function getDataForVietnam()
    {
        return
            array (
                0 =>
                    array (
                        0 => '1890',
                        1 => 'Huyện Bảo Lâm',
                        2 => '21000',
                    ),
                1 =>
                    array (
                        0 => '1566',
                        1 => 'Thành phố Long Xuyên',
                        2 => '90000',
                    ),
                2 =>
                    array (
                        0 => '1753',
                        1 => 'Thành phố Châu Đốc',
                        2 => '90000',
                    ),
                3 =>
                    array (
                        0 => '1754',
                        1 => 'Huyện An Phú',
                        2 => '90000',
                    ),
                4 =>
                    array (
                        0 => '1755',
                        1 => 'Thị Xã Tân Châu',
                        2 => '90000',
                    ),
                5 =>
                    array (
                        0 => '1756',
                        1 => 'Huyện Phú Tân',
                        2 => '90000',
                    ),
                6 =>
                    array (
                        0 => '1758',
                        1 => 'Huyện Châu Phú',
                        2 => '90000',
                    ),
                7 =>
                    array (
                        0 => '1752',
                        1 => 'Huyện Tịnh Biên',
                        2 => '90000',
                    ),
                8 =>
                    array (
                        0 => '1751',
                        1 => 'Huyện Tri Tôn',
                        2 => '90000',
                    ),
                9 =>
                    array (
                        0 => '1718',
                        1 => 'Huyện Châu Thành',
                        2 => '90000',
                    ),
                10 =>
                    array (
                        0 => '1757',
                        1 => 'Huyện Chợ Mới',
                        2 => '90000',
                    ),
                11 =>
                    array (
                        0 => '1750',
                        1 => 'Huyện Thoại Sơn',
                        2 => '90000',
                    ),
                12 =>
                    array (
                        0 => '1544',
                        1 => 'Thành phố Vũng Tàu',
                        2 => '78000',
                    ),
                13 =>
                    array (
                        0 => '1667',
                        1 => 'Thành phố Bà Rịa',
                        2 => '78000',
                    ),
                14 =>
                    array (
                        0 => '1709',
                        1 => 'Huyện Châu Đức',
                        2 => '78000',
                    ),
                15 =>
                    array (
                        0 => '1699',
                        1 => 'Huyện Xuyên Mộc',
                        2 => '78000',
                    ),
                16 =>
                    array (
                        0 => '1689',
                        1 => 'Huyện Long Điền',
                        2 => '78000',
                    ),
                17 =>
                    array (
                        0 => '1690',
                        1 => 'Huyện Đất Đỏ',
                        2 => '78000',
                    ),
                18 =>
                    array (
                        0 => '1701',
                        1 => 'Thị Xã Phú Mỹ',
                        2 => '78000',
                    ),
                19 =>
                    array (
                        0 => '1643',
                        1 => 'Thành phố Bắc Giang',
                        2 => '26000',
                    ),
                20 =>
                    array (
                        0 => '1765',
                        1 => 'Huyện Yên Thế',
                        2 => '26000',
                    ),
                21 =>
                    array (
                        0 => '1762',
                        1 => 'Huyện Tân Yên',
                        2 => '26000',
                    ),
                22 =>
                    array (
                        0 => '1760',
                        1 => 'Huyện Lạng Giang',
                        2 => '26000',
                    ),
                23 =>
                    array (
                        0 => '1965',
                        1 => 'Huyện Lục Nam',
                        2 => '26000',
                    ),
                24 =>
                    array (
                        0 => '1966',
                        1 => 'Huyện Lục Ngạn',
                        2 => '26000',
                    ),
                25 =>
                    array (
                        0 => '1761',
                        1 => 'Huyện Sơn Động',
                        2 => '26000',
                    ),
                26 =>
                    array (
                        0 => '1764',
                        1 => 'Huyện Yên Dũng',
                        2 => '26000',
                    ),
                27 =>
                    array (
                        0 => '1763',
                        1 => 'Huyện Việt Yên',
                        2 => '26000',
                    ),
                28 =>
                    array (
                        0 => '1759',
                        1 => 'Huyện Hiệp Hòa',
                        2 => '26000',
                    ),
                29 =>
                    array (
                        0 => '1640',
                        1 => 'Thành phố Bắc Kạn',
                        2 => '23000',
                    ),
                30 =>
                    array (
                        0 => '3249',
                        1 => 'Huyện Pác Nặm',
                        2 => '23000',
                    ),
                31 =>
                    array (
                        0 => '1887',
                        1 => 'Huyện Ba Bể',
                        2 => '23000',
                    ),
                32 =>
                    array (
                        0 => '3242',
                        1 => 'Huyện Ngân Sơn',
                        2 => '23000',
                    ),
                33 =>
                    array (
                        0 => '1889',
                        1 => 'Huyện Bạch Thông',
                        2 => '23000',
                    ),
                34 =>
                    array (
                        0 => '1913',
                        1 => 'Huyện Chợ Đồn',
                        2 => '23000',
                    ),
                35 =>
                    array (
                        0 => '1914',
                        1 => 'Huyện Chợ Mới',
                        2 => '23000',
                    ),
                36 =>
                    array (
                        0 => '3232',
                        1 => 'Huyện Na Rì',
                        2 => '23000',
                    ),
                37 =>
                    array (
                        0 => '1655',
                        1 => 'Thành phố Bạc Liêu',
                        2 => '97000',
                    ),
                38 =>
                    array (
                        0 => '1946',
                        1 => 'Huyện Hồng Dân',
                        2 => '97000',
                    ),
                39 =>
                    array (
                        0 => '1998',
                        1 => 'Huyện Phước Long',
                        2 => '97000',
                    ),
                40 =>
                    array (
                        0 => '2050',
                        1 => 'Huyện Vĩnh Lợi',
                        2 => '97000',
                    ),
                41 =>
                    array (
                        0 => '1935',
                        1 => 'Thị Xã Giá Rai',
                        2 => '97000',
                    ),
                42 =>
                    array (
                        0 => '1926',
                        1 => 'Huyện Đông Hải',
                        2 => '97000',
                    ),
                43 =>
                    array (
                        0 => '1723',
                        1 => 'Huyện Hòa Bình',
                        2 => '97000',
                    ),
                44 =>
                    array (
                        0 => '1644',
                        1 => 'Thành phố Bắc Ninh',
                        2 => '16000',
                    ),
                45 =>
                    array (
                        0 => '1768',
                        1 => 'Huyện Yên Phong',
                        2 => '16000',
                    ),
                46 =>
                    array (
                        0 => '1728',
                        1 => 'Huyện Quế Võ',
                        2 => '16000',
                    ),
                47 =>
                    array (
                        0 => '1729',
                        1 => 'Huyện Tiên Du',
                        2 => '16000',
                    ),
                48 =>
                    array (
                        0 => '1730',
                        1 => 'Thị xã Từ Sơn',
                        2 => '16000',
                    ),
                49 =>
                    array (
                        0 => '1767',
                        1 => 'Huyện Thuận Thành',
                        2 => '16000',
                    ),
                50 =>
                    array (
                        0 => '1766',
                        1 => 'Huyện Gia Bình',
                        2 => '16000',
                    ),
                51 =>
                    array (
                        0 => '1969',
                        1 => 'Huyện Lương Tài',
                        2 => '16000',
                    ),
                52 =>
                    array (
                        0 => '1558',
                        1 => 'Thành phố Bến Tre',
                        2 => '86000',
                    ),
                53 =>
                    array (
                        0 => '1742',
                        1 => 'Huyện Châu Thành',
                        2 => '86000',
                    ),
                54 =>
                    array (
                        0 => '3158',
                        1 => 'Huyện Chợ Lách',
                        2 => '86000',
                    ),
                55 =>
                    array (
                        0 => '1975',
                        1 => 'Huyện Mỏ Cày Nam',
                        2 => '86000',
                    ),
                56 =>
                    array (
                        0 => '1937',
                        1 => 'Huyện Giồng Trôm',
                        2 => '86000',
                    ),
                57 =>
                    array (
                        0 => '1895',
                        1 => 'Huyện Bình Đại',
                        2 => '86000',
                    ),
                58 =>
                    array (
                        0 => '1888',
                        1 => 'Huyện Ba Tri',
                        2 => '86000',
                    ),
                59 =>
                    array (
                        0 => '2028',
                        1 => 'Huyện Thạnh Phú',
                        2 => '86000',
                    ),
                60 =>
                    array (
                        0 => '1974',
                        1 => 'Huyện Mỏ Cày Bắc',
                        2 => '86000',
                    ),
                61 =>
                    array (
                        0 => '1662',
                        1 => 'Thành phố Quy Nhơn',
                        2 => '55000',
                    ),
                62 =>
                    array (
                        0 => '1886',
                        1 => 'Huyện An Lão',
                        2 => '55000',
                    ),
                63 =>
                    array (
                        0 => '1771',
                        1 => 'Thị xã Hoài Nhơn',
                        2 => '55000',
                    ),
                64 =>
                    array (
                        0 => '2140',
                        1 => 'Huyện Hoài Ân',
                        2 => '55000',
                    ),
                65 =>
                    array (
                        0 => '3254',
                        1 => 'Huyện Phù Mỹ',
                        2 => '55000',
                    ),
                66 =>
                    array (
                        0 => '2258',
                        1 => 'Huyện Vĩnh Thạnh',
                        2 => '55000',
                    ),
                67 =>
                    array (
                        0 => '3279',
                        1 => 'Huyện Tây Sơn',
                        2 => '55000',
                    ),
                68 =>
                    array (
                        0 => '1770',
                        1 => 'Huyện Phù Cát',
                        2 => '55000',
                    ),
                69 =>
                    array (
                        0 => '1769',
                        1 => 'Thị xã An Nhơn',
                        2 => '55000',
                    ),
                70 =>
                    array (
                        0 => '2023',
                        1 => 'Huyện Tuy Phước',
                        2 => '55000',
                    ),
                71 =>
                    array (
                        0 => '3312',
                        1 => 'Huyện Vân Canh',
                        2 => '55000',
                    ),
                72 =>
                    array (
                        0 => '1538',
                        1 => 'Thành phố Thủ Dầu Một',
                        2 => '75000',
                    ),
                73 =>
                    array (
                        0 => '3132',
                        1 => 'Huyện Bàu Bàng',
                        2 => '75000',
                    ),
                74 =>
                    array (
                        0 => '1746',
                        1 => 'Huyện Dầu Tiếng',
                        2 => '75000',
                    ),
                75 =>
                    array (
                        0 => '1696',
                        1 => 'Thị xã Bến Cát',
                        2 => '75000',
                    ),
                76 =>
                    array (
                        0 => '1992',
                        1 => 'Huyện Phú Giáo',
                        2 => '75000',
                    ),
                77 =>
                    array (
                        0 => '1695',
                        1 => 'Thị xã Tân Uyên',
                        2 => '75000',
                    ),
                78 =>
                    array (
                        0 => '1540',
                        1 => 'Thành phố Dĩ An',
                        2 => '75000',
                    ),
                79 =>
                    array (
                        0 => '1541',
                        1 => 'Thành phố Thuận An',
                        2 => '75000',
                    ),
                80 =>
                    array (
                        0 => '3135',
                        1 => 'Huyện Bắc Tân Uyên',
                        2 => '75000',
                    ),
                81 =>
                    array (
                        0 => '1775',
                        1 => 'Thị xã Phước Long',
                        2 => '67000',
                    ),
                82 =>
                    array (
                        0 => '1625',
                        1 => 'Thành phố Đồng Xoài',
                        2 => '67000',
                    ),
                83 =>
                    array (
                        0 => '1774',
                        1 => 'Thị xã Bình Long',
                        2 => '67000',
                    ),
                84 =>
                    array (
                        0 => '3141',
                        1 => 'Huyện Bù Gia Mập',
                        2 => '67000',
                    ),
                85 =>
                    array (
                        0 => '1964',
                        1 => 'Huyện Lộc Ninh',
                        2 => '67000',
                    ),
                86 =>
                    array (
                        0 => '3140',
                        1 => 'Huyện Bù Đốp',
                        2 => '67000',
                    ),
                87 =>
                    array (
                        0 => '1773',
                        1 => 'Huyện Hớn Quản',
                        2 => '67000',
                    ),
                88 =>
                    array (
                        0 => '1722',
                        1 => 'Huyện Đồng Phú',
                        2 => '67000',
                    ),
                89 =>
                    array (
                        0 => '1899',
                        1 => 'Huyện Bù Đăng',
                        2 => '67000',
                    ),
                90 =>
                    array (
                        0 => '1772',
                        1 => 'Huyện Chơn Thành',
                        2 => '67000',
                    ),
                91 =>
                    array (
                        0 => '3444',
                        1 => 'Huyện Phú Riềng',
                        2 => '67000',
                    ),
                92 =>
                    array (
                        0 => '1666',
                        1 => 'Thành phố Phan Thiết',
                        2 => '77000',
                    ),
                93 =>
                    array (
                        0 => '1778',
                        1 => 'Thị xã La Gi',
                        2 => '77000',
                    ),
                94 =>
                    array (
                        0 => '1781',
                        1 => 'Huyện Tuy Phong',
                        2 => '77000',
                    ),
                95 =>
                    array (
                        0 => '1780',
                        1 => 'Huyện Bắc Bình',
                        2 => '77000',
                    ),
                96 =>
                    array (
                        0 => '1777',
                        1 => 'Huyện Hàm Thuận Bắc',
                        2 => '77000',
                    ),
                97 =>
                    array (
                        0 => '1776',
                        1 => 'Huyện Hàm Thuận Nam',
                        2 => '77000',
                    ),
                98 =>
                    array (
                        0 => '2012',
                        1 => 'Huyện Tánh Linh',
                        2 => '77000',
                    ),
                99 =>
                    array (
                        0 => '1779',
                        1 => 'Huyện Đức Linh',
                        2 => '77000',
                    ),
                100 =>
                    array (
                        0 => '3196',
                        1 => 'Huyện Hàm Tân',
                        2 => '77000',
                    ),
                101 =>
                    array (
                        0 => '2116',
                        1 => 'Huyện Phú Quý',
                        2 => '77000',
                    ),
                102 =>
                    array (
                        0 => '1654',
                        1 => 'Thành phố Cà Mau',
                        2 => '98000',
                    ),
                103 =>
                    array (
                        0 => '2042',
                        1 => 'Huyện U Minh',
                        2 => '98000',
                    ),
                104 =>
                    array (
                        0 => '1782',
                        1 => 'Huyện Thới Bình',
                        2 => '98000',
                    ),
                105 =>
                    array (
                        0 => '2038',
                        1 => 'Huyện Trần Văn Thời',
                        2 => '98000',
                    ),
                106 =>
                    array (
                        0 => '1901',
                        1 => 'Huyện Cái Nước',
                        2 => '98000',
                    ),
                107 =>
                    array (
                        0 => '1922',
                        1 => 'Huyện Đầm Dơi',
                        2 => '98000',
                    ),
                108 =>
                    array (
                        0 => '1783',
                        1 => 'Huyện Năm Căn',
                        2 => '98000',
                    ),
                109 =>
                    array (
                        0 => '1883',
                        1 => 'Huyện Phú Tân',
                        2 => '98000',
                    ),
                110 =>
                    array (
                        0 => '2186',
                        1 => 'Huyện Ngọc Hiển',
                        2 => '98000',
                    ),
                111 =>
                    array (
                        0 => '1572',
                        1 => 'Quận Ninh Kiều',
                        2 => '94000',
                    ),
                112 =>
                    array (
                        0 => '1575',
                        1 => 'Quận Ô Môn',
                        2 => '94000',
                    ),
                113 =>
                    array (
                        0 => '1573',
                        1 => 'Quận Bình Thủy',
                        2 => '94000',
                    ),
                114 =>
                    array (
                        0 => '1574',
                        1 => 'Quận Cái Răng',
                        2 => '94000',
                    ),
                115 =>
                    array (
                        0 => '1576',
                        1 => 'Quận Thốt Nốt',
                        2 => '94000',
                    ),
                116 =>
                    array (
                        0 => '3317',
                        1 => 'Huyện Vĩnh Thạnh',
                        2 => '94000',
                    ),
                117 =>
                    array (
                        0 => '3150',
                        1 => 'Huyện Cờ Đỏ',
                        2 => '94000',
                    ),
                118 =>
                    array (
                        0 => '3250',
                        1 => 'Huyện Phong Điền',
                        2 => '94000',
                    ),
                119 =>
                    array (
                        0 => '3300',
                        1 => 'Huyện Thới Lai',
                        2 => '94000',
                    ),
                120 =>
                    array (
                        0 => '1641',
                        1 => 'Thành phố Cao Bằng',
                        2 => '21000',
                    ),
                121 =>
                    array (
                        0 => '3130',
                        1 => 'Huyện Bảo Lạc',
                        2 => '21000',
                    ),
                122 =>
                    array (
                        0 => '3299',
                        1 => 'Huyện Thông Nông',
                        2 => '21000',
                    ),
                123 =>
                    array (
                        0 => '1939',
                        1 => 'Huyện Hà Quảng',
                        2 => '21000',
                    ),
                124 =>
                    array (
                        0 => '3305',
                        1 => 'Huyện Trà Lĩnh',
                        2 => '21000',
                    ),
                125 =>
                    array (
                        0 => '2041',
                        1 => 'Huyện Trùng Khánh',
                        2 => '21000',
                    ),
                126 =>
                    array (
                        0 => '3194',
                        1 => 'Huyện Hạ Lang',
                        2 => '21000',
                    ),
                127 =>
                    array (
                        0 => '3259',
                        1 => 'Huyện Quảng Uyên',
                        2 => '21000',
                    ),
                128 =>
                    array (
                        0 => '1997',
                        1 => 'Huyện Phục Hòa',
                        2 => '21000',
                    ),
                129 =>
                    array (
                        0 => '1943',
                        1 => 'Huyện Hòa An',
                        2 => '21000',
                    ),
                130 =>
                    array (
                        0 => '3246',
                        1 => 'Huyện Nguyên Bình',
                        2 => '21000',
                    ),
                131 =>
                    array (
                        0 => '3289',
                        1 => 'Huyện Thạch An',
                        2 => '21000',
                    ),
                132 =>
                    array (
                        0 => '1530',
                        1 => 'Quận Liên Chiểu',
                        2 => '50000',
                    ),
                133 =>
                    array (
                        0 => '1527',
                        1 => 'Quận Thanh Khê',
                        2 => '50000',
                    ),
                134 =>
                    array (
                        0 => '1526',
                        1 => 'Quận Hải Châu',
                        2 => '50000',
                    ),
                135 =>
                    array (
                        0 => '1528',
                        1 => 'Quận Sơn Trà',
                        2 => '50000',
                    ),
                136 =>
                    array (
                        0 => '1529',
                        1 => 'Quận Ngũ Hành Sơn',
                        2 => '50000',
                    ),
                137 =>
                    array (
                        0 => '1531',
                        1 => 'Quận Cẩm Lệ',
                        2 => '50000',
                    ),
                138 =>
                    array (
                        0 => '1687',
                        1 => 'Huyện Hòa Vang',
                        2 => '50000',
                    ),
                139 =>
                    array (
                        0 => '1552',
                        1 => 'Thành phố Buôn Ma Thuột',
                        2 => '63000',
                    ),
                140 =>
                    array (
                        0 => '1788',
                        1 => 'Thị xã Buôn Hồ',
                        2 => '63000',
                    ),
                141 =>
                    array (
                        0 => '1786',
                        1 => 'Huyện Ea H\'Leo',
                        2 => '63000',
                    ),
                142 =>
                    array (
                        0 => '2131',
                        1 => 'Huyện Ea Súp',
                        2 => '63000',
                    ),
                143 =>
                    array (
                        0 => '1784',
                        1 => 'Huyện Buôn Đôn',
                        2 => '63000',
                    ),
                144 =>
                    array (
                        0 => '1785',
                        1 => 'Huyện Cư M\'Gar',
                        2 => '63000',
                    ),
                145 =>
                    array (
                        0 => '2150',
                        1 => 'Huyện Krông Búk',
                        2 => '63000',
                    ),
                146 =>
                    array (
                        0 => '1787',
                        1 => 'Huyện Krông Năng',
                        2 => '63000',
                    ),
                147 =>
                    array (
                        0 => '1931',
                        1 => 'Huyện Ea Kar',
                        2 => '63000',
                    ),
                148 =>
                    array (
                        0 => '3418',
                        1 => 'Huyện M\'Đrắk',
                        2 => '63000',
                    ),
                149 =>
                    array (
                        0 => '1789',
                        1 => 'Huyện Krông Bông',
                        2 => '63000',
                    ),
                150 =>
                    array (
                        0 => '1954',
                        1 => 'Huyện Krông Pắc',
                        2 => '63000',
                    ),
                151 =>
                    array (
                        0 => '1884',
                        1 => 'Huyện Krông A Na',
                        2 => '63000',
                    ),
                152 =>
                    array (
                        0 => '3217',
                        1 => 'Huyện Lắk',
                        2 => '63000',
                    ),
                153 =>
                    array (
                        0 => '3153',
                        1 => 'Huyện Cư Kuin',
                        2 => '63000',
                    ),
                154 =>
                    array (
                        0 => '1627',
                        1 => 'Thành phố Gia Nghĩa',
                        2 => '65000',
                    ),
                155 =>
                    array (
                        0 => '1791',
                        1 => 'Huyện Đắk Glong',
                        2 => '65000',
                    ),
                156 =>
                    array (
                        0 => '3152',
                        1 => 'Huyện Cư Jút',
                        2 => '65000',
                    ),
                157 =>
                    array (
                        0 => '1792',
                        1 => 'Huyện Đắk Mil',
                        2 => '65000',
                    ),
                158 =>
                    array (
                        0 => '2151',
                        1 => 'Huyện Krông Nô',
                        2 => '65000',
                    ),
                159 =>
                    array (
                        0 => '2120',
                        1 => 'Huyện Đắk Song',
                        2 => '65000',
                    ),
                160 =>
                    array (
                        0 => '1790',
                        1 => 'Huyện Đắk R\'Lấp',
                        2 => '65000',
                    ),
                161 =>
                    array (
                        0 => '2227',
                        1 => 'Huyện Tuy Đức',
                        2 => '65000',
                    ),
                162 =>
                    array (
                        0 => '1676',
                        1 => 'Thành phố Điện Biên Phủ',
                        2 => '32000',
                    ),
                163 =>
                    array (
                        0 => '2060',
                        1 => 'Thị xã Mường Lay',
                        2 => '32000',
                    ),
                164 =>
                    array (
                        0 => '1979',
                        1 => 'Huyện Mường Nhé',
                        2 => '32000',
                    ),
                165 =>
                    array (
                        0 => '1978',
                        1 => 'Huyện Mường Chà',
                        2 => '32000',
                    ),
                166 =>
                    array (
                        0 => '2021',
                        1 => 'Huyện Tủa Chùa',
                        2 => '32000',
                    ),
                167 =>
                    array (
                        0 => '2022',
                        1 => 'Huyện Tuần Giáo',
                        2 => '32000',
                    ),
                168 =>
                    array (
                        0 => '1923',
                        1 => 'Huyện Điện Biên',
                        2 => '32000',
                    ),
                169 =>
                    array (
                        0 => '2123',
                        1 => 'Huyện Điện Biên Đông',
                        2 => '32000',
                    ),
                170 =>
                    array (
                        0 => '2170',
                        1 => 'Huyện Mường Ảng',
                        2 => '32000',
                    ),
                171 =>
                    array (
                        0 => '2179',
                        1 => 'Huyện Nậm Pồ',
                        2 => '32000',
                    ),
                172 =>
                    array (
                        0 => '1536',
                        1 => 'Thành phố Biên Hòa',
                        2 => '76000',
                    ),
                173 =>
                    array (
                        0 => '1692',
                        1 => 'Thành phố Long Khánh',
                        2 => '76000',
                    ),
                174 =>
                    array (
                        0 => '1693',
                        1 => 'Huyện Tân Phú',
                        2 => '76000',
                    ),
                175 =>
                    array (
                        0 => '2049',
                        1 => 'Huyện Vĩnh Cửu',
                        2 => '76000',
                    ),
                176 =>
                    array (
                        0 => '1700',
                        1 => 'Huyện Định Quán',
                        2 => '76000',
                    ),
                177 =>
                    array (
                        0 => '1691',
                        1 => 'Huyện Trảng Bom',
                        2 => '76000',
                    ),
                178 =>
                    array (
                        0 => '1705',
                        1 => 'Huyện Thống Nhất',
                        2 => '76000',
                    ),
                179 =>
                    array (
                        0 => '1702',
                        1 => 'Huyện Cẩm Mỹ',
                        2 => '76000',
                    ),
                180 =>
                    array (
                        0 => '1694',
                        1 => 'Huyện Long Thành',
                        2 => '76000',
                    ),
                181 =>
                    array (
                        0 => '1704',
                        1 => 'Huyện Xuân Lộc',
                        2 => '76000',
                    ),
                182 =>
                    array (
                        0 => '1708',
                        1 => 'Huyện Nhơn Trạch',
                        2 => '76000',
                    ),
                183 =>
                    array (
                        0 => '1564',
                        1 => 'Thành phố Cao Lãnh',
                        2 => '81000',
                    ),
                184 =>
                    array (
                        0 => '1668',
                        1 => 'Thành phố Sa Đéc',
                        2 => '81000',
                    ),
                185 =>
                    array (
                        0 => '2059',
                        1 => 'Thị xã Hồng Ngự',
                        2 => '81000',
                    ),
                186 =>
                    array (
                        0 => '2013',
                        1 => 'Huyện Tân Hồng',
                        2 => '81000',
                    ),
                187 =>
                    array (
                        0 => '3200',
                        1 => 'Thành phố Hồng Ngự',
                        2 => '81000',
                    ),
                188 =>
                    array (
                        0 => '2011',
                        1 => 'Huyện Tam Nông',
                        2 => '81000',
                    ),
                189 =>
                    array (
                        0 => '2030',
                        1 => 'Huyện Tháp Mười',
                        2 => '81000',
                    ),
                190 =>
                    array (
                        0 => '1724',
                        1 => 'Huyện Cao Lãnh',
                        2 => '81000',
                    ),
                191 =>
                    array (
                        0 => '2026',
                        1 => 'Huyện Thanh Bình',
                        2 => '81000',
                    ),
                192 =>
                    array (
                        0 => '1961',
                        1 => 'Huyện Lấp Vò',
                        2 => '81000',
                    ),
                193 =>
                    array (
                        0 => '1725',
                        1 => 'Huyện Lai Vung',
                        2 => '81000',
                    ),
                194 =>
                    array (
                        0 => '3155',
                        1 => 'Huyện Châu Thành',
                        2 => '81000',
                    ),
                195 =>
                    array (
                        0 => '1546',
                        1 => 'Thành phố Pleiku',
                        2 => '61000',
                    ),
                196 =>
                    array (
                        0 => '1800',
                        1 => 'Thị xã An Khê',
                        2 => '61000',
                    ),
                197 =>
                    array (
                        0 => '1798',
                        1 => 'Thị xã Ayun Pa',
                        2 => '61000',
                    ),
                198 =>
                    array (
                        0 => '2144',
                        1 => 'Huyện Kbang',
                        2 => '61000',
                    ),
                199 =>
                    array (
                        0 => '2118',
                        1 => 'Huyện Đắk Đoa',
                        2 => '61000',
                    ),
                200 =>
                    array (
                        0 => '1801',
                        1 => 'Huyện Chư Păh',
                        2 => '61000',
                    ),
                201 =>
                    array (
                        0 => '1793',
                        1 => 'Huyện Ia Grai',
                        2 => '61000',
                    ),
                202 =>
                    array (
                        0 => '2165',
                        1 => 'Huyện Mang Yang',
                        2 => '61000',
                    ),
                203 =>
                    array (
                        0 => '2149',
                        1 => 'Huyện Kông Chro',
                        2 => '61000',
                    ),
                204 =>
                    array (
                        0 => '1794',
                        1 => 'Huyện Đức Cơ',
                        2 => '61000',
                    ),
                205 =>
                    array (
                        0 => '1795',
                        1 => 'Huyện Chư Prông',
                        2 => '61000',
                    ),
                206 =>
                    array (
                        0 => '1796',
                        1 => 'Huyện Chư Sê',
                        2 => '61000',
                    ),
                207 =>
                    array (
                        0 => '2119',
                        1 => 'Huyện Đắk Pơ',
                        2 => '61000',
                    ),
                208 =>
                    array (
                        0 => '1799',
                        1 => 'Huyện Ia Pa',
                        2 => '61000',
                    ),
                209 =>
                    array (
                        0 => '2152',
                        1 => 'Huyện Krông Pa',
                        2 => '61000',
                    ),
                210 =>
                    array (
                        0 => '1797',
                        1 => 'Huyện Phú Thiện',
                        2 => '61000',
                    ),
                211 =>
                    array (
                        0 => '2101',
                        1 => 'Huyện Chư Pưh',
                        2 => '61000',
                    ),
                212 =>
                    array (
                        0 => '1600',
                        1 => 'Thành phố Hà Giang',
                        2 => '20000',
                    ),
                213 =>
                    array (
                        0 => '1928',
                        1 => 'Huyện Đồng Văn',
                        2 => '20000',
                    ),
                214 =>
                    array (
                        0 => '1973',
                        1 => 'Huyện Mèo Vạc',
                        2 => '20000',
                    ),
                215 =>
                    array (
                        0 => '2053',
                        1 => 'Huyện Yên Minh',
                        2 => '20000',
                    ),
                216 =>
                    array (
                        0 => '1999',
                        1 => 'Huyện Quản Bạ',
                        2 => '20000',
                    ),
                217 =>
                    array (
                        0 => '2256',
                        1 => 'Huyện Vị Xuyên',
                        2 => '20000',
                    ),
                218 =>
                    array (
                        0 => '2075',
                        1 => 'Huyện Bắc Mê',
                        2 => '20000',
                    ),
                219 =>
                    array (
                        0 => '1945',
                        1 => 'Huyện Hoàng Su Phì',
                        2 => '20000',
                    ),
                220 =>
                    array (
                        0 => '2052',
                        1 => 'Huyện Xín Mần',
                        2 => '20000',
                    ),
                221 =>
                    array (
                        0 => '1893',
                        1 => 'Huyện Bắc Quang',
                        2 => '20000',
                    ),
                222 =>
                    array (
                        0 => '2001',
                        1 => 'Huyện Quang Bình',
                        2 => '20000',
                    ),
                223 =>
                    array (
                        0 => '1614',
                        1 => 'Thành phố Phủ Lý',
                        2 => '18000',
                    ),
                224 =>
                    array (
                        0 => '1802',
                        1 => 'Thị xã Duy Tiên',
                        2 => '18000',
                    ),
                225 =>
                    array (
                        0 => '1952',
                        1 => 'Huyện Kim Bảng',
                        2 => '18000',
                    ),
                226 =>
                    array (
                        0 => '2027',
                        1 => 'Huyện Thanh Liêm',
                        2 => '18000',
                    ),
                227 =>
                    array (
                        0 => '1897',
                        1 => 'Huyện Bình Lục',
                        2 => '18000',
                    ),
                228 =>
                    array (
                        0 => '1970',
                        1 => 'Huyện Lý Nhân',
                        2 => '18000',
                    ),
                229 =>
                    array (
                        0 => '1484',
                        1 => 'Quận Ba Đình',
                        2 => '10000',
                    ),
                230 =>
                    array (
                        0 => '1489',
                        1 => 'Quận Hoàn Kiếm',
                        2 => '10000',
                    ),
                231 =>
                    array (
                        0 => '1492',
                        1 => 'Quận Tây Hồ',
                        2 => '10000',
                    ),
                232 =>
                    array (
                        0 => '1491',
                        1 => 'Quận Long Biên',
                        2 => '10000',
                    ),
                233 =>
                    array (
                        0 => '1485',
                        1 => 'Quận Cầu Giấy',
                        2 => '10000',
                    ),
                234 =>
                    array (
                        0 => '1486',
                        1 => 'Quận Đống Đa',
                        2 => '10000',
                    ),
                235 =>
                    array (
                        0 => '1488',
                        1 => 'Quận Hai Bà Trưng',
                        2 => '10000',
                    ),
                236 =>
                    array (
                        0 => '1490',
                        1 => 'Quận Hoàng Mai',
                        2 => '10000',
                    ),
                237 =>
                    array (
                        0 => '1493',
                        1 => 'Quận Thanh Xuân',
                        2 => '10000',
                    ),
                238 =>
                    array (
                        0 => '1583',
                        1 => 'Huyện Sóc Sơn',
                        2 => '10000',
                    ),
                239 =>
                    array (
                        0 => '1582',
                        1 => 'Huyện Đông Anh',
                        2 => '10000',
                    ),
                240 =>
                    array (
                        0 => '1703',
                        1 => 'Huyện Gia Lâm',
                        2 => '10000',
                    ),
                241 =>
                    array (
                        0 => '3440',
                        1 => 'Quận Nam Từ Liêm',
                        2 => '10000',
                    ),
                242 =>
                    array (
                        0 => '1710',
                        1 => 'Huyện Thanh Trì',
                        2 => '10000',
                    ),
                243 =>
                    array (
                        0 => '1482',
                        1 => 'Quận Bắc Từ Liêm',
                        2 => '10000',
                    ),
                244 =>
                    array (
                        0 => '1581',
                        1 => 'Huyện Mê Linh',
                        2 => '10000',
                    ),
                245 =>
                    array (
                        0 => '1542',
                        1 => 'Quận Hà Đông',
                        2 => '10000',
                    ),
                246 =>
                    array (
                        0 => '1711',
                        1 => 'Thị xã Sơn Tây',
                        2 => '10000',
                    ),
                247 =>
                    array (
                        0 => '1803',
                        1 => 'Huyện Ba Vì',
                        2 => '10000',
                    ),
                248 =>
                    array (
                        0 => '1807',
                        1 => 'Huyện Phúc Thọ',
                        2 => '10000',
                    ),
                249 =>
                    array (
                        0 => '1804',
                        1 => 'Huyện Đan Phượng',
                        2 => '10000',
                    ),
                250 =>
                    array (
                        0 => '1805',
                        1 => 'Huyện Hoài Đức',
                        2 => '10000',
                    ),
                251 =>
                    array (
                        0 => '2004',
                        1 => 'Huyện Quốc Oai',
                        2 => '10000',
                    ),
                252 =>
                    array (
                        0 => '1808',
                        1 => 'Huyện Thạch Thất',
                        2 => '10000',
                    ),
                253 =>
                    array (
                        0 => '1915',
                        1 => 'Huyện Chương Mỹ',
                        2 => '10000',
                    ),
                254 =>
                    array (
                        0 => '1809',
                        1 => 'Huyện Thanh Oai',
                        2 => '10000',
                    ),
                255 =>
                    array (
                        0 => '3303',
                        1 => 'Huyện Thường Tín',
                        2 => '10000',
                    ),
                256 =>
                    array (
                        0 => '3255',
                        1 => 'Huyện Phú Xuyên',
                        2 => '10000',
                    ),
                257 =>
                    array (
                        0 => '1810',
                        1 => 'Huyện Ứng Hòa',
                        2 => '10000',
                    ),
                258 =>
                    array (
                        0 => '1806',
                        1 => 'Huyện Mỹ Đức',
                        2 => '10000',
                    ),
                259 =>
                    array (
                        0 => '1618',
                        1 => 'Thành phố Hà Tĩnh',
                        2 => '45000',
                    ),
                260 =>
                    array (
                        0 => '1814',
                        1 => 'Thị xã Hồng Lĩnh',
                        2 => '45000',
                    ),
                261 =>
                    array (
                        0 => '3201',
                        1 => 'Huyện Hương Sơn',
                        2 => '45000',
                    ),
                262 =>
                    array (
                        0 => '3188',
                        1 => 'Huyện Đức Thọ',
                        2 => '45000',
                    ),
                263 =>
                    array (
                        0 => '3320',
                        1 => 'Huyện Vũ Quang',
                        2 => '45000',
                    ),
                264 =>
                    array (
                        0 => '1813',
                        1 => 'Huyện Nghi Xuân',
                        2 => '45000',
                    ),
                265 =>
                    array (
                        0 => '3143',
                        1 => 'Huyện Can Lộc',
                        2 => '45000',
                    ),
                266 =>
                    array (
                        0 => '1812',
                        1 => 'Huyện Hương Khê',
                        2 => '45000',
                    ),
                267 =>
                    array (
                        0 => '2024',
                        1 => 'Huyện Thạch Hà',
                        2 => '45000',
                    ),
                268 =>
                    array (
                        0 => '1815',
                        1 => 'Huyện Cẩm Xuyên',
                        2 => '45000',
                    ),
                269 =>
                    array (
                        0 => '1811',
                        1 => 'Huyện Kỳ Anh',
                        2 => '45000',
                    ),
                270 =>
                    array (
                        0 => '3220',
                        1 => 'Huyện Lộc Hà',
                        2 => '45000',
                    ),
                271 =>
                    array (
                        0 => '3441',
                        1 => 'Thị xã Kỳ Anh',
                        2 => '45000',
                    ),
                272 =>
                    array (
                        0 => '1598',
                        1 => 'Thành phố Hải Dương',
                        2 => '3000',
                    ),
                273 =>
                    array (
                        0 => '2056',
                        1 => 'Thành phố Chí Linh',
                        2 => '3000',
                    ),
                274 =>
                    array (
                        0 => '1727',
                        1 => 'Huyện Nam Sách',
                        2 => '3000',
                    ),
                275 =>
                    array (
                        0 => '1818',
                        1 => 'Thị xã Kinh Môn',
                        2 => '3000',
                    ),
                276 =>
                    array (
                        0 => '1953',
                        1 => 'Huyện Kim Thành',
                        2 => '3000',
                    ),
                277 =>
                    array (
                        0 => '3292',
                        1 => 'Huyện Thanh Hà',
                        2 => '3000',
                    ),
                278 =>
                    array (
                        0 => '1817',
                        1 => 'Huyện Cẩm Giàng',
                        2 => '3000',
                    ),
                279 =>
                    array (
                        0 => '1816',
                        1 => 'Huyện Bình Giang',
                        2 => '3000',
                    ),
                280 =>
                    array (
                        0 => '1934',
                        1 => 'Huyện Gia Lộc',
                        2 => '3000',
                    ),
                281 =>
                    array (
                        0 => '3287',
                        1 => 'Huyện Tứ Kỳ',
                        2 => '3000',
                    ),
                282 =>
                    array (
                        0 => '3238',
                        1 => 'Huyện Ninh Giang',
                        2 => '3000',
                    ),
                283 =>
                    array (
                        0 => '3294',
                        1 => 'Huyện Thanh Miện',
                        2 => '3000',
                    ),
                284 =>
                    array (
                        0 => '1589',
                        1 => 'Quận Hồng Bàng',
                        2 => '4000',
                    ),
                285 =>
                    array (
                        0 => '1587',
                        1 => 'Quận Ngô Quyền',
                        2 => '4000',
                    ),
                286 =>
                    array (
                        0 => '1588',
                        1 => 'Quận Lê Chân',
                        2 => '4000',
                    ),
                287 =>
                    array (
                        0 => '1591',
                        1 => 'Quận Hải An',
                        2 => '4000',
                    ),
                288 =>
                    array (
                        0 => '1590',
                        1 => 'Quận Kiến An',
                        2 => '4000',
                    ),
                289 =>
                    array (
                        0 => '1707',
                        1 => 'Quận Đồ Sơn',
                        2 => '4000',
                    ),
                290 =>
                    array (
                        0 => '1706',
                        1 => 'Quận Dương Kinh',
                        2 => '4000',
                    ),
                291 =>
                    array (
                        0 => '1726',
                        1 => 'Huyện Thủy Nguyên',
                        2 => '4000',
                    ),
                292 =>
                    array (
                        0 => '1819',
                        1 => 'Huyện An Dương',
                        2 => '4000',
                    ),
                293 =>
                    array (
                        0 => '1820',
                        1 => 'Huyện An Lão',
                        2 => '4000',
                    ),
                294 =>
                    array (
                        0 => '3203',
                        1 => 'Huyện Kiến Thụy',
                        2 => '4000',
                    ),
                295 =>
                    array (
                        0 => '1821',
                        1 => 'Huyện Tiên Lãng',
                        2 => '4000',
                    ),
                296 =>
                    array (
                        0 => '1822',
                        1 => 'Huyện Vĩnh Bảo',
                        2 => '4000',
                    ),
                297 =>
                    array (
                        0 => '2108',
                        1 => 'Huyện Cát Hải',
                        2 => '4000',
                    ),
                298 =>
                    array (
                        0 => '1653',
                        1 => 'Thành phố Vị Thanh',
                        2 => '95000',
                    ),
                299 =>
                    array (
                        0 => '1823',
                        1 => 'Thành phố Ngã Bảy',
                        2 => '95000',
                    ),
                300 =>
                    array (
                        0 => '1912',
                        1 => 'Huyện Châu Thành A',
                        2 => '95000',
                    ),
                301 =>
                    array (
                        0 => '2096',
                        1 => 'Huyện Châu Thành',
                        2 => '95000',
                    ),
                302 =>
                    array (
                        0 => '1824',
                        1 => 'Huyện Phụng Hiệp',
                        2 => '95000',
                    ),
                303 =>
                    array (
                        0 => '2048',
                        1 => 'Huyện Vị Thuỷ',
                        2 => '95000',
                    ),
                304 =>
                    array (
                        0 => '3445',
                        1 => 'Huyện Long Mỹ',
                        2 => '95000',
                    ),
                305 =>
                    array (
                        0 => '3218',
                        1 => 'Thị xã Long Mỹ',
                        2 => '95000',
                    ),
                306 =>
                    array (
                        0 => '1442',
                        1 => 'Quận 1',
                        2 => '70000',
                    ),
                307 =>
                    array (
                        0 => '1454',
                        1 => 'Quận 12',
                        2 => '70000',
                    ),
                308 =>
                    array (
                        0 => '1463',
                        1 => 'Quận Thủ Đức',
                        2 => '70000',
                    ),
                309 =>
                    array (
                        0 => '1451',
                        1 => 'Quận 9',
                        2 => '70000',
                    ),
                310 =>
                    array (
                        0 => '1461',
                        1 => 'Quận Gò Vấp',
                        2 => '70000',
                    ),
                311 =>
                    array (
                        0 => '1462',
                        1 => 'Quận Bình Thạnh',
                        2 => '70000',
                    ),
                312 =>
                    array (
                        0 => '1455',
                        1 => 'Quận Tân Bình',
                        2 => '70000',
                    ),
                313 =>
                    array (
                        0 => '1456',
                        1 => 'Quận Tân Phú',
                        2 => '70000',
                    ),
                314 =>
                    array (
                        0 => '1457',
                        1 => 'Quận Phú Nhuận',
                        2 => '70000',
                    ),
                315 =>
                    array (
                        0 => '1443',
                        1 => 'Quận 2',
                        2 => '70000',
                    ),
                316 =>
                    array (
                        0 => '1444',
                        1 => 'Quận 3',
                        2 => '70000',
                    ),
                317 =>
                    array (
                        0 => '1452',
                        1 => 'Quận 10',
                        2 => '70000',
                    ),
                318 =>
                    array (
                        0 => '1453',
                        1 => 'Quận 11',
                        2 => '70000',
                    ),
                319 =>
                    array (
                        0 => '1446',
                        1 => 'Quận 4',
                        2 => '70000',
                    ),
                320 =>
                    array (
                        0 => '1447',
                        1 => 'Quận 5',
                        2 => '70000',
                    ),
                321 =>
                    array (
                        0 => '1448',
                        1 => 'Quận 6',
                        2 => '70000',
                    ),
                322 =>
                    array (
                        0 => '1450',
                        1 => 'Quận 8',
                        2 => '70000',
                    ),
                323 =>
                    array (
                        0 => '1458',
                        1 => 'Quận Bình Tân',
                        2 => '70000',
                    ),
                324 =>
                    array (
                        0 => '1449',
                        1 => 'Quận 7',
                        2 => '70000',
                    ),
                325 =>
                    array (
                        0 => '1460',
                        1 => 'Huyện Củ Chi',
                        2 => '70000',
                    ),
                326 =>
                    array (
                        0 => '1459',
                        1 => 'Huyện Hóc Môn',
                        2 => '70000',
                    ),
                327 =>
                    array (
                        0 => '1533',
                        1 => 'Huyện Bình Chánh',
                        2 => '70000',
                    ),
                328 =>
                    array (
                        0 => '1534',
                        1 => 'Huyện Nhà Bè',
                        2 => '70000',
                    ),
                329 =>
                    array (
                        0 => '2090',
                        1 => 'Huyện Cần Giờ',
                        2 => '70000',
                    ),
                330 =>
                    array (
                        0 => '1678',
                        1 => 'Thành phố Hòa Bình',
                        2 => '36000',
                    ),
                331 =>
                    array (
                        0 => '1916',
                        1 => 'Huyện Đà Bắc',
                        2 => '36000',
                    ),
                332 =>
                    array (
                        0 => '1955',
                        1 => 'Huyện Kỳ Sơn',
                        2 => '36000',
                    ),
                333 =>
                    array (
                        0 => '1968',
                        1 => 'Huyện Lương Sơn',
                        2 => '36000',
                    ),
                334 =>
                    array (
                        0 => '2146',
                        1 => 'Huyện Kim Bôi',
                        2 => '36000',
                    ),
                335 =>
                    array (
                        0 => '2087',
                        1 => 'Huyện Cao Phong',
                        2 => '36000',
                    ),
                336 =>
                    array (
                        0 => '2014',
                        1 => 'Huyện Tân Lạc',
                        2 => '36000',
                    ),
                337 =>
                    array (
                        0 => '2163',
                        1 => 'Huyện Mai Châu',
                        2 => '36000',
                    ),
                338 =>
                    array (
                        0 => '2156',
                        1 => 'Huyện Lạc Sơn',
                        2 => '36000',
                    ),
                339 =>
                    array (
                        0 => '2270',
                        1 => 'Huyện Yên Thủy',
                        2 => '36000',
                    ),
                340 =>
                    array (
                        0 => '2157',
                        1 => 'Huyện Lạc Thủy',
                        2 => '36000',
                    ),
                341 =>
                    array (
                        0 => '1680',
                        1 => 'Thành phố Hưng Yên',
                        2 => '17000',
                    ),
                342 =>
                    array (
                        0 => '2046',
                        1 => 'Huyện Văn Lâm',
                        2 => '17000',
                    ),
                343 =>
                    array (
                        0 => '2045',
                        1 => 'Huyện Văn Giang',
                        2 => '17000',
                    ),
                344 =>
                    array (
                        0 => '1828',
                        1 => 'Huyện Yên Mỹ',
                        2 => '17000',
                    ),
                345 =>
                    array (
                        0 => '1827',
                        1 => 'Thị xã Mỹ Hào',
                        2 => '17000',
                    ),
                346 =>
                    array (
                        0 => '1825',
                        1 => 'Huyện Ân Thi',
                        2 => '17000',
                    ),
                347 =>
                    array (
                        0 => '1826',
                        1 => 'Huyện Khoái Châu',
                        2 => '17000',
                    ),
                348 =>
                    array (
                        0 => '1717',
                        1 => 'Huyện Kim Động',
                        2 => '17000',
                    ),
                349 =>
                    array (
                        0 => '2018',
                        1 => 'Huyện Tiên Lữ',
                        2 => '17000',
                    ),
                350 =>
                    array (
                        0 => '2194',
                        1 => 'Huyện Phù Cừ',
                        2 => '17000',
                    ),
                351 =>
                    array (
                        0 => '1548',
                        1 => 'Thành phố Nha Trang',
                        2 => '57000',
                    ),
                352 =>
                    array (
                        0 => '1664',
                        1 => 'Thành phố Cam Ranh',
                        2 => '57000',
                    ),
                353 =>
                    array (
                        0 => '1902',
                        1 => 'Huyện Cam Lâm',
                        2 => '57000',
                    ),
                354 =>
                    array (
                        0 => '1829',
                        1 => 'Huyện Vạn Ninh',
                        2 => '57000',
                    ),
                355 =>
                    array (
                        0 => '2061',
                        1 => 'Thị xã Ninh Hòa',
                        2 => '57000',
                    ),
                356 =>
                    array (
                        0 => '3213',
                        1 => 'Huyện Khánh Vĩnh',
                        2 => '57000',
                    ),
                357 =>
                    array (
                        0 => '1739',
                        1 => 'Huyện Diên Khánh',
                        2 => '57000',
                    ),
                358 =>
                    array (
                        0 => '3212',
                        1 => 'Huyện Khánh Sơn',
                        2 => '57000',
                    ),
                359 =>
                    array (
                        0 => '2117',
                        1 => 'Huyện Trường Sa',
                        2 => '57000',
                    ),
                360 =>
                    array (
                        0 => '1570',
                        1 => 'Thành phố Rạch Giá',
                        2 => '91000',
                    ),
                361 =>
                    array (
                        0 => '2058',
                        1 => 'Thành phố Hà Tiên',
                        2 => '91000',
                    ),
                362 =>
                    array (
                        0 => '1950',
                        1 => 'Huyện Kiên Lương',
                        2 => '91000',
                    ),
                363 =>
                    array (
                        0 => '1830',
                        1 => 'Huyện Hòn Đất',
                        2 => '91000',
                    ),
                364 =>
                    array (
                        0 => '1831',
                        1 => 'Huyện Tân Hiệp',
                        2 => '91000',
                    ),
                365 =>
                    array (
                        0 => '1719',
                        1 => 'Huyện Châu Thành',
                        2 => '91000',
                    ),
                366 =>
                    array (
                        0 => '1832',
                        1 => 'Huyện Giồng Riềng',
                        2 => '91000',
                    ),
                367 =>
                    array (
                        0 => '2132',
                        1 => 'Huyện Gò Quao',
                        2 => '91000',
                    ),
                368 =>
                    array (
                        0 => '1833',
                        1 => 'Huyện An Biên',
                        2 => '91000',
                    ),
                369 =>
                    array (
                        0 => '3125',
                        1 => 'Huyện An Minh',
                        2 => '91000',
                    ),
                370 =>
                    array (
                        0 => '2260',
                        1 => 'Huyện Vĩnh Thuận',
                        2 => '91000',
                    ),
                371 =>
                    array (
                        0 => '2115',
                        1 => 'Thành phố Phú Quốc',
                        2 => '91000',
                    ),
                372 =>
                    array (
                        0 => '2113',
                        1 => 'Huyện Kiên Hải',
                        2 => '91000',
                    ),
                373 =>
                    array (
                        0 => '2251',
                        1 => 'Huyện U Minh Thượng',
                        2 => '91000',
                    ),
                374 =>
                    array (
                        0 => '2134',
                        1 => 'Huyện Giang Thành',
                        2 => '91000',
                    ),
                375 =>
                    array (
                        0 => '1660',
                        1 => 'Thành phố Kon Tum',
                        2 => '60000',
                    ),
                376 =>
                    array (
                        0 => '1921',
                        1 => 'Huyện Đắk Glei',
                        2 => '60000',
                    ),
                377 =>
                    array (
                        0 => '2187',
                        1 => 'Huyện Ngọc Hồi',
                        2 => '60000',
                    ),
                378 =>
                    array (
                        0 => '2121',
                        1 => 'Huyện Đắk Tô',
                        2 => '60000',
                    ),
                379 =>
                    array (
                        0 => '1834',
                        1 => 'Huyện Kon Plông',
                        2 => '60000',
                    ),
                380 =>
                    array (
                        0 => '2148',
                        1 => 'Huyện Kon Rẫy',
                        2 => '60000',
                    ),
                381 =>
                    array (
                        0 => '1835',
                        1 => 'Huyện Đắk Hà',
                        2 => '60000',
                    ),
                382 =>
                    array (
                        0 => '2205',
                        1 => 'Huyện Sa Thầy',
                        2 => '60000',
                    ),
                383 =>
                    array (
                        0 => '2225',
                        1 => 'Huyện Tu Mơ Rông',
                        2 => '60000',
                    ),
                384 =>
                    array (
                        0 => '3446',
                        1 => 'Huyện Ia H\' Drai',
                        2 => '60000',
                    ),
                385 =>
                    array (
                        0 => '1675',
                        1 => 'Thành phố Lai Châu',
                        2 => '30000',
                    ),
                386 =>
                    array (
                        0 => '2010',
                        1 => 'Huyện Tam Đường',
                        2 => '30000',
                    ),
                387 =>
                    array (
                        0 => '1980',
                        1 => 'Huyện Mường Tè',
                        2 => '30000',
                    ),
                388 =>
                    array (
                        0 => '2006',
                        1 => 'Huyện Sìn Hồ',
                        2 => '30000',
                    ),
                389 =>
                    array (
                        0 => '1989',
                        1 => 'Huyện Phong Thổ',
                        2 => '30000',
                    ),
                390 =>
                    array (
                        0 => '2025',
                        1 => 'Huyện Than Uyên',
                        2 => '30000',
                    ),
                391 =>
                    array (
                        0 => '2017',
                        1 => 'Huyện Tân Uyên',
                        2 => '30000',
                    ),
                392 =>
                    array (
                        0 => '1984',
                        1 => 'Huyện Nậm Nhùn',
                        2 => '30000',
                    ),
                393 =>
                    array (
                        0 => '1550',
                        1 => 'Thành phố Đà Lạt',
                        2 => '66000',
                    ),
                394 =>
                    array (
                        0 => '1838',
                        1 => 'Thành phố Bảo Lộc',
                        2 => '66000',
                    ),
                395 =>
                    array (
                        0 => '1919',
                        1 => 'Huyện Đam Rông',
                        2 => '66000',
                    ),
                396 =>
                    array (
                        0 => '1956',
                        1 => 'Huyện Lạc Dương',
                        2 => '66000',
                    ),
                397 =>
                    array (
                        0 => '1958',
                        1 => 'Huyện Lâm Hà',
                        2 => '66000',
                    ),
                398 =>
                    array (
                        0 => '1836',
                        1 => 'Huyện Đơn Dương',
                        2 => '66000',
                    ),
                399 =>
                    array (
                        0 => '1837',
                        1 => 'Huyện Đức Trọng',
                        2 => '66000',
                    ),
                400 =>
                    array (
                        0 => '3160',
                        1 => 'Huyện Di Linh',
                        2 => '66000',
                    ),
                401 =>
                    array (
                        0 => '1839',
                        1 => 'Huyện Bảo Lâm',
                        2 => '66000',
                    ),
                402 =>
                    array (
                        0 => '2104',
                        1 => 'Huyện Đạ Huoai',
                        2 => '66000',
                    ),
                403 =>
                    array (
                        0 => '2106',
                        1 => 'Huyện Đạ Tẻh',
                        2 => '66000',
                    ),
                404 =>
                    array (
                        0 => '3146',
                        1 => 'Huyện Cát Tiên',
                        2 => '66000',
                    ),
                405 =>
                    array (
                        0 => '1642',
                        1 => 'Thành phố Lạng Sơn',
                        2 => '25000',
                    ),
                406 =>
                    array (
                        0 => '2036',
                        1 => 'Huyện Tràng Định',
                        2 => '25000',
                    ),
                407 =>
                    array (
                        0 => '3138',
                        1 => 'Huyện Bình Gia',
                        2 => '25000',
                    ),
                408 =>
                    array (
                        0 => '3310',
                        1 => 'Huyện Văn Lãng',
                        2 => '25000',
                    ),
                409 =>
                    array (
                        0 => '1904',
                        1 => 'Huyện Cao Lộc',
                        2 => '25000',
                    ),
                410 =>
                    array (
                        0 => '3311',
                        1 => 'Huyện Văn Quan',
                        2 => '25000',
                    ),
                411 =>
                    array (
                        0 => '3134',
                        1 => 'Huyện Bắc Sơn',
                        2 => '25000',
                    ),
                412 =>
                    array (
                        0 => '1948',
                        1 => 'Huyện Hữu Lũng',
                        2 => '25000',
                    ),
                413 =>
                    array (
                        0 => '3156',
                        1 => 'Huyện Chi Lăng',
                        2 => '25000',
                    ),
                414 =>
                    array (
                        0 => '1963',
                        1 => 'Huyện Lộc Bình',
                        2 => '25000',
                    ),
                415 =>
                    array (
                        0 => '3182',
                        1 => 'Huyện Đình Lập',
                        2 => '25000',
                    ),
                416 =>
                    array (
                        0 => '1682',
                        1 => 'Thành phố Lào Cai',
                        2 => '31000',
                    ),
                417 =>
                    array (
                        0 => '1744',
                        1 => 'Huyện Bát Xát',
                        2 => '31000',
                    ),
                418 =>
                    array (
                        0 => '2171',
                        1 => 'Huyện Mường Khương',
                        2 => '31000',
                    ),
                419 =>
                    array (
                        0 => '2264',
                        1 => 'Huyện Si Ma Cai',
                        2 => '31000',
                    ),
                420 =>
                    array (
                        0 => '1892',
                        1 => 'Huyện Bắc Hà',
                        2 => '31000',
                    ),
                421 =>
                    array (
                        0 => '2073',
                        1 => 'Huyện Bảo Thắng',
                        2 => '31000',
                    ),
                422 =>
                    array (
                        0 => '1891',
                        1 => 'Huyện Bảo Yên',
                        2 => '31000',
                    ),
                423 =>
                    array (
                        0 => '2005',
                        1 => 'Thị xã Sa Pa',
                        2 => '31000',
                    ),
                424 =>
                    array (
                        0 => '2043',
                        1 => 'Huyện Văn Bàn',
                        2 => '31000',
                    ),
                425 =>
                    array (
                        0 => '1554',
                        1 => 'Thành phố Tân An',
                        2 => '82000',
                    ),
                426 =>
                    array (
                        0 => '3329',
                        1 => 'Thị xã Kiến Tường',
                        2 => '82000',
                    ),
                427 =>
                    array (
                        0 => '3273',
                        1 => 'Huyện Tân Hưng',
                        2 => '82000',
                    ),
                428 =>
                    array (
                        0 => '3315',
                        1 => 'Huyện Vĩnh Hưng',
                        2 => '82000',
                    ),
                429 =>
                    array (
                        0 => '3227',
                        1 => 'Huyện Mộc Hóa',
                        2 => '82000',
                    ),
                430 =>
                    array (
                        0 => '3276',
                        1 => 'Huyện Tân Thạnh',
                        2 => '82000',
                    ),
                431 =>
                    array (
                        0 => '3293',
                        1 => 'Huyện Thạnh Hóa',
                        2 => '82000',
                    ),
                432 =>
                    array (
                        0 => '2129',
                        1 => 'Huyện Đức Huệ',
                        2 => '82000',
                    ),
                433 =>
                    array (
                        0 => '1929',
                        1 => 'Huyện Đức Hòa',
                        2 => '82000',
                    ),
                434 =>
                    array (
                        0 => '1894',
                        1 => 'Huyện Bến Lức',
                        2 => '82000',
                    ),
                435 =>
                    array (
                        0 => '2031',
                        1 => 'Huyện Thủ Thừa',
                        2 => '82000',
                    ),
                436 =>
                    array (
                        0 => '2016',
                        1 => 'Huyện Tân Trụ',
                        2 => '82000',
                    ),
                437 =>
                    array (
                        0 => '1906',
                        1 => 'Huyện Cần Đước',
                        2 => '82000',
                    ),
                438 =>
                    array (
                        0 => '1907',
                        1 => 'Huyện Cần Giuộc',
                        2 => '82000',
                    ),
                439 =>
                    array (
                        0 => '1909',
                        1 => 'Huyện Châu Thành',
                        2 => '82000',
                    ),
                440 =>
                    array (
                        0 => '1613',
                        1 => 'Thành phố Nam Định',
                        2 => '7000',
                    ),
                441 =>
                    array (
                        0 => '1981',
                        1 => 'Huyện Mỹ Lộc',
                        2 => '7000',
                    ),
                442 =>
                    array (
                        0 => '3319',
                        1 => 'Huyện Vụ Bản',
                        2 => '7000',
                    ),
                443 =>
                    array (
                        0 => '1841',
                        1 => 'Huyện Ý Yên',
                        2 => '7000',
                    ),
                444 =>
                    array (
                        0 => '3243',
                        1 => 'Huyện Nghĩa Hưng',
                        2 => '7000',
                    ),
                445 =>
                    array (
                        0 => '1983',
                        1 => 'Huyện Nam Trực',
                        2 => '7000',
                    ),
                446 =>
                    array (
                        0 => '3308',
                        1 => 'Huyện Trực Ninh',
                        2 => '7000',
                    ),
                447 =>
                    array (
                        0 => '3323',
                        1 => 'Huyện Xuân Trường',
                        2 => '7000',
                    ),
                448 =>
                    array (
                        0 => '3193',
                        1 => 'Huyện Giao Thủy',
                        2 => '7000',
                    ),
                449 =>
                    array (
                        0 => '1840',
                        1 => 'Huyện Hải Hậu',
                        2 => '7000',
                    ),
                450 =>
                    array (
                        0 => '1617',
                        1 => 'Thành phố Vinh',
                        2 => '43000',
                    ),
                451 =>
                    array (
                        0 => '1842',
                        1 => 'Thị xã Cửa Lò',
                        2 => '43000',
                    ),
                452 =>
                    array (
                        0 => '1850',
                        1 => 'Thị xã Thái Hòa',
                        2 => '43000',
                    ),
                453 =>
                    array (
                        0 => '3260',
                        1 => 'Huyện Quế Phong',
                        2 => '43000',
                    ),
                454 =>
                    array (
                        0 => '3261',
                        1 => 'Huyện Quỳ Châu',
                        2 => '43000',
                    ),
                455 =>
                    array (
                        0 => '3211',
                        1 => 'Huyện Kỳ Sơn',
                        2 => '43000',
                    ),
                456 =>
                    array (
                        0 => '3288',
                        1 => 'Huyện Tương Dương',
                        2 => '43000',
                    ),
                457 =>
                    array (
                        0 => '1851',
                        1 => 'Huyện Nghĩa Đàn',
                        2 => '43000',
                    ),
                458 =>
                    array (
                        0 => '1852',
                        1 => 'Huyện Quỳ Hợp',
                        2 => '43000',
                    ),
                459 =>
                    array (
                        0 => '1848',
                        1 => 'Huyện Quỳnh Lưu',
                        2 => '43000',
                    ),
                460 =>
                    array (
                        0 => '1853',
                        1 => 'Huyện Con Cuông',
                        2 => '43000',
                    ),
                461 =>
                    array (
                        0 => '1845',
                        1 => 'Huyện Tân Kỳ',
                        2 => '43000',
                    ),
                462 =>
                    array (
                        0 => '1844',
                        1 => 'Huyện Anh Sơn',
                        2 => '43000',
                    ),
                463 =>
                    array (
                        0 => '1847',
                        1 => 'Huyện Diễn Châu',
                        2 => '43000',
                    ),
                464 =>
                    array (
                        0 => '1846',
                        1 => 'Huyện Yên Thành',
                        2 => '43000',
                    ),
                465 =>
                    array (
                        0 => '1843',
                        1 => 'Huyện Đô Lương',
                        2 => '43000',
                    ),
                466 =>
                    array (
                        0 => '3291',
                        1 => 'Huyện Thanh Chương',
                        2 => '43000',
                    ),
                467 =>
                    array (
                        0 => '1854',
                        1 => 'Huyện Nghi Lộc',
                        2 => '43000',
                    ),
                468 =>
                    array (
                        0 => '3233',
                        1 => 'Huyện Nam Đàn',
                        2 => '43000',
                    ),
                469 =>
                    array (
                        0 => '1947',
                        1 => 'Huyện Hưng Nguyên',
                        2 => '43000',
                    ),
                470 =>
                    array (
                        0 => '1849',
                        1 => 'Thị xã Hoàng Mai',
                        2 => '43000',
                    ),
                471 =>
                    array (
                        0 => '1615',
                        1 => 'Thành phố Ninh Bình',
                        2 => '8000',
                    ),
                472 =>
                    array (
                        0 => '1713',
                        1 => 'Thành phố Tam Điệp',
                        2 => '8000',
                    ),
                473 =>
                    array (
                        0 => '3247',
                        1 => 'Huyện Nho Quan',
                        2 => '8000',
                    ),
                474 =>
                    array (
                        0 => '3191',
                        1 => 'Huyện Gia Viễn',
                        2 => '8000',
                    ),
                475 =>
                    array (
                        0 => '1944',
                        1 => 'Huyện Hoa Lư',
                        2 => '8000',
                    ),
                476 =>
                    array (
                        0 => '1714',
                        1 => 'Huyện Yên Khánh',
                        2 => '8000',
                    ),
                477 =>
                    array (
                        0 => '3205',
                        1 => 'Huyện Kim Sơn',
                        2 => '8000',
                    ),
                478 =>
                    array (
                        0 => '3327',
                        1 => 'Huyện Yên Mô',
                        2 => '8000',
                    ),
                479 =>
                    array (
                        0 => '1665',
                        1 => 'Thành phố Phan Rang',
                        2 => '59000',
                    ),
                480 =>
                    array (
                        0 => '3129',
                        1 => 'Huyện Bác Ái',
                        2 => '59000',
                    ),
                481 =>
                    array (
                        0 => '1855',
                        1 => 'Huyện Ninh Sơn',
                        2 => '59000',
                    ),
                482 =>
                    array (
                        0 => '1985',
                        1 => 'Huyện Ninh Hải',
                        2 => '59000',
                    ),
                483 =>
                    array (
                        0 => '1986',
                        1 => 'Huyện Ninh Phước',
                        2 => '59000',
                    ),
                484 =>
                    array (
                        0 => '3301',
                        1 => 'Huyện Thuận Bắc',
                        2 => '59000',
                    ),
                485 =>
                    array (
                        0 => '3302',
                        1 => 'Huyện Thuận Nam',
                        2 => '59000',
                    ),
                486 =>
                    array (
                        0 => '1602',
                        1 => 'Thành phố Việt Trì',
                        2 => '35000',
                    ),
                487 =>
                    array (
                        0 => '2064',
                        1 => 'Thị xã Phú Thọ',
                        2 => '35000',
                    ),
                488 =>
                    array (
                        0 => '1925',
                        1 => 'Huyện Đoan Hùng',
                        2 => '35000',
                    ),
                489 =>
                    array (
                        0 => '1938',
                        1 => 'Huyện Hạ Hòa',
                        2 => '35000',
                    ),
                490 =>
                    array (
                        0 => '3290',
                        1 => 'Huyện Thanh Ba',
                        2 => '35000',
                    ),
                491 =>
                    array (
                        0 => '1994',
                        1 => 'Huyện Phù Ninh',
                        2 => '35000',
                    ),
                492 =>
                    array (
                        0 => '2268',
                        1 => 'Huyện Yên Lập',
                        2 => '35000',
                    ),
                493 =>
                    array (
                        0 => '1905',
                        1 => 'Huyện Cẩm Khê',
                        2 => '35000',
                    ),
                494 =>
                    array (
                        0 => '3272',
                        1 => 'Huyện Tam Nông',
                        2 => '35000',
                    ),
                495 =>
                    array (
                        0 => '1959',
                        1 => 'Huyện Lâm Thao',
                        2 => '35000',
                    ),
                496 =>
                    array (
                        0 => '2029',
                        1 => 'Huyện Thanh Sơn',
                        2 => '35000',
                    ),
                497 =>
                    array (
                        0 => '2237',
                        1 => 'Huyện Thanh Thủy',
                        2 => '35000',
                    ),
                498 =>
                    array (
                        0 => '2015',
                        1 => 'Huyện Tân Sơn',
                        2 => '35000',
                    ),
                499 =>
                    array (
                        0 => '1663',
                        1 => 'Thành phố Tuy Hòa',
                        2 => '56000',
                    ),
                500 =>
                    array (
                        0 => '1856',
                        1 => 'Thị Xã Sông Cầu',
                        2 => '56000',
                    ),
                501 =>
                    array (
                        0 => '3186',
                        1 => 'Huyện Đồng Xuân',
                        2 => '56000',
                    ),
                502 =>
                    array (
                        0 => '3284',
                        1 => 'Huyện Tuy An',
                        2 => '56000',
                    ),
                503 =>
                    array (
                        0 => '2211',
                        1 => 'Huyện Sơn Hòa',
                        2 => '56000',
                    ),
                504 =>
                    array (
                        0 => '2206',
                        1 => 'Huyện Sông Hinh',
                        2 => '56000',
                    ),
                505 =>
                    array (
                        0 => '3278',
                        1 => 'Huyện Tây Hòa',
                        2 => '56000',
                    ),
                506 =>
                    array (
                        0 => '1993',
                        1 => 'Huyện Phú Hòa',
                        2 => '56000',
                    ),
                507 =>
                    array (
                        0 => '3184',
                        1 => 'Thị xã Đông Hòa',
                        2 => '56000',
                    ),
                508 =>
                    array (
                        0 => '1619',
                        1 => 'Thành phố Đồng Hới',
                        2 => '47000',
                    ),
                509 =>
                    array (
                        0 => '3224',
                        1 => 'Huyện Minh Hóa',
                        2 => '47000',
                    ),
                510 =>
                    array (
                        0 => '3286',
                        1 => 'Huyện Tuyên Hóa',
                        2 => '47000',
                    ),
                511 =>
                    array (
                        0 => '3258',
                        1 => 'Huyện Quảng Trạch',
                        2 => '47000',
                    ),
                512 =>
                    array (
                        0 => '1858',
                        1 => 'Huyện Bố Trạch',
                        2 => '47000',
                    ),
                513 =>
                    array (
                        0 => '2002',
                        1 => 'Huyện Quảng Ninh',
                        2 => '47000',
                    ),
                514 =>
                    array (
                        0 => '1857',
                        1 => 'Huyện Lệ Thủy',
                        2 => '47000',
                    ),
                515 =>
                    array (
                        0 => '1859',
                        1 => 'Thị xã Ba Đồn',
                        2 => '47000',
                    ),
                516 =>
                    array (
                        0 => '1631',
                        1 => 'Thành phố Tam Kỳ',
                        2 => '51000',
                    ),
                517 =>
                    array (
                        0 => '1632',
                        1 => 'Thành phố Hội An',
                        2 => '51000',
                    ),
                518 =>
                    array (
                        0 => '2219',
                        1 => 'Huyện Tây Giang',
                        2 => '51000',
                    ),
                519 =>
                    array (
                        0 => '2125',
                        1 => 'Huyện Đông Giang',
                        2 => '51000',
                    ),
                520 =>
                    array (
                        0 => '1917',
                        1 => 'Huyện Đại Lộc',
                        2 => '51000',
                    ),
                521 =>
                    array (
                        0 => '1736',
                        1 => 'Thị xã Điện Bàn',
                        2 => '51000',
                    ),
                522 =>
                    array (
                        0 => '1735',
                        1 => 'Huyện Duy Xuyên',
                        2 => '51000',
                    ),
                523 =>
                    array (
                        0 => '2003',
                        1 => 'Huyện Quế Sơn',
                        2 => '51000',
                    ),
                524 =>
                    array (
                        0 => '2177',
                        1 => 'Huyện Nam Giang',
                        2 => '51000',
                    ),
                525 =>
                    array (
                        0 => '2198',
                        1 => 'Huyện Phước Sơn',
                        2 => '51000',
                    ),
                526 =>
                    array (
                        0 => '2139',
                        1 => 'Huyện Hiệp Đức',
                        2 => '51000',
                    ),
                527 =>
                    array (
                        0 => '2239',
                        1 => 'Huyện Thăng Bình',
                        2 => '51000',
                    ),
                528 =>
                    array (
                        0 => '2224',
                        1 => 'Huyện Tiên Phước',
                        2 => '51000',
                    ),
                529 =>
                    array (
                        0 => '2078',
                        1 => 'Huyện Bắc Trà My',
                        2 => '51000',
                    ),
                530 =>
                    array (
                        0 => '2178',
                        1 => 'Huyện Nam Trà My',
                        2 => '51000',
                    ),
                531 =>
                    array (
                        0 => '1987',
                        1 => 'Huyện Núi Thành',
                        2 => '51000',
                    ),
                532 =>
                    array (
                        0 => '1995',
                        1 => 'Huyện Phú Ninh',
                        2 => '51000',
                    ),
                533 =>
                    array (
                        0 => '2182',
                        1 => 'Huyện Nông Sơn',
                        2 => '51000',
                    ),
                534 =>
                    array (
                        0 => '1630',
                        1 => 'Thành phố Quảng Ngãi',
                        2 => '53000',
                    ),
                535 =>
                    array (
                        0 => '1898',
                        1 => 'Huyện Bình Sơn',
                        2 => '53000',
                    ),
                536 =>
                    array (
                        0 => '3304',
                        1 => 'Huyện Trà Bồng',
                        2 => '53000',
                    ),
                537 =>
                    array (
                        0 => '2222',
                        1 => 'Huyện Tây Trà',
                        2 => '53000',
                    ),
                538 =>
                    array (
                        0 => '1737',
                        1 => 'Huyện Sơn Tịnh',
                        2 => '53000',
                    ),
                539 =>
                    array (
                        0 => '1738',
                        1 => 'Huyện Tư Nghĩa',
                        2 => '53000',
                    ),
                540 =>
                    array (
                        0 => '2210',
                        1 => 'Huyện Sơn Hà',
                        2 => '53000',
                    ),
                541 =>
                    array (
                        0 => '3270',
                        1 => 'Huyện Sơn Tây',
                        2 => '53000',
                    ),
                542 =>
                    array (
                        0 => '2167',
                        1 => 'Huyện Minh Long',
                        2 => '53000',
                    ),
                543 =>
                    array (
                        0 => '1988',
                        1 => 'Huyện Nghĩa Hành',
                        2 => '53000',
                    ),
                544 =>
                    array (
                        0 => '3226',
                        1 => 'Huyện Mộ Đức',
                        2 => '53000',
                    ),
                545 =>
                    array (
                        0 => '1930',
                        1 => 'Thị xã Đức Phổ',
                        2 => '53000',
                    ),
                546 =>
                    array (
                        0 => '3127',
                        1 => 'Huyện Ba Tơ',
                        2 => '53000',
                    ),
                547 =>
                    array (
                        0 => '2114',
                        1 => 'Huyện Lý Sơn',
                        2 => '53000',
                    ),
                548 =>
                    array (
                        0 => '1604',
                        1 => 'Thành phố Hạ Long',
                        2 => '1000',
                    ),
                549 =>
                    array (
                        0 => '1603',
                        1 => 'Thành phố Móng Cái',
                        2 => '1000',
                    ),
                550 =>
                    array (
                        0 => '1683',
                        1 => 'Thành phố Cẩm Phả',
                        2 => '1000',
                    ),
                551 =>
                    array (
                        0 => '1686',
                        1 => 'Thành phố Uông Bí',
                        2 => '1000',
                    ),
                552 =>
                    array (
                        0 => '1896',
                        1 => 'Huyện Bình Liêu',
                        2 => '1000',
                    ),
                553 =>
                    array (
                        0 => '2019',
                        1 => 'Huyện Tiên Yên',
                        2 => '1000',
                    ),
                554 =>
                    array (
                        0 => '3180',
                        1 => 'Huyện Đầm Hà',
                        2 => '1000',
                    ),
                555 =>
                    array (
                        0 => '1940',
                        1 => 'Huyện Hải Hà',
                        2 => '1000',
                    ),
                556 =>
                    array (
                        0 => '3126',
                        1 => 'Huyện Ba Chẽ',
                        2 => '1000',
                    ),
                557 =>
                    array (
                        0 => '1920',
                        1 => 'Huyện Vân Đồn',
                        2 => '1000',
                    ),
                558 =>
                    array (
                        0 => '3199',
                        1 => 'Huyện Hoành Bồ',
                        2 => '1000',
                    ),
                559 =>
                    array (
                        0 => '3185',
                        1 => 'Thị xã Đông Triều',
                        2 => '1000',
                    ),
                560 =>
                    array (
                        0 => '2066',
                        1 => 'Thị xã Quảng Yên',
                        2 => '1000',
                    ),
                561 =>
                    array (
                        0 => '2109',
                        1 => 'Huyện Cô Tô',
                        2 => '1000',
                    ),
                562 =>
                    array (
                        0 => '1620',
                        1 => 'Thành phố Đông Hà',
                        2 => '48000',
                    ),
                563 =>
                    array (
                        0 => '1621',
                        1 => 'Thị xã Quảng Trị',
                        2 => '48000',
                    ),
                564 =>
                    array (
                        0 => '1861',
                        1 => 'Huyện Vĩnh Linh',
                        2 => '48000',
                    ),
                565 =>
                    array (
                        0 => '1860',
                        1 => 'Huyện Hướng Hóa',
                        2 => '48000',
                    ),
                566 =>
                    array (
                        0 => '1936',
                        1 => 'Huyện Gio Linh',
                        2 => '48000',
                    ),
                567 =>
                    array (
                        0 => '2105',
                        1 => 'Huyện Đa Krông',
                        2 => '48000',
                    ),
                568 =>
                    array (
                        0 => '1903',
                        1 => 'Huyện Cam Lộ',
                        2 => '48000',
                    ),
                569 =>
                    array (
                        0 => '2040',
                        1 => 'Huyện Triệu Phong',
                        2 => '48000',
                    ),
                570 =>
                    array (
                        0 => '2137',
                        1 => 'Huyện Hải Lăng',
                        2 => '48000',
                    ),
                571 =>
                    array (
                        0 => '1568',
                        1 => 'Thành phố Sóc Trăng',
                        2 => '96000',
                    ),
                572 =>
                    array (
                        0 => '1910',
                        1 => 'Huyện Châu Thành',
                        2 => '96000',
                    ),
                573 =>
                    array (
                        0 => '1949',
                        1 => 'Huyện Kế Sách',
                        2 => '96000',
                    ),
                574 =>
                    array (
                        0 => '2173',
                        1 => 'Huyện Mỹ Tú',
                        2 => '96000',
                    ),
                575 =>
                    array (
                        0 => '2093',
                        1 => 'Huyện Cù Lao Dung',
                        2 => '96000',
                    ),
                576 =>
                    array (
                        0 => '2161',
                        1 => 'Huyện Long Phú',
                        2 => '96000',
                    ),
                577 =>
                    array (
                        0 => '1743',
                        1 => 'Huyện Mỹ Xuyên',
                        2 => '96000',
                    ),
                578 =>
                    array (
                        0 => '2062',
                        1 => 'Thị xã Ngã Năm',
                        2 => '96000',
                    ),
                579 =>
                    array (
                        0 => '2238',
                        1 => 'Huyện Thạnh Trị',
                        2 => '96000',
                    ),
                580 =>
                    array (
                        0 => '2272',
                        1 => 'Thị xã Vĩnh Châu',
                        2 => '96000',
                    ),
                581 =>
                    array (
                        0 => '2037',
                        1 => 'Huyện Trần Đề',
                        2 => '96000',
                    ),
                582 =>
                    array (
                        0 => '1677',
                        1 => 'Thành phố Sơn La',
                        2 => '34000',
                    ),
                583 =>
                    array (
                        0 => '2204',
                        1 => 'Huyện Quỳnh Nhai',
                        2 => '34000',
                    ),
                584 =>
                    array (
                        0 => '2032',
                        1 => 'Huyện Thuận Châu',
                        2 => '34000',
                    ),
                585 =>
                    array (
                        0 => '3230',
                        1 => 'Huyện Mường La',
                        2 => '34000',
                    ),
                586 =>
                    array (
                        0 => '2079',
                        1 => 'Huyện Bắc Yên',
                        2 => '34000',
                    ),
                587 =>
                    array (
                        0 => '1996',
                        1 => 'Huyện Phù Yên',
                        2 => '34000',
                    ),
                588 =>
                    array (
                        0 => '1976',
                        1 => 'Huyện Mộc Châu',
                        2 => '34000',
                    ),
                589 =>
                    array (
                        0 => '2267',
                        1 => 'Huyện Yên Châu',
                        2 => '34000',
                    ),
                590 =>
                    array (
                        0 => '1971',
                        1 => 'Huyện Mai Sơn',
                        2 => '34000',
                    ),
                591 =>
                    array (
                        0 => '2007',
                        1 => 'Huyện Sông Mã',
                        2 => '34000',
                    ),
                592 =>
                    array (
                        0 => '3266',
                        1 => 'huyện Sốp Cộp',
                        2 => '34000',
                    ),
                593 =>
                    array (
                        0 => '2255',
                        1 => 'Huyện Vân Hồ',
                        2 => '34000',
                    ),
                594 =>
                    array (
                        0 => '1626',
                        1 => 'Thành phố Tây Ninh',
                        2 => '80000',
                    ),
                595 =>
                    array (
                        0 => '1862',
                        1 => 'Huyện Tân Biên',
                        2 => '80000',
                    ),
                596 =>
                    array (
                        0 => '1863',
                        1 => 'Huyện Tân Châu',
                        2 => '80000',
                    ),
                597 =>
                    array (
                        0 => '1864',
                        1 => 'Huyện Dương Minh Châu',
                        2 => '80000',
                    ),
                598 =>
                    array (
                        0 => '1720',
                        1 => 'Huyện Châu Thành',
                        2 => '80000',
                    ),
                599 =>
                    array (
                        0 => '1721',
                        1 => 'Thị xã Hòa Thành',
                        2 => '80000',
                    ),
                600 =>
                    array (
                        0 => '1866',
                        1 => 'Huyện Gò Dầu',
                        2 => '80000',
                    ),
                601 =>
                    array (
                        0 => '1865',
                        1 => 'Huyện Bến Cầu',
                        2 => '80000',
                    ),
                602 =>
                    array (
                        0 => '2035',
                        1 => 'Thị xã Trảng Bàng',
                        2 => '80000',
                    ),
                603 =>
                    array (
                        0 => '1599',
                        1 => 'Thành phố Thái Bình',
                        2 => '6000',
                    ),
                604 =>
                    array (
                        0 => '1868',
                        1 => 'Huyện Quỳnh Phụ',
                        2 => '6000',
                    ),
                605 =>
                    array (
                        0 => '1867',
                        1 => 'Huyện Hưng Hà',
                        2 => '6000',
                    ),
                606 =>
                    array (
                        0 => '1715',
                        1 => 'Huyện Đông Hưng',
                        2 => '6000',
                    ),
                607 =>
                    array (
                        0 => '1869',
                        1 => 'Huyện Thái Thụy',
                        2 => '6000',
                    ),
                608 =>
                    array (
                        0 => '3281',
                        1 => 'Huyện Tiền Hải',
                        2 => '6000',
                    ),
                609 =>
                    array (
                        0 => '1951',
                        1 => 'Huyện Kiến Xương',
                        2 => '6000',
                    ),
                610 =>
                    array (
                        0 => '1716',
                        1 => 'Huyện Vũ Thư',
                        2 => '6000',
                    ),
                611 =>
                    array (
                        0 => '1639',
                        1 => 'Thành phố Thái Nguyên',
                        2 => '24000',
                    ),
                612 =>
                    array (
                        0 => '1684',
                        1 => 'Thành Phố Sông Công',
                        2 => '24000',
                    ),
                613 =>
                    array (
                        0 => '1924',
                        1 => 'Huyện Định Hóa',
                        2 => '24000',
                    ),
                614 =>
                    array (
                        0 => '2195',
                        1 => 'Huyện Phú Lương',
                        2 => '24000',
                    ),
                615 =>
                    array (
                        0 => '1731',
                        1 => 'Huyện Đồng Hỷ',
                        2 => '24000',
                    ),
                616 =>
                    array (
                        0 => '2051',
                        1 => 'Huyện Võ Nhai',
                        2 => '24000',
                    ),
                617 =>
                    array (
                        0 => '1918',
                        1 => 'Huyện Đại Từ',
                        2 => '24000',
                    ),
                618 =>
                    array (
                        0 => '1990',
                        1 => 'Thị xã Phổ Yên',
                        2 => '24000',
                    ),
                619 =>
                    array (
                        0 => '1991',
                        1 => 'Huyện Phú Bình',
                        2 => '24000',
                    ),
                620 =>
                    array (
                        0 => '1616',
                        1 => 'Thành phố Thanh Hóa',
                        2 => '40000',
                    ),
                621 =>
                    array (
                        0 => '1876',
                        1 => 'Thị xã Bỉm Sơn',
                        2 => '40000',
                    ),
                622 =>
                    array (
                        0 => '1712',
                        1 => 'Thành phố Sầm Sơn',
                        2 => '40000',
                    ),
                623 =>
                    array (
                        0 => '1878',
                        1 => 'Huyện Mường Lát',
                        2 => '40000',
                    ),
                624 =>
                    array (
                        0 => '1879',
                        1 => 'Huyện Quan Hóa',
                        2 => '40000',
                    ),
                625 =>
                    array (
                        0 => '2070',
                        1 => 'Huyện Bá Thước',
                        2 => '40000',
                    ),
                626 =>
                    array (
                        0 => '2000',
                        1 => 'Huyện Quan Sơn',
                        2 => '40000',
                    ),
                627 =>
                    array (
                        0 => '3216',
                        1 => 'Huyện Lang Chánh',
                        2 => '40000',
                    ),
                628 =>
                    array (
                        0 => '1874',
                        1 => 'Huyện Ngọc Lặc',
                        2 => '40000',
                    ),
                629 =>
                    array (
                        0 => '3147',
                        1 => 'Huyện Cẩm Thủy',
                        2 => '40000',
                    ),
                630 =>
                    array (
                        0 => '1880',
                        1 => 'Huyện Thạch Thành',
                        2 => '40000',
                    ),
                631 =>
                    array (
                        0 => '1877',
                        1 => 'Huyện Hà Trung',
                        2 => '40000',
                    ),
                632 =>
                    array (
                        0 => '1881',
                        1 => 'Huyện Vĩnh Lộc',
                        2 => '40000',
                    ),
                633 =>
                    array (
                        0 => '1875',
                        1 => 'Huyện Yên Định',
                        2 => '40000',
                    ),
                634 =>
                    array (
                        0 => '1873',
                        1 => 'Huyện Thọ Xuân',
                        2 => '40000',
                    ),
                635 =>
                    array (
                        0 => '1872',
                        1 => 'Huyện Thường Xuân',
                        2 => '40000',
                    ),
                636 =>
                    array (
                        0 => '2249',
                        1 => 'Huyện Triệu Sơn',
                        2 => '40000',
                    ),
                637 =>
                    array (
                        0 => '3298',
                        1 => 'Huyện Thiệu Hóa',
                        2 => '40000',
                    ),
                638 =>
                    array (
                        0 => '1748',
                        1 => 'Huyện Hoằng Hóa',
                        2 => '40000',
                    ),
                639 =>
                    array (
                        0 => '1942',
                        1 => 'Huyện Hậu Lộc',
                        2 => '40000',
                    ),
                640 =>
                    array (
                        0 => '3241',
                        1 => 'Huyện Nga Sơn',
                        2 => '40000',
                    ),
                641 =>
                    array (
                        0 => '1871',
                        1 => 'Huyện Như Xuân',
                        2 => '40000',
                    ),
                642 =>
                    array (
                        0 => '2190',
                        1 => 'Huyện Như Thanh',
                        2 => '40000',
                    ),
                643 =>
                    array (
                        0 => '2181',
                        1 => 'Huyện Nông Cống',
                        2 => '40000',
                    ),
                644 =>
                    array (
                        0 => '1927',
                        1 => 'Huyện Đông Sơn',
                        2 => '40000',
                    ),
                645 =>
                    array (
                        0 => '1747',
                        1 => 'Huyện Quảng Xương',
                        2 => '40000',
                    ),
                646 =>
                    array (
                        0 => '1870',
                        1 => 'Thị Xã Nghi Sơn',
                        2 => '40000',
                    ),
                647 =>
                    array (
                        0 => '1585',
                        1 => 'Thành phố Huế',
                        2 => '49000',
                    ),
                648 =>
                    array (
                        0 => '2193',
                        1 => 'Huyện Phong Điền',
                        2 => '49000',
                    ),
                649 =>
                    array (
                        0 => '3257',
                        1 => 'Huyện Quảng Điền',
                        2 => '49000',
                    ),
                650 =>
                    array (
                        0 => '1749',
                        1 => 'Huyện Phú Vang',
                        2 => '49000',
                    ),
                651 =>
                    array (
                        0 => '1698',
                        1 => 'Thị xã Hương Thủy',
                        2 => '49000',
                    ),
                652 =>
                    array (
                        0 => '1697',
                        1 => 'Thị xã Hương Trà',
                        2 => '49000',
                    ),
                653 =>
                    array (
                        0 => '1885',
                        1 => 'Huyện A Lưới',
                        2 => '49000',
                    ),
                654 =>
                    array (
                        0 => '1882',
                        1 => 'Huyện Phú Lộc',
                        2 => '49000',
                    ),
                655 =>
                    array (
                        0 => '3234',
                        1 => 'Huyện Nam Đông',
                        2 => '49000',
                    ),
                656 =>
                    array (
                        0 => '1556',
                        1 => 'Thành phố Mỹ Tho',
                        2 => '84000',
                    ),
                657 =>
                    array (
                        0 => '2057',
                        1 => 'Thị xã Gò Công',
                        2 => '84000',
                    ),
                658 =>
                    array (
                        0 => '2055',
                        1 => 'Thị xã Cai Lậy',
                        2 => '84000',
                    ),
                659 =>
                    array (
                        0 => '3275',
                        1 => 'Huyện Tân Phước',
                        2 => '84000',
                    ),
                660 =>
                    array (
                        0 => '1900',
                        1 => 'Huyện Cái Bè',
                        2 => '84000',
                    ),
                661 =>
                    array (
                        0 => '2084',
                        1 => 'Huyện Cai Lậy',
                        2 => '84000',
                    ),
                662 =>
                    array (
                        0 => '1740',
                        1 => 'Huyện Châu Thành',
                        2 => '84000',
                    ),
                663 =>
                    array (
                        0 => '1741',
                        1 => 'Huyện Chợ Gạo',
                        2 => '84000',
                    ),
                664 =>
                    array (
                        0 => '1933',
                        1 => 'Huyện Gò Công Tây',
                        2 => '84000',
                    ),
                665 =>
                    array (
                        0 => '1932',
                        1 => 'Huyện Gò Công Đông',
                        2 => '84000',
                    ),
                666 =>
                    array (
                        0 => '2216',
                        1 => 'Huyện Tân Phú Đông',
                        2 => '84000',
                    ),
                667 =>
                    array (
                        0 => '1560',
                        1 => 'Thành phố Trà Vinh',
                        2 => '87000',
                    ),
                668 =>
                    array (
                        0 => '2086',
                        1 => 'Huyện Càng Long',
                        2 => '87000',
                    ),
                669 =>
                    array (
                        0 => '2091',
                        1 => 'Huyện Cầu Kè',
                        2 => '87000',
                    ),
                670 =>
                    array (
                        0 => '2020',
                        1 => 'Huyện Tiểu Cần',
                        2 => '87000',
                    ),
                671 =>
                    array (
                        0 => '1911',
                        1 => 'Huyện Châu Thành',
                        2 => '87000',
                    ),
                672 =>
                    array (
                        0 => '1908',
                        1 => 'Huyện Cầu Ngang',
                        2 => '87000',
                    ),
                673 =>
                    array (
                        0 => '2033',
                        1 => 'Huyện Trà Cú',
                        2 => '87000',
                    ),
                674 =>
                    array (
                        0 => '2103',
                        1 => 'Huyện Duyên Hải',
                        2 => '87000',
                    ),
                675 =>
                    array (
                        0 => '3443',
                        1 => 'Thị xã Duyên Hải',
                        2 => '87000',
                    ),
                676 =>
                    array (
                        0 => '1601',
                        1 => 'Thành phố Tuyên Quang',
                        2 => '22000',
                    ),
                677 =>
                    array (
                        0 => '1957',
                        1 => 'Huyện Lâm Bình',
                        2 => '22000',
                    ),
                678 =>
                    array (
                        0 => '1982',
                        1 => 'Huyện Na Hang',
                        2 => '22000',
                    ),
                679 =>
                    array (
                        0 => '3157',
                        1 => 'Huyện Chiêm Hóa',
                        2 => '22000',
                    ),
                680 =>
                    array (
                        0 => '1941',
                        1 => 'Huyện Hàm Yên',
                        2 => '22000',
                    ),
                681 =>
                    array (
                        0 => '1745',
                        1 => 'Huyện Yên Sơn',
                        2 => '22000',
                    ),
                682 =>
                    array (
                        0 => '3267',
                        1 => 'Huyện Sơn Dương',
                        2 => '22000',
                    ),
                683 =>
                    array (
                        0 => '1562',
                        1 => 'Thành phố Vĩnh Long',
                        2 => '85000',
                    ),
                684 =>
                    array (
                        0 => '1962',
                        1 => 'Huyện Long Hồ',
                        2 => '85000',
                    ),
                685 =>
                    array (
                        0 => '2164',
                        1 => 'Huyện Mang Thít',
                        2 => '85000',
                    ),
                686 =>
                    array (
                        0 => '2263',
                        1 => 'Huyện Vũng Liêm',
                        2 => '85000',
                    ),
                687 =>
                    array (
                        0 => '2008',
                        1 => 'Huyện Tam Bình',
                        2 => '85000',
                    ),
                688 =>
                    array (
                        0 => '2054',
                        1 => 'Thị xã Bình Minh',
                        2 => '85000',
                    ),
                689 =>
                    array (
                        0 => '2034',
                        1 => 'Huyện Trà Ôn',
                        2 => '85000',
                    ),
                690 =>
                    array (
                        0 => '2081',
                        1 => 'Huyện Bình Tân',
                        2 => '85000',
                    ),
                691 =>
                    array (
                        0 => '1578',
                        1 => 'Thành phố Vĩnh Yên',
                        2 => '15000',
                    ),
                692 =>
                    array (
                        0 => '2065',
                        1 => 'Thành phố Phúc Yên',
                        2 => '15000',
                    ),
                693 =>
                    array (
                        0 => '1960',
                        1 => 'Huyện Lập Thạch',
                        2 => '15000',
                    ),
                694 =>
                    array (
                        0 => '2009',
                        1 => 'Huyện Tam Dương',
                        2 => '15000',
                    ),
                695 =>
                    array (
                        0 => '3271',
                        1 => 'Huyện Tam Đảo',
                        2 => '15000',
                    ),
                696 =>
                    array (
                        0 => '1732',
                        1 => 'Huyện Bình Xuyên',
                        2 => '15000',
                    ),
                697 =>
                    array (
                        0 => '1734',
                        1 => 'Huyện Yên Lạc',
                        2 => '15000',
                    ),
                698 =>
                    array (
                        0 => '1733',
                        1 => 'Huyện Vĩnh Tường',
                        2 => '15000',
                    ),
                699 =>
                    array (
                        0 => '3265',
                        1 => 'Huyện Sông Lô',
                        2 => '15000',
                    ),
                700 =>
                    array (
                        0 => '1674',
                        1 => 'Thành phố Yên Bái',
                        2 => '33000',
                    ),
                701 =>
                    array (
                        0 => '2063',
                        1 => 'Thị xã Nghĩa Lộ',
                        2 => '33000',
                    ),
                702 =>
                    array (
                        0 => '1967',
                        1 => 'Huyện Lục Yên',
                        2 => '33000',
                    ),
                703 =>
                    array (
                        0 => '2047',
                        1 => 'Huyện Văn Yên',
                        2 => '33000',
                    ),
                704 =>
                    array (
                        0 => '1977',
                        1 => 'Huyện Mù Cang Chải',
                        2 => '33000',
                    ),
                705 =>
                    array (
                        0 => '2039',
                        1 => 'Huyện Trấn Yên',
                        2 => '33000',
                    ),
                706 =>
                    array (
                        0 => '2248',
                        1 => 'Huyện Trạm Tấu',
                        2 => '33000',
                    ),
                707 =>
                    array (
                        0 => '2044',
                        1 => 'Huyện Văn Chấn',
                        2 => '33000',
                    ),
                708 =>
                    array (
                        0 => '2266',
                        1 => 'Huyện Yên Bình',
                        2 => '33000',
                    ),
                709 =>
                    array (
                        0 => '1580',
                        1 => 'Quận Đặc Biệt',
                        2 => '70000',
                    ),
                710 =>
                    array (
                        0 => '3448',
                        1 => 'Quận Đặc Biệt DC',
                        2 => '70000',
                    ),
                711 =>
                    array (
                        0 => '3442',
                        1 => 'Quận Đặc Biệt',
                        2 => '10000',
                    ),
                712 =>
                    array (
                        0 => '3447',
                        1 => 'Quận Đặc Biệt',
                        2 => '50000',
                    ),
                713 =>
                    array (
                        0 => '2111',
                        1 => 'Huyện Côn Đảo',
                        2 => '78000',
                    ),
                714 =>
                    array (
                        0 => '2112',
                        1 => 'Huyện Hoàng Sa',
                        2 => '50000',
                    ),
                715 =>
                    array (
                        0 => '3694',
                        1 => 'Huyện Quảng Hòa',
                        2 => '21000',
                    ),
                716 =>
                    array (
                        0 => '3695',
                        1 => 'Thành Phố Thủ Đức',
                        2 => '70000',
                    ),
                717 =>
                    array (
                        0 => '2107',
                        1 => 'Huyện Bạch Long Vĩ',
                        2 => '4000',
                    ),
                718 =>
                    array (
                        0 => '2110',
                        1 => 'Huyện Cồn Cỏ',
                        2 => '48000',
                    ),
            );
    }
}

