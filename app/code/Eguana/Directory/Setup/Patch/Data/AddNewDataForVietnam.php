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
class AddNewDataForVietnam implements DataPatchInterface
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
        $this->deleteExistingRegionData(); //todo: check if delete on cascade is working
        /** @var RegionCityZipcodeDataInstaller $regionCityDataInstaller */
        $regionCityDataInstaller = $this->regionCityZipcodeDataInstallerFactory->create();
        $regionCityDataInstaller->addCountryRegionsCitiesWithZipcode($this->moduleDataSetup->getConnection(), $this->getDataForVietnam());
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
        return [\Eguana\Directory\Setup\Patch\Data\AddDataForTaiwan::class];
    }

    /**
     * @return array[]
     */
    public function getDataForVietnam()
    {
        return [
            ["VN","VN-01","An Giang", [
                ["90000","Thành Phố Long Xuyên","VN-001"],
                ["90000","Huyện An Phú","VN-002"],
                ["90000","Huyện Châu Phú","VN-003"],
                ["90000","Huyện Châu Thành","VN-004"],
                ["90000","Huyện Chợ Mới","VN-005"],
                ["90000","Huyện Phú Tân","VN-006"],
                ["90000","Huyện Thoại Sơn","VN-007"],
                ["90000","Huyện Tịnh Biên","VN-008"],
                ["90000","Huyện Tri Tôn","VN-009"],
                ["90000","Thành Phố Châu Đốc","VN-010"],
                ["90000","Thị Xã Tân Châu","VN-011"]
            ]],
            ["VN","VN-02","Bà Rịa - Vũng Tàu", [
                ["78000","Thành Phố Bà Rịa","VN-012"],
                ["78000","Thị xã Phú Mỹ","VN-013"],
                ["78000","Thành Phố Vũng Tàu","VN-014"],
                ["78000","Huyện Đất Đỏ","VN-015"],
                ["78000","Huyện Long Điền","VN-016"],
                ["78000","Huyện Tân Thành","VN-017"],
                ["78000","Huyện Châu Đức","VN-018"],
                ["78000","Huyện Côn Đảo","VN-019"],
                ["78000","Huyện Xuyên Mộc","VN-020"]
            ]],
            ["VN","VN-03","Bắc Giang", [
                ["26000","Thành Phố Bắc Giang","VN-021"],
                ["26000","Huyện Hiệp Hòa","VN-022"],
                ["26000","Huyện Lạng Giang","VN-023"],
                ["26000","Huyện Lục Nam","VN-024"],
                ["26000","Huyện Lục Ngạn","VN-025"],
                ["26000","Huyện Sơn Động","VN-026"],
                ["26000","Huyện Tân Yên","VN-027"],
                ["26000","Huyện Việt Yên","VN-028"],
                ["26000","Huyện Yên Dũng","VN-029"],
                ["26000","Huyện Yên Thế","VN-030"]
            ]],
            ["VN","VN-04","Bắc Kạn", [
                ["23000","Thành Phố Bắc Kạn","VN-031"],
                ["23000","Huyện Ba Bể","VN-032"],
                ["23000","Huyện Bạch Thông","VN-033"],
                ["23000","Huyện Chợ Đồn","VN-034"],
                ["23000","Huyện Chợ Mới","VN-035"],
                ["23000","Huyện Na Rì","VN-036"],
                ["23000","Huyện Ngân Sơn","VN-037"],
                ["23000","Huyện Pác Nặm","VN-038"]
            ]],
            ["VN","VN-05","Bạc Liêu", [
                ["97000","Thành Phố Bạc Liêu","VN-039"],
                ["97000","Huyện Hòa Bình","VN-040"],
                ["97000","Huyện Vĩnh Lợi","VN-041"],
                ["97000","Thị Xã Giá Rai","VN-042"],
                ["97000","Huyện Đông Hải","VN-043"],
                ["97000","Huyện Hồng Dân","VN-044"],
                ["97000","Huyện Phước Long","VN-045"]
            ]],
            ["VN","VN-06","Bắc Ninh", [
                ["16000","Thành Phố Bắc Ninh","VN-046"],
                ["16000","Huyện Gia Bình","VN-047"],
                ["16000","Huyện Lương Tài","VN-048"],
                ["16000","Huyện Quế Võ","VN-049"],
                ["16000","Huyện Thuận Thành","VN-050"],
                ["16000","Huyện Tiên Du","VN-051"],
                ["16000","Huyện Yên Phong","VN-052"],
                ["16000","Thị Xã Từ Sơn","VN-053"]
            ]],
            ["VN","VN-07","Bến Tre", [
                ["86000","Thành Phố Bến Tre","VN-054"],
                ["86000","Huyện Ba Tri","VN-055"],
                ["86000","Huyện Bình Đại","VN-056"],
                ["86000","Huyện Châu Thành","VN-057"],
                ["86000","Huyện Chợ Lách","VN-058"],
                ["86000","Huyện Giồng Trôm","VN-059"],
                ["86000","Huyện Mỏ Cày Bắc","VN-060"],
                ["86000","Huyện Mỏ Cày Nam","VN-061"],
                ["86000","Huyện Thạnh Phú","VN-062"]
            ]],
            ["VN","VN-08","Bình Định", [
                ["55000","Thành Phố Quy Nhơn","VN-063"],
                ["55000","Thị Xã An Nhơn","VN-064"],
                ["55000","Huyện An Lão","VN-065"],
                ["55000","Huyện Hoài Ân","VN-066"],
                ["55000","Huyện Hoài Nhơn","VN-067"],
                ["55000","Huyện Phù Cát","VN-068"],
                ["55000","Huyện Phù Mỹ","VN-069"],
                ["55000","Huyện Tây Sơn","VN-070"],
                ["55000","Huyện Tuy Phước","VN-071"],
                ["55000","Huyện Vân Canh","VN-072"],
                ["55000","Huyện Vĩnh Thạnh","VN-073"]
            ]],
            ["VN","VN-09","Bình Dương", [
                ["75000","Thành Phố Thủ Dầu Một","VN-074"],
                ["75000","Thành Phố Dĩ An","VN-075"],
                ["75000","Thành Phố Thuận An","VN-076"],
                ["75000","Huyện Bắc Tân Uyên","VN-077"],
                ["75000","Huyện Bàu Bàng","VN-078"],
                ["75000","Huyện Dầu Tiếng","VN-079"],
                ["75000","Huyện Phú Giáo","VN-080"],
                ["75000","Thị Xã Bến Cát","VN-081"],
                ["75000","Thị Xã Tân Uyên","VN-082"]
            ]],
            ["VN","VN-10","Bình Phước", [
                ["67000","Huyện Bù Đốp","VN-083"],
                ["67000","Huyện Bù Gia Mập","VN-084"],
                ["67000","Huyện Chơn Thành","VN-085"],
                ["67000","Huyện Đồng Phú","VN-086"],
                ["67000","Huyện Hớn Quản","VN-087"],
                ["67000","Huyện Lộc Ninh","VN-088"],
                ["67000","Huyện Phú Riềng","VN-089"],
                ["67000","Thị Xã Bình Long","VN-090"],
                ["67000","Thành phố Đồng Xoài ","VN-091"],
                ["67000","Thị Xã Phước Long","VN-092"],
                ["67000","Huyện Bù Đăng","VN-093"]
            ]],
            ["VN","VN-11","Bình Thuận", [
                ["77000","Thành Phố Phan Thiết","VN-094"],
                ["77000","Huyện Bắc Bình","VN-095"],
                ["77000","Huyện Đức Linh","VN-096"],
                ["77000","Huyện Hàm Tân","VN-097"],
                ["77000","Huyện Hàm Thuận Bắc","VN-098"],
                ["77000","Huyện Hàm Thuận Nam","VN-099"],
                ["77000","Huyện Tánh Linh","VN-100"],
                ["77000","Thị Xã La Gi","VN-101"],
                ["77000","Huyện Tuy Phong","VN-102"],
                ["77000","Huyện Phú Quí","VN-103"]
            ]],
            ["VN","VN-12","Cà Mau", [
                ["98000","Thành Phố Cà Mau","VN-104"],
                ["98000","Huyện Cái Nước","VN-105"],
                ["98000","Huyện Đầm Dơi","VN-106"],
                ["98000","Huyện Phú Tân","VN-107"],
                ["98000","Huyện Thới Bình","VN-108"],
                ["98000","Huyện Năm Căn","VN-109"],
                ["98000","Huyện Ngọc Hiển","VN-110"],
                ["98000","Huyện Trần Văn Thời","VN-111"],
                ["98000","Huyện U Minh","VN-112"]
            ]],
            ["VN","VN-13","Cần Thơ", [
                ["94000","Quận Bình Thủy","VN-113"],
                ["94000","Quận Cái Răng","VN-114"],
                ["94000","Quận Ninh Kiều","VN-115"],
                ["94000","Quận Ô Môn","VN-116"],
                ["94000","Huyện Cờ Đỏ","VN-117"],
                ["94000","Huyện Phong Điền","VN-118"],
                ["94000","Huyện Thới Lai","VN-119"],
                ["94000","Huyện Vĩnh Thạnh","VN-120"],
                ["94000","Quận Thốt Nốt","VN-121"]
            ]],
            ["VN","VN-14","Cao Bằng", [
                ["21000","Thành Phố Cao Bằng","VN-122"],
                ["21000","Huyện Bảo Lạc","VN-123"],
                ["21000","Huyện Bảo Lâm","VN-124"],
                ["21000","Huyện Hạ Lang","VN-125"],
                ["21000","Huyện Hà Quảng","VN-126"],
                ["21000","Huyện Hòa An","VN-127"],
                ["21000","Huyện Nguyên Bình","VN-128"],
                ["21000","Huyện Quảng Hòa","VN-129"],
                ["21000","Huyện Thạch An","VN-130"],
                ["21000","Huyện Thông Nông","VN-131"],
                ["21000","Huyện Trà Lĩnh","VN-132"],
                ["21000","Huyện Trùng Khánh","VN-133"]
            ]],
            ["VN","VN-15","Đà Nẵng", [
                ["50000","Quận Cẩm Lệ","VN-134"],
                ["50000","Quận Hải Châu","VN-135"],
                ["50000","Quận Liên Chiểu","VN-136"],
                ["50000","Quận Ngũ Hành Sơn","VN-137"],
                ["50000","Quận Sơn Trà","VN-138"],
                ["50000","Quận Thanh Khê","VN-139"],
                ["50000","Huyện Hòa Vang","VN-140"],
                ["50000","HuyệN HoàNg Sa","VN-141"]
            ]],
            ["VN","VN-16","Đắk Lắk", [
                ["63000","Thành Phố Buôn Ma Thuột","VN-142"],
                ["63000","Huyện Buôn Đôn","VN-143"],
                ["63000","Huyện Krông A Na","VN-144"],
                ["63000","Huyện Krông Bông","VN-145"],
                ["63000","Huyện Krông Búk","VN-146"],
                ["63000","Huyện Krông Năng","VN-147"],
                ["63000","Huyện Krông Pắc","VN-148"],
                ["63000","Huyện Lắk","VN-149"],
                ["63000","Huyện Cư Kuin","VN-150"],
                ["63000","Huyện Cư M'Gar","VN-151"],
                ["63000","Huyện Ea H'Leo","VN-152"],
                ["63000","Huyện Ea Kar","VN-153"],
                ["63000","Huyện Ea Súp","VN-154"],
                ["63000","Huyện M'Đrắk","VN-155"],
                ["63000","Thị Xã Buôn Hồ","VN-156"]
            ]],
            ["VN","VN-17","Đắk Nông", [
                ["65000","Thành Phố Gia Nghĩa","VN-157"],
                ["65000","Huyện Cư Jút","VN-158"],
                ["65000","Huyện Đắk Mil","VN-159"],
                ["65000","Huyện Đắk R'Lấp","VN-160"],
                ["65000","Huyện Đắk Song","VN-161"],
                ["65000","Huyện Đăk Glong","VN-162"],
                ["65000","Huyện Krông Nô","VN-163"],
                ["65000","Huyện Tuy Đức","VN-164"]
            ]],
            ["VN","VN-18","Điện Biên", [
                ["32000","Thành Phố Điện Biên Phủ","VN-165"],
                ["32000","Huyện Điện Biên","VN-166"],
                ["32000","Huyện Điện Biên Đông","VN-167"],
                ["32000","Huyện Mường Ảng","VN-168"],
                ["32000","Huyện Mường Chà","VN-169"],
                ["32000","Huyện Mường Nhé","VN-170"],
                ["32000","Huyện Tủa Chùa","VN-171"],
                ["32000","Huyện Tuần Giáo","VN-172"],
                ["32000","Thị Xã Mường Lay","VN-173"],
                ["32000","Huyện Nậm Pồ","VN-174"]
            ]],
            ["VN","VN-19","Đồng Nai", [
                ["76000","Thành Phố Biên Hòa","VN-175"],
                ["76000","Huyện Cẩm Mỹ","VN-176"],
                ["76000","Huyện Định Quán","VN-177"],
                ["76000","Huyện Long Thành","VN-178"],
                ["76000","Huyện Nhơn Trạch","VN-179"],
                ["76000","Huyện Tân Phú","VN-180"],
                ["76000","Huyện Thống Nhất","VN-181"],
                ["76000","Huyện Trảng Bom","VN-182"],
                ["76000","Huyện Vĩnh Cửu","VN-183"],
                ["76000","Huyện Xuân Lộc","VN-184"],
                ["76000","Thành Phố Long Khánh ","VN-185"]
            ]],
            ["VN","VN-20","Đồng Tháp", [
                ["81000","Thành Phố Sa Đéc","VN-186"],
                ["81000","Thành Phố Cao Lãnh","VN-187"],
                ["81000","Huyện Cao Lãnh","VN-188"],
                ["81000","Huyện Lai Vung","VN-189"],
                ["81000","Huyện Tân Hồng","VN-190"],
                ["81000","Thị Xã Hồng Ngự","VN-191"],
                ["81000","Huyện Châu Thành","VN-192"],
                ["81000","Huyện Hồng Ngự","VN-193"],
                ["81000","Huyện Lấp Vò","VN-194"],
                ["81000","Huyện Tam Nông","VN-195"],
                ["81000","Huyện Thanh Bình","VN-196"],
                ["81000","Huyện Tháp Mười","VN-197"]
            ]],
            ["VN","VN-21","Gia Lai", [
                ["61000","Thành Phố Pleiku","VN-198"],
                ["61000","Huyện Chư Păh","VN-199"],
                ["61000","Huyện Chư Prông","VN-200"],
                ["61000","Huyện Chư Pưh","VN-201"],
                ["61000","Huyện Chư Sê","VN-202"],
                ["61000","Huyện Đăk Đoa","VN-203"],
                ["61000","Huyện Đăk Pơ","VN-204"],
                ["61000","Huyện Đức Cơ","VN-205"],
                ["61000","Huyện Ia Grai","VN-206"],
                ["61000","Huyện Ia Pa","VN-207"],
                ["61000","Huyện Kbang","VN-208"],
                ["61000","Huyện Kông Chro","VN-209"],
                ["61000","Huyện Krông Pa","VN-210"],
                ["61000","Huyện Mang Yang","VN-211"],
                ["61000","Huyện Phú Thiện","VN-212"],
                ["61000","Thị Xã An Khê","VN-213"],
                ["61000","Thị Xã Ayun Pa","VN-214"]
            ]],
            ["VN","VN-22","Hà Giang", [
                ["20000","Thành Phố Hà Giang","VN-215"],
                ["20000","Huyện Bắc Mê","VN-216"],
                ["20000","Huyện Bắc Quang","VN-217"],
                ["20000","Huyện Đồng Văn","VN-218"],
                ["20000","Huyện Hoàng Su Phì","VN-219"],
                ["20000","Huyện Mèo Vạc","VN-220"],
                ["20000","Huyện Quản Bạ","VN-221"],
                ["20000","Huyện Quang Bình","VN-222"],
                ["20000","Huyện Vị Xuyên","VN-223"],
                ["20000","Huyện Xín Mần","VN-224"],
                ["20000","Huyện Yên Minh","VN-225"]
            ]],
            ["VN","VN-23","Hà Nam", [
                ["18000","Thành Phố Phủ Lý","VN-226"],
                ["18000","Huyện Bình Lục","VN-227"],
                ["18000","Thị Xã Duy Tiên","VN-228"],
                ["18000","Huyện Kim Bảng","VN-229"],
                ["18000","Huyện Lý Nhân","VN-230"],
                ["18000","Huyện Thanh Liêm","VN-231"]
            ]],
            ["VN","VN-24","Hà Nội", [
                ["10000","Quận Ba Đình","VN-232"],
                ["10000","Quận Bắc Từ Liêm","VN-233"],
                ["10000","Quận Cầu Giấy","VN-234"],
                ["10000","Quận Đống Đa","VN-235"],
                ["10000","Quận Hà Đông","VN-236"],
                ["10000","Quận Hai Bà Trưng","VN-237"],
                ["10000","Quận Hoàn Kiếm","VN-238"],
                ["10000","Quận Hoàng Mai","VN-239"],
                ["10000","Quận Nam Từ Liêm","VN-240"],
                ["10000","Quận Tây Hồ","VN-241"],
                ["10000","Quận Thanh Xuân","VN-242"],
                ["10000","Huyện Ba Vì","VN-243"],
                ["10000","Huyện Đan Phượng","VN-244"],
                ["10000","Huyện Gia Lâm","VN-245"],
                ["10000","Huyện Hoài Đức","VN-246"],
                ["10000","Huyện Mỹ Đức","VN-247"],
                ["10000","Huyện Phúc Thọ","VN-248"],
                ["10000","Huyện Quốc Oai","VN-249"],
                ["10000","Huyện Sóc Sơn","VN-250"],
                ["10000","Huyện Thạch Thất","VN-251"],
                ["10000","Huyện Thanh Trì","VN-252"],
                ["10000","Thị Xã Sơn Tây","VN-253"],
                ["10000","Quận Long Biên","VN-254"],
                ["10000","Huyện Chương Mỹ","VN-255"],
                ["10000","Huyện Đông Anh","VN-256"],
                ["10000","Huyện Mê Linh","VN-257"],
                ["10000","Huyện Phú Xuyên","VN-258"],
                ["10000","Huyện Thanh Oai","VN-259"],
                ["10000","Huyện Thường Tín","VN-260"],
                ["10000","Huyện Ứng Hòa","VN-261"]
            ]],
            ["VN","VN-25","Hà Tĩnh", [
                ["45000","Thành Phố Hà Tĩnh","VN-262"],
                ["45000","Huyện Cẩm Xuyên","VN-263"],
                ["45000","Huyện Can Lộc","VN-264"],
                ["45000","Huyện Đức Thọ","VN-265"],
                ["45000","Huyện Hương Khê","VN-266"],
                ["45000","Huyện Hương Sơn","VN-267"],
                ["45000","Huyện Kỳ Anh","VN-268"],
                ["45000","Huyện Lộc Hà","VN-269"],
                ["45000","Huyện Nghi Xuân","VN-270"],
                ["45000","Huyện Thạch Hà","VN-271"],
                ["45000","Huyện Vũ Quang","VN-272"],
                ["45000","Thị Xã Hồng Lĩnh","VN-273"],
                ["45000","Thị Xã Kỳ Anh","VN-274"]
            ]],
            ["VN","VN-26","Hải Dương", [
                ["3000","Thành Phố Hải Dương","VN-275"],
                ["3000","Huyện Bình Giang","VN-276"],
                ["3000","Huyện Cẩm Giàng","VN-277"],
                ["3000","Huyện Gia Lộc","VN-278"],
                ["3000","Huyện Kim Thành","VN-279"],
                ["3000","Huyện Kinh Môn","VN-280"],
                ["3000","Huyện Nam Sách","VN-281"],
                ["3000","Huyện Ninh Giang","VN-282"],
                ["3000","Huyện Thanh Hà","VN-283"],
                ["3000","Huyện Thanh Miện","VN-284"],
                ["3000","Huyện Tứ Kỳ","VN-285"],
                ["3000","Thành Phố Chí Linh","VN-286"]
            ]],
            ["VN","VN-27","Hải Phòng", [
                ["4000","Quận Hải An","VN-287"],
                ["4000","Quận Hồng Bàng","VN-288"],
                ["4000","Quận Kiến An","VN-289"],
                ["4000","Quận Lê Chân","VN-290"],
                ["4000","Quận Ngô Quyền","VN-291"],
                ["4000","Huyện An Dương","VN-292"],
                ["4000","Huyện An Lão","VN-293"],
                ["4000","Huyện Cát Hải","VN-294"],
                ["4000","Huyện Kiến Thụy","VN-295"],
                ["4000","Huyện Thủy Nguyên","VN-296"],
                ["4000","Huyện Tiên Lãng","VN-297"],
                ["4000","Huyện Vĩnh Bảo","VN-298"],
                ["4000","Quận Đồ Sơn","VN-299"],
                ["4000","Quận Dương Kinh","VN-300"],
                ["4000","HuyệN Bạch Long Vĩ","VN-301"]
            ]],
            ["VN","VN-28","Hậu Giang", [
                ["95000","Thành Phố Vị Thanh","VN-302"],
                ["95000","Huyện Châu Thành","VN-303"],
                ["95000","Huyện Châu Thành A","VN-304"],
                ["95000","Huyện Long Mỹ","VN-305"],
                ["95000","Huyện Phụng Hiệp","VN-306"],
                ["95000","Huyện Vị Thủy","VN-307"],
                ["95000","Thị Xã Long Mỹ","VN-308"],
                ["95000","Thành Phố Ngã Bảy","VN-309"]
            ]],
            ["VN","VN-29","Hồ Chí Minh", [
                ["70000","Quận 1","VN-310"],
                ["70000","Quận 10","VN-311"],
                ["70000","Quận 11","VN-312"],
                ["70000","Quận 12","VN-313"],
                ["70000","Quận 2","VN-314"],
                ["70000","Quận 3","VN-315"],
                ["70000","Quận 4","VN-316"],
                ["70000","Quận 5","VN-317"],
                ["70000","Quận 6","VN-318"],
                ["70000","Quận 7","VN-319"],
                ["70000","Quận 8","VN-320"],
                ["70000","Quận 9","VN-321"],
                ["70000","Quận Bình Tân","VN-322"],
                ["70000","Quận Bình Thạnh","VN-323"],
                ["70000","Quận Gò Vấp","VN-324"],
                ["70000","Quận Phú Nhuận","VN-325"],
                ["70000","Quận Tân Bình","VN-326"],
                ["70000","Quận Tân Phú","VN-327"],
                ["70000","Thành Phố  Thủ Đức","VN-328"],
                ["70000","Huyện Bình Chánh","VN-329"],
                ["70000","Huyện Cần Giờ","VN-330"],
                ["70000","Huyện Củ Chi","VN-331"],
                ["70000","Huyện Hóc Môn","VN-332"],
                ["70000","Huyện Nhà Bè","VN-333"]
            ]],
            ["VN","VN-30","Hòa Bình", [
                ["36000","Thành Phố Hòa Bình","VN-334"],
                ["36000","Huyện Cao Phong","VN-335"],
                ["36000","Huyện Đà Bắc","VN-336"],
                ["36000","Huyện Kim Bôi","VN-337"],
                ["36000","Huyện Kỳ Sơn","VN-338"],
                ["36000","Huyện Lạc Sơn","VN-339"],
                ["36000","Huyện Lạc Thủy","VN-340"],
                ["36000","Huyện Lương Sơn","VN-341"],
                ["36000","Huyện Mai Châu","VN-342"],
                ["36000","Huyện Tân Lạc","VN-343"],
                ["36000","Huyện Yên Thủy","VN-344"]
            ]],
            ["VN","VN-31","Hưng Yên", [
                ["17000","Thị Xã Mỹ Hào","VN-345"],
                ["17000","Thành Phố Hưng Yên","VN-346"],
                ["17000","Huyện Ân Thi","VN-347"],
                ["17000","Huyện Khoái Châu","VN-348"],
                ["17000","Huyện Kim Động","VN-349"],
                ["17000","Huyện Phù Cừ","VN-350"],
                ["17000","Huyện Tiên Lữ","VN-351"],
                ["17000","Huyện Văn Giang","VN-352"],
                ["17000","Huyện Văn Lâm","VN-353"],
                ["17000","Huyện Yên Mỹ","VN-354"]
            ]],
            ["VN","VN-32","Khánh Hòa", [
                ["57000","Thành Phố Nha Trang","VN-355"],
                ["57000","Huyện Cam Lâm","VN-356"],
                ["57000","Huyện Diên Khánh","VN-357"],
                ["57000","Huyện Khánh Sơn","VN-358"],
                ["57000","Thành Phố Cam Ranh","VN-359"],
                ["57000","Thị Xã Ninh Hòa","VN-360"],
                ["57000","Huyện Khánh Vĩnh","VN-361"],
                ["57000","Huyện Vạn Ninh","VN-362"],
                ["57000","Huyện Trường Sa","VN-363"]
            ]],
            ["VN","VN-33","Kiên Giang", [
                ["91000","Huyện Châu Thành","VN-364"],
                ["91000","Huyện Hòn Đất","VN-365"],
                ["91000","Huyện Kiên Lương","VN-366"],
                ["91000","Thành Phố Rạch Giá","VN-367"],
                ["91000","Huyện An Biên","VN-368"],
                ["91000","Huyện An Minh","VN-369"],
                ["91000","Huyện Gò Quao","VN-370"],
                ["91000","Huyện Tân Hiệp","VN-371"],
                ["91000","Huyện U Minh Thượng","VN-372"],
                ["91000","Thành Phố Hà Tiên","VN-373"],
                ["91000","Huyện Giang Thành","VN-374"],
                ["91000","Huyện Giồng Riềng","VN-375"],
                ["91000","Thành Phố Phú Quốc","VN-376"],
                ["91000","Huyện Vĩnh Thuận","VN-377"],
                ["91000","Huyện Kiên Hải","VN-378"]
            ]],
            ["VN","VN-34","Kon Tum", [
                ["60000","Huyện Đắk Glei","VN-379"],
                ["60000","Thành Phố Kon Tum","VN-380"],
                ["60000","Huyện Đắk Hà","VN-381"],
                ["60000","Huyện Đắk Tô","VN-382"],
                ["60000","Huyện Ia H' Drai","VN-383"],
                ["60000","Huyện Kon Plông","VN-384"],
                ["60000","Huyện Kon Rẫy","VN-385"],
                ["60000","Huyện Ngọc Hồi","VN-386"],
                ["60000","Huyện Sa Thầy","VN-387"],
                ["60000","Huyện Tu Mơ Rông","VN-388"]
            ]],
            ["VN","VN-35","Lai Châu", [
                ["30000","Thành Phố Lai Châu","VN-389"],
                ["30000","Huyện Mường Tè","VN-390"],
                ["30000","Huyện Nậm Nhùn","VN-391"],
                ["30000","Huyện Phong Thổ","VN-392"],
                ["30000","Huyện Sìn Hồ","VN-393"],
                ["30000","Huyện Tam Đường","VN-394"],
                ["30000","Huyện Tân Uyên","VN-395"],
                ["30000","Huyện Than Uyên","VN-396"]
            ]],
            ["VN","VN-36","Lâm Đồng", [
                ["66000","Huyện Đơn Dương","VN-397"],
                ["66000","Thành Phố Bảo Lộc","VN-398"],
                ["66000","Thành Phố Đà Lạt","VN-399"],
                ["66000","Huyện Bảo Lâm","VN-400"],
                ["66000","Huyện Cát Tiên","VN-401"],
                ["66000","Huyện Đạ Huoai","VN-402"],
                ["66000","Huyện Đạ Tẻh","VN-403"],
                ["66000","Huyện Đam Rông","VN-404"],
                ["66000","Huyện Di Linh","VN-405"],
                ["66000","Huyện Đức Trọng","VN-406"],
                ["66000","Huyện Lạc Dương","VN-407"],
                ["66000","Huyện Lâm Hà","VN-408"]
            ]],
            ["VN","VN-37","Lạng Sơn", [
                ["25000","Thành Phố Lạng Sơn","VN-409"],
                ["25000","Huyện Bắc Sơn","VN-410"],
                ["25000","Huyện Bình Gia","VN-411"],
                ["25000","Huyện Cao Lộc","VN-412"],
                ["25000","Huyện Chi Lăng","VN-413"],
                ["25000","Huyện Đình Lập","VN-414"],
                ["25000","Huyện Hữu Lũng","VN-415"],
                ["25000","Huyện Lộc Bình","VN-416"],
                ["25000","Huyện Tràng Định","VN-417"],
                ["25000","Huyện Văn Lãng","VN-418"],
                ["25000","Huyện Văn Quan","VN-419"]
            ]],
            ["VN","VN-38","Lào Cai", [
                ["31000","Thành Phố Lào Cai","VN-420"],
                ["31000","Huyện Bắc Hà","VN-421"],
                ["31000","Huyện Bảo Thắng","VN-422"],
                ["31000","Huyện Bảo Yên","VN-423"],
                ["31000","Huyện Bát Xát","VN-424"],
                ["31000","Huyện Mường Khương","VN-425"],
                ["31000","Thị Xã Sa pa","VN-426"],
                ["31000","Huyện Si Ma Cai","VN-427"],
                ["31000","Huyện Văn Bàn","VN-428"]
            ]],
            ["VN","VN-39","Long An", [
                ["82000","Thành Phố Tân An","VN-429"],
                ["82000","Huyện Bến Lức","VN-430"],
                ["82000","Huyện Cần Đước","VN-431"],
                ["82000","Huyện Cần Giuộc","VN-432"],
                ["82000","Huyện Châu Thành","VN-433"],
                ["82000","Huyện Đức Hòa","VN-434"],
                ["82000","Huyện Đức Huệ","VN-435"],
                ["82000","Huyện Mộc Hóa","VN-436"],
                ["82000","Huyện Tân Hưng","VN-437"],
                ["82000","Huyện Tân Thạnh","VN-438"],
                ["82000","Huyện Tân Trụ","VN-439"],
                ["82000","Huyện Thạnh Hóa","VN-440"],
                ["82000","Huyện Thủ Thừa","VN-441"],
                ["82000","Huyện Vĩnh Hưng","VN-442"],
                ["82000","Thị Xã Kiến Tường","VN-443"]
            ]],
            ["VN","VN-40","Nam Định", [
                ["7000","Thành Phố Nam Định","VN-444"],
                ["7000","Huyện Giao Thủy","VN-445"],
                ["7000","Huyện Hải Hậu","VN-446"],
                ["7000","Huyện Mỹ Lộc","VN-447"],
                ["7000","Huyện Nam Trực","VN-448"],
                ["7000","Huyện Nghĩa Hưng","VN-449"],
                ["7000","Huyện Trực Ninh","VN-450"],
                ["7000","Huyện Vụ Bản","VN-451"],
                ["7000","Huyện Xuân Trường","VN-452"],
                ["7000","Huyện Ý Yên","VN-453"]
            ]],
            ["VN","VN-41","Nghệ An", [
                ["43000","Thành Phố Vinh","VN-454"],
                ["43000","Huyện Anh Sơn","VN-455"],
                ["43000","Huyện Con Cuông","VN-456"],
                ["43000","Huyện Diễn Châu","VN-457"],
                ["43000","Huyện Đô Lương","VN-458"],
                ["43000","Huyện Hưng Nguyên","VN-459"],
                ["43000","Huyện Kỳ Sơn","VN-460"],
                ["43000","Huyện Nam Đàn","VN-461"],
                ["43000","Huyện Nghi Lộc","VN-462"],
                ["43000","Huyện Nghĩa Đàn","VN-463"],
                ["43000","Huyện Quế Phong","VN-464"],
                ["43000","Huyện Quỳ Châu","VN-465"],
                ["43000","Huyện Quỳ Hợp","VN-466"],
                ["43000","Huyện Quỳnh Lưu","VN-467"],
                ["43000","Huyện Tân Kỳ","VN-468"],
                ["43000","Huyện Thanh Chương","VN-469"],
                ["43000","Huyện Tương Dương","VN-470"],
                ["43000","Huyện Yên Thành","VN-471"],
                ["43000","Thị Xã Cửa Lò","VN-472"],
                ["43000","Thị Xã Hoàng Mai","VN-473"],
                ["43000","Thị Xã Thái Hòa","VN-474"]
            ]],
            ["VN","VN-42","Ninh Bình", [
                ["8000","Thành Phố Ninh Bình","VN-475"],
                ["8000","Thành Phố Tam Điệp","VN-476"],
                ["8000","Huyện Gia Viễn","VN-477"],
                ["8000","Huyện Hoa Lư","VN-478"],
                ["8000","Huyện Kim Sơn","VN-479"],
                ["8000","Huyện Nho Quan","VN-480"],
                ["8000","Huyện Yên Khánh","VN-481"],
                ["8000","Huyện Yên Mô","VN-482"]
            ]],
            ["VN","VN-43","Ninh Thuận", [
                ["59000","Thành phố Phan Rang","VN-483"],
                ["59000","Huyện Ninh Hải","VN-484"],
                ["59000","Huyện Ninh Phước","VN-485"],
                ["59000","Huyện Ninh Sơn","VN-486"],
                ["59000","Huyện Thuận Bắc","VN-487"],
                ["59000","Huyện Thuận Nam","VN-488"],
                ["59000","Huyện Bác Ái","VN-489"]
            ]],
            ["VN","VN-44","Phú Thọ", [
                ["35000","Thành Phố Việt Trì","VN-490"],
                ["35000","Huyện Cẩm Khê","VN-491"],
                ["35000","Huyện Đoan Hùng","VN-492"],
                ["35000","Huyện Hạ Hòa","VN-493"],
                ["35000","Huyện Lâm Thao","VN-494"],
                ["35000","Huyện Phù Ninh","VN-495"],
                ["35000","Huyện Tam Nông","VN-496"],
                ["35000","Huyện Tân Sơn","VN-497"],
                ["35000","Huyện Thanh Ba","VN-498"],
                ["35000","Huyện Thanh Sơn","VN-499"],
                ["35000","Huyện Thanh Thủy","VN-500"],
                ["35000","Huyện Yên Lập","VN-501"],
                ["35000","Thị Xã Phú Thọ","VN-502"]
            ]],
            ["VN","VN-45","Phú Yên", [
                ["56000","Huyện Đông Hòa","VN-503"],
                ["56000","Huyện Phú Hòa","VN-504"],
                ["56000","Huyện Tây Hòa","VN-505"],
                ["56000","Thành Phố Tuy Hòa","VN-506"],
                ["56000","Thị Xã Sông Cầu","VN-507"],
                ["56000","Huyện Đồng Xuân","VN-508"],
                ["56000","Huyện Sơn Hòa","VN-509"],
                ["56000","Huyện Sông Hinh","VN-510"],
                ["56000","Huyện Tuy An","VN-511"]
            ]],
            ["VN","VN-46","Quảng Bình", [
                ["47000","Thành Phố Đồng Hới","VN-512"],
                ["47000","Huyện Bố Trạch","VN-513"],
                ["47000","Huyện Lệ Thủy","VN-514"],
                ["47000","Huyện Minh Hóa","VN-515"],
                ["47000","Huyện Quảng Ninh","VN-516"],
                ["47000","Huyện Quảng Trạch","VN-517"],
                ["47000","Huyện Tuyên Hóa","VN-518"],
                ["47000","Thị Xã Ba Đồn","VN-519"]
            ]],
            ["VN","VN-47","Quảng Nam", [
                ["51000","Thành Phố Hội An","VN-520"],
                ["51000","Thành Phố Tam Kỳ","VN-521"],
                ["51000","Huyện Bắc Trà My","VN-522"],
                ["51000","Huyện Đại Lộc","VN-523"],
                ["51000","Huyện Đông Giang","VN-524"],
                ["51000","Huyện Duy Xuyên","VN-525"],
                ["51000","Huyện Hiệp Đức","VN-526"],
                ["51000","Huyện Nam Giang","VN-527"],
                ["51000","Huyện Nam Trà My","VN-528"],
                ["51000","Huyện Nông Sơn","VN-529"],
                ["51000","Huyện Núi Thành","VN-530"],
                ["51000","Huyện Phú Ninh","VN-531"],
                ["51000","Huyện Phước Sơn","VN-532"],
                ["51000","Huyện Quế Sơn","VN-533"],
                ["51000","Huyện Tây Giang","VN-534"],
                ["51000","Huyện Thăng Bình","VN-535"],
                ["51000","Huyện Tiên Phước","VN-536"],
                ["51000","Thị Xã Điện Bàn","VN-537"]
            ]],
            ["VN","VN-48","Quảng Ngãi", [
                ["53000","Thành Phố Quảng Ngãi","VN-538"],
                ["53000","Huyện Ba Tơ","VN-539"],
                ["53000","Huyện Bình Sơn","VN-540"],
                ["53000","Thị Xã Đức Phổ","VN-541"],
                ["53000","Huyện Lý Sơn","VN-542"],
                ["53000","Huyện Minh Long","VN-543"],
                ["53000","Huyện Mộ Đức","VN-544"],
                ["53000","Huyện Nghĩa Hành","VN-545"],
                ["53000","Huyện Sơn Hà","VN-546"],
                ["53000","Huyện Sơn Tây","VN-547"],
                ["53000","Huyện Sơn Tịnh","VN-548"],
                ["53000","Huyện Tây Trà","VN-549"],
                ["53000","Huyện Trà Bồng","VN-550"],
                ["53000","Huyện Tư Nghĩa","VN-551"]
            ]],
            ["VN","VN-49","Quảng Ninh", [
                ["1000","Thành Phố Cẩm Phả","VN-552"],
                ["1000","Thành Phố Hạ Long","VN-553"],
                ["1000","Huyện Ba Chẽ","VN-554"],
                ["1000","Huyện Bình Liêu","VN-555"],
                ["1000","Huyện Cô Tô","VN-556"],
                ["1000","Huyện Đầm Hà","VN-557"],
                ["1000","Huyện Hải Hà","VN-558"],
                ["1000","Huyện Hoành Bồ","VN-559"],
                ["1000","Huyện Tiên Yên","VN-560"],
                ["1000","Huyện Vân Đồn","VN-561"],
                ["1000","Thành Phố Móng Cái","VN-562"],
                ["1000","Thành Phố Uông Bí","VN-563"],
                ["1000","Thị Xã Đông Triều","VN-564"],
                ["1000","Thị Xã Quảng Yên","VN-565"]
            ]],
            ["VN","VN-50","Quảng Trị", [
                ["48000","Thành Phố Đông Hà","VN-566"],
                ["48000","Huyện Cam Lộ","VN-567"],
                ["48000","Huyện Đa Krông","VN-568"],
                ["48000","Huyện Gio Linh","VN-569"],
                ["48000","Huyện Hải Lăng","VN-570"],
                ["48000","Huyện Hướng Hóa","VN-571"],
                ["48000","Huyện Triệu Phong","VN-572"],
                ["48000","Huyện Vĩnh Linh","VN-573"],
                ["48000","Thị Xã Quảng Trị","VN-574"],
                ["48000","Huyện Cồn Cỏ","VN-575"]
            ]],
            ["VN","VN-51","Sóc Trăng", [
                ["96000","Thành Phố Sóc Trăng","VN-576"],
                ["96000","Huyện Châu Thành","VN-577"],
                ["96000","Huyện Cù Lao Dung","VN-578"],
                ["96000","Huyện Kế Sách","VN-579"],
                ["96000","Huyện Long Phú","VN-580"],
                ["96000","Huyện Mỹ Tú","VN-581"],
                ["96000","Huyện Mỹ Xuyên","VN-582"],
                ["96000","Huyện Thạnh Trị","VN-583"],
                ["96000","Huyện Trần Đề","VN-584"],
                ["96000","Thị Xã Ngã Năm","VN-585"],
                ["96000","Thị Xã Vĩnh Châu","VN-586"]
            ]],
            ["VN","VN-52","Sơn La", [
                ["34000","Thành Phố Sơn La","VN-587"],
                ["34000","Huyện Bắc Yên","VN-588"],
                ["34000","Huyện Mai Sơn","VN-589"],
                ["34000","Huyện Mộc Châu","VN-590"],
                ["34000","Huyện Mường La","VN-591"],
                ["34000","Huyện Phù Yên","VN-592"],
                ["34000","Huyện Quỳnh Nhai","VN-593"],
                ["34000","Huyện Sông Mã","VN-594"],
                ["34000","Huyện Sốp Cộp","VN-595"],
                ["34000","Huyện Thuận Châu","VN-596"],
                ["34000","Huyện Vân Hồ","VN-597"],
                ["34000","Huyện Yên Châu","VN-598"]
            ]],
            ["VN","VN-53","Tây Ninh", [
                ["80000","Thành Phố Tây Ninh","VN-599"],
                ["80000","Huyện Tân Biên","VN-600"],
                ["80000","Huyện Tân Châu","VN-601"],
                ["80000","Huyện Bến Cầu","VN-602"],
                ["80000","Huyện Châu Thành","VN-603"],
                ["80000","Huyện Dương Minh Châu","VN-604"],
                ["80000","Huyện Gò Dầu","VN-605"],
                ["80000","Thị Xã Hòa Thành","VN-606"],
                ["80000","Thị Xã Trảng Bàng","VN-607"]
            ]],
            ["VN","VN-54","Thái Bình", [
                ["6000","Thành Phố Thái Bình","VN-608"],
                ["6000","Huyện Đông Hưng","VN-609"],
                ["6000","Huyện Hưng Hà","VN-610"],
                ["6000","Huyện Kiến Xương","VN-611"],
                ["6000","Huyện Quỳnh Phụ","VN-612"],
                ["6000","Huyện Thái Thụy","VN-613"],
                ["6000","Huyện Tiền Hải","VN-614"],
                ["6000","Huyện Vũ Thư","VN-615"]
            ]],
            ["VN","VN-55","Thái Nguyên", [
                ["24000","Thành Phố Sông Công","VN-616"],
                ["24000","Thành Phố Thái Nguyên","VN-617"],
                ["24000","Thị Xã Phổ Yên","VN-618"],
                ["24000","Huyện Đại Từ","VN-619"],
                ["24000","Huyện Định Hóa","VN-620"],
                ["24000","Huyện Đồng Hỷ","VN-621"],
                ["24000","Huyện Phú Bình","VN-622"],
                ["24000","Huyện Phú Lương","VN-623"],
                ["24000","Huyện Võ Nhai","VN-624"]
            ]],
            ["VN","VN-56","Thanh Hóa", [
                ["40000","Thành Phố Thanh Hóa","VN-625"],
                ["40000","Thị Xã Bỉm Sơn","VN-626"],
                ["40000","Huyện Cẩm Thủy","VN-627"],
                ["40000","Huyện Đông Sơn","VN-628"],
                ["40000","Huyện Hà Trung","VN-629"],
                ["40000","Huyện Hậu Lộc","VN-630"],
                ["40000","Huyện Hoằng Hóa","VN-631"],
                ["40000","Huyện Nga Sơn","VN-632"],
                ["40000","Huyện Ngọc Lặc","VN-633"],
                ["40000","Huyện Như Thanh","VN-634"],
                ["40000","Huyện Như Xuân","VN-635"],
                ["40000","Huyện Nông Cống","VN-636"],
                ["40000","Huyện Quảng Xương","VN-637"],
                ["40000","Huyện Thạch Thành","VN-638"],
                ["40000","Huyện Thiệu Hóa","VN-639"],
                ["40000","Huyện Thọ Xuân","VN-640"],
                ["40000","Huyện Thường Xuân","VN-641"],
                ["40000","Huyện Tĩnh Gia","VN-642"],
                ["40000","Huyện Triệu Sơn","VN-643"],
                ["40000","Huyện Vĩnh Lộc","VN-644"],
                ["40000","Huyện Yên Định","VN-645"],
                ["40000","Thị Xã Nghi Sơn","VN-646"],
                ["40000","Thành Phố Sầm Sơn","VN-647"],
                ["40000","Huyện Bá Thước","VN-648"],
                ["40000","Huyện Lang Chánh","VN-649"],
                ["40000","Huyện Mường Lát","VN-650"],
                ["40000","Huyện Quan Hóa","VN-651"],
                ["40000","Huyện Quan Sơn","VN-652"]
            ]],
            ["VN","VN-57","Thừa Thiên Huế", [
                ["49000","Thành Phố Huế","VN-653"],
                ["49000","Huyện A Lưới","VN-654"],
                ["49000","Huyện Nam Đông","VN-655"],
                ["49000","Huyện Phong Điền","VN-656"],
                ["49000","Huyện Phú Lộc","VN-657"],
                ["49000","Huyện Phú Vang","VN-658"],
                ["49000","Huyện Quảng Điền","VN-659"],
                ["49000","Thị Xã Hương Thủy","VN-660"],
                ["49000","Thị Xã Hương Trà","VN-661"]
            ]],
            ["VN","VN-58","Tiền Giang", [
                ["84000","Thành Phố Mỹ Tho","VN-662"],
                ["84000","Huyện Cái Bè","VN-663"],
                ["84000","Huyện Cai Lậy","VN-664"],
                ["84000","Huyện Châu Thành","VN-665"],
                ["84000","Huyện Chợ Gạo","VN-666"],
                ["84000","Huyện Gò Công Đông","VN-667"],
                ["84000","Huyện Gò Công Tây","VN-668"],
                ["84000","Huyện Tân Phú Đông","VN-669"],
                ["84000","Huyện Tân Phước","VN-670"],
                ["84000","Thị Xã Cai Lậy","VN-671"],
                ["84000","Thị Xã Gò Công","VN-672"]
            ]],
            ["VN","VN-59","Trà Vinh", [
                ["87000","Thành Phố Trà Vinh","VN-673"],
                ["87000","Huyện Càng Long","VN-674"],
                ["87000","Huyện Cầu Kè","VN-675"],
                ["87000","Huyện Cầu Ngang","VN-676"],
                ["87000","Huyện Châu Thành","VN-677"],
                ["87000","Huyện Duyên Hải","VN-678"],
                ["87000","Huyện Tiểu Cần","VN-679"],
                ["87000","Huyện Trà Cú","VN-680"],
                ["87000","Thị Xã Duyên Hải","VN-681"]
            ]],
            ["VN","VN-60","Tuyên Quang", [
                ["22000","Thành Phố Tuyên Quang","VN-682"],
                ["22000","Huyện Chiêm Hóa","VN-683"],
                ["22000","Huyện Hàm Yên","VN-684"],
                ["22000","Huyện Lâm Bình","VN-685"],
                ["22000","Huyện Nà Hang","VN-686"],
                ["22000","Huyện Sơn Dương","VN-687"],
                ["22000","Huyện Yên Sơn","VN-688"]
            ]],
            ["VN","VN-61","Vĩnh Long", [
                ["85000","Thành Phố Vĩnh Long","VN-689"],
                ["85000","Huyện Bình Tân","VN-690"],
                ["85000","Huyện Long Hồ","VN-691"],
                ["85000","Huyện Mang Thít","VN-692"],
                ["85000","Huyện Tam Bình","VN-693"],
                ["85000","Huyện Trà Ôn","VN-694"],
                ["85000","Huyện Vũng Liêm","VN-695"],
                ["85000","Thị Xã Bình Minh","VN-696"]
            ]],
            ["VN","VN-62","Vĩnh Phúc", [
                ["15000","Thành Phố Vĩnh Yên","VN-697"],
                ["15000","Huyện Bình Xuyên","VN-698"],
                ["15000","Huyện Lập Thạch","VN-699"],
                ["15000","Huyện Sông Lô","VN-700"],
                ["15000","Huyện Tam Đảo","VN-701"],
                ["15000","Huyện Tam Dương","VN-702"],
                ["15000","Huyện Vĩnh Tường","VN-703"],
                ["15000","Huyện Yên Lạc","VN-704"],
                ["15000","Thành Phố Phúc Yên","VN-705"]
            ]],
            ["VN","VN-63","Yên Bái", [
                ["33000","Thành Phố Yên Bái","VN-706"],
                ["33000","Huyện Lục Yên","VN-707"],
                ["33000","Huyện Mù Căng Chải","VN-708"],
                ["33000","Huyện Trạm Tấu","VN-709"],
                ["33000","Huyện Trấn Yên","VN-710"],
                ["33000","Huyện Văn Chấn","VN-711"],
                ["33000","Huyện Văn Yên","VN-712"],
                ["33000","Huyện Yên Bình","VN-713"],
                ["33000","Thị Xã Nghĩa Lộ","VN-714"]
            ]]
        ];
    }

    private function deleteExistingRegionData()
    {
        $adapter = $this->moduleDataSetup->getConnection();
        $adapter->delete(
            $adapter->getTableName('directory_country_region'),
            [
                'country_id = ?' => 'VN',
            ]
        );
    }

}

