<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eguana\Directory\Setup\Patch\Data;

use Eguana\Directory\Setup\RegionCityDataInstaller;
use Eguana\Directory\Setup\RegionCityDataInstallerFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class AddNewDataForTaiwan implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var RegionCityDataInstallerFactory
     */
    private $regionCityDataInstallerFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RegionCityDataInstallerFactory $regionCityDataInstallerFactory

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->regionCityDataInstallerFactory = $regionCityDataInstallerFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->deleteExistingRegionData();
        /** @var RegionCityDataInstaller $regionCityDataInstaller */
        $regionCityDataInstaller = $this->regionCityDataInstallerFactory->create();
        $regionCityDataInstaller->addCountryRegionsCities($this->moduleDataSetup->getConnection(), $this->getDataForTaiwan());
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
    public function getDataForTaiwan()
    {
        return [
            ['TW', 'T002','基隆市',[
                ['200','仁愛區'],
                ['201','信義區'],
                ['202','中正區'],
                ['203','中山區'],
                ['204','安樂區'],
                ['205','暖暖區'],
                ['206','七堵區'],
            ]],
            ['TW', 'T001','臺北市',[
                ['100','中正區'],
                ['103','大同區'],
                ['104','中山區'],
                ['105','松山區'],
                ['106','大安區'],
                ['108','萬華區'],
                ['110','信義區'],
                ['111','士林區'],
                ['112','北投區'],
                ['114','內湖區'],
                ['115','南港區'],
                ['116','文山區'],
            ]],
            ['TW', 'T003','新北市',[
                ['207','萬里區'],
                ['208','金山區'],
                ['220','板橋區'],
                ['221','汐止區'],
                ['222','深坑區'],
                ['223','石碇區'],
                ['224','瑞芳區'],
                ['226','平溪區'],
                ['227','雙溪區'],
                ['228','貢寮區'],
                ['231','新店區'],
                ['232','坪林區'],
                ['233','烏來區'],
                ['234','永和區'],
                ['235','中和區'],
                ['236','土城區'],
                ['237','三峽區'],
                ['238','樹林區'],
                ['239','鶯歌區'],
                ['241','三重區'],
                ['242','新莊區'],
                ['243','泰山區'],
                ['244','林口區'],
                ['247','蘆洲區'],
                ['248','五股區'],
                ['249','八里區'],
                ['251','淡水區'],
                ['252','三芝區'],
                ['253','石門區'],
            ]],
            ['TW', 'T007','桃園市',[
                ['320','中壢區'],
                ['324','平鎮區'],
                ['325','龍潭區'],
                ['326','楊梅區'],
                ['327','新屋區'],
                ['328','觀音區'],
                ['330','桃園區'],
                ['333','龜山區'],
                ['334','八德區'],
                ['335','大溪區'],
                ['336','復興區'],
                ['337','大園區'],
                ['338','蘆竹區'],
            ]],
            ['TW', 'T005','新竹市',[
                ['300','新竹市'],
            ]],
            ['TW', 'T006','新竹縣',[
                ['302','竹北市'],
                ['303','湖口鄉'],
                ['304','新豐鄉'],
                ['305','新埔鎮'],
                ['306','關西鎮'],
                ['307','芎林鄉'],
                ['308','寶山鄉'],
                ['310','竹東鎮'],
                ['311','五峰鄉'],
                ['312','橫山鄉'],
                ['313','尖石鄉'],
                ['314','北埔鄉'],
                ['315','峨眉鄉'],
            ]],
            ['TW', 'T008','苗栗縣',[
                ['350','竹南鎮'],
                ['351','頭份市'],
                ['352','三灣鄉'],
                ['353','南庄鄉'],
                ['354','獅潭鄉'],
                ['356','後龍鎮'],
                ['357','通霄鎮'],
                ['358','苑裡鎮'],
                ['360','苗栗市'],
                ['361','造橋鄉'],
                ['362','頭屋鄉'],
                ['363','公館鄉'],
                ['364','大湖鄉'],
                ['365','泰安鄉'],
                ['366','銅鑼鄉'],
                ['367','三義鄉'],
                ['368','西湖鄉'],
                ['369','卓蘭鎮'],
            ]],
            ['TW', 'T009','臺中市',[
                ['400','中 區'],
                ['401','東 區'],
                ['402','南 區'],
                ['403','西 區'],
                ['404','北 區'],
                ['406','北屯區'],
                ['407','西屯區'],
                ['408','南屯區'],
                ['411','太平區'],
                ['412','大里區'],
                ['413','霧峰區'],
                ['414','烏日區'],
                ['420','豐原區'],
                ['421','后里區'],
                ['422','石岡區'],
                ['423','東勢區'],
                ['424','和平區'],
                ['426','新社區'],
                ['427','潭子區'],
                ['428','大雅區'],
                ['429','神岡區'],
                ['432','大肚區'],
                ['433','沙鹿區'],
                ['434','龍井區'],
                ['435','梧棲區'],
                ['436','清水區'],
                ['437','大甲區'],
                ['438','外埔區'],
                ['439','大安區'],
            ]],
            ['TW', 'T011','彰化縣',[
                ['500','彰化市'],
                ['502','芬園鄉'],
                ['503','花壇鄉'],
                ['504','秀水鄉'],
                ['505','鹿港鎮'],
                ['506','福興鄉'],
                ['507','線西鄉'],
                ['508','和美鎮'],
                ['509','伸港鄉'],
                ['510','員林鎮'],
                ['511','社頭鄉'],
                ['512','永靖鄉'],
                ['513','埔心鄉'],
                ['514','溪湖鎮'],
                ['515','大村鄉'],
                ['516','埔鹽鄉'],
                ['520','田中鎮'],
                ['521','北斗鎮'],
                ['522','田尾鄉'],
                ['523','埤頭鄉'],
                ['524','溪州鄉'],
                ['525','竹塘鄉'],
                ['526','二林鎮'],
                ['527','大城鄉'],
                ['528','芳苑鄉'],
                ['530','二水鄉'],
            ]],
            ['TW', 'T012','南投縣',[
                ['540','南投市'],
                ['541','中寮鄉'],
                ['542','草屯鎮'],
                ['544','國姓鄉'],
                ['545','埔里鎮'],
                ['546','仁愛鄉'],
                ['551','名間鄉'],
                ['552','集集鎮'],
                ['553','水里鄉'],
                ['555','魚池鄉'],
                ['556','信義鄉'],
                ['557','竹山鎮'],
                ['558','鹿谷鄉'],
            ]],
            ['TW', 'T015','雲林縣',[
                ['630','斗南鎮'],
                ['631','大埤鄉'],
                ['632','虎尾鎮'],
                ['633','土庫鎮'],
                ['634','褒忠鄉'],
                ['635','東勢鄉'],
                ['636','臺西鄉'],
                ['637','崙背鄉'],
                ['638','麥寮鄉'],
                ['640','斗六市'],
                ['643','林內鄉'],
                ['646','古坑鄉'],
                ['647','莿桐鄉'],
                ['648','西螺鎮'],
                ['649','二崙鄉'],
                ['651','北港區'],
                ['652','水林區'],
                ['653','口湖區'],
                ['654','四湖區'],
                ['655','元長區']
            ]],
            ['TW', 'T013','嘉義市',[
                ['600','嘉義市'],
            ]],
            ['TW', 'T014','嘉義縣',[
                ['602','番路鄉'],
                ['603','梅山鄉'],
                ['604','竹崎鄉'],
                ['605','阿里山鄉'],
                ['606','中埔鄉'],
                ['607','大埔鄉'],
                ['608','水上鄉'],
                ['611','鹿草鄉'],
                ['612','太保市'],
                ['613','朴子市'],
                ['614','東石鄉'],
                ['615','六腳鄉'],
                ['616','新港鄉'],
                ['621','民雄鄉'],
                ['622','大林鎮'],
                ['623','溪口鄉'],
                ['624','義竹鄉'],
                ['625','布袋鎮'],
            ]],
            ['TW', 'T016','臺南市',[
                ['700','中西 區'],
                ['701','東 區'],
                ['702','南 區'],
                ['704','北 區'],
                ['708','安平區'],
                ['709','安南區'],
                ['710','永康區'],
                ['711','歸仁區'],
                ['712','新化區'],
                ['713','左鎮區'],
                ['714','玉井區'],
                ['715','楠西區'],
                ['716','南化區'],
                ['717','仁德區'],
                ['718','關廟區'],
                ['719','龍崎區'],
                ['720','官田區'],
                ['721','麻豆區'],
                ['722','佳里區'],
                ['723','西港區'],
                ['724','七股區'],
                ['725','將軍區'],
                ['726','學甲區'],
                ['727','北門區'],
                ['730','新營區'],
                ['731','後壁區'],
                ['732','白河區'],
                ['733','東山區'],
                ['734','六甲區'],
                ['735','下營區'],
                ['736','柳營區'],
                ['737','鹽水區'],
                ['741','善化區'],
                ['742','大內區'],
                ['743','山上區'],
                ['744','新市區'],
                ['745','安定區'],
            ]],
            ['TW', 'T018','高雄市',[
                ['800','新興區'],
                ['801','前金區'],
                ['802','苓雅區'],
                ['803','鹽埕區'],
                ['804','鼓山區'],
                ['805','旗津區'],
                ['806','前鎮區'],
                ['807','三民區'],
                ['811','楠梓區'],
                ['812','小港區'],
                ['813','左營區'],
                ['814','仁武區'],
                ['815','大社區'],
                ['820','岡山區'],
                ['821','路竹區'],
                ['822','阿蓮區'],
                ['823','田寮區'],
                ['824','燕巢區'],
                ['825','橋頭區'],
                ['826','梓官區'],
                ['827','彌陀區'],
                ['828','永安區'],
                ['829','湖內區'],
                ['830','鳳山區'],
                ['831','大寮區'],
                ['832','林園區'],
                ['833','鳥松區'],
                ['840','大樹區'],
                ['842','旗山區'],
                ['843','美濃區'],
                ['844','六龜區'],
                ['845','內門區'],
                ['846','杉林區'],
                ['847','甲仙區'],
                ['848','桃源區'],
                ['849','那瑪夏區'],
                ['851','茂林區'],
                ['852','茄萣區'],
            ]],
            ['TW', 'T021','屏東縣',[
                ['900','屏東市'],
                ['901','三地門鄉'],
                ['902','霧臺鄉'],
                ['903','瑪家鄉'],
                ['904','九如鄉'],
                ['905','里港鄉'],
                ['906','高樹鄉'],
                ['907','盬埔鄉'],
                ['908','長治鄉'],
                ['909','麟洛鄉'],
                ['911','竹田鄉'],
                ['912','內埔鄉'],
                ['913','萬丹鄉'],
                ['920','潮州鎮'],
                ['921','泰武鄉'],
                ['922','來義鄉'],
                ['923','萬巒鄉'],
                ['924','崁頂鄉'],
                ['925','新埤鄉'],
                ['926','南州鄉'],
                ['927','林邊鄉'],
                ['928','東港鎮'],
                ['929','琉球鄉'],
                ['931','佳冬鄉'],
                ['932','新園鄉'],
                ['940','枋寮鄉'],
                ['941','枋山鄉'],
                ['942','春日鄉'],
                ['943','獅子鄉'],
                ['944','車城鄉'],
                ['945','牡丹鄉'],
                ['946','恆春鎮'],
                ['947','滿州鄉'],
            ]],
            ['TW', 'T022','臺東縣',[
                ['950','臺東市'],
                ['951','綠島鄉'],
                ['952','蘭嶼鄉'],
                ['953','延平鄉'],
                ['954','卑南鄉'],
                ['955','鹿野鄉'],
                ['956','關山鎮'],
                ['957','海端鄉'],
                ['958','池上鄉'],
                ['959','東河鄉'],
                ['961','成功鎮'],
                ['962','長濱鄉'],
                ['963','太麻里鄉'],
                ['964','金峰鄉'],
                ['965','大武鄉'],
                ['966','達仁鄉'],
            ]],
            ['TW', 'T023','花蓮縣',[
                ['970','花蓮市'],
                ['971','新城鄉'],
                ['972','秀林鄉'],
                ['973','吉安鄉'],
                ['974','壽豐鄉'],
                ['975','鳳林鎮'],
                ['976','光復鄉'],
                ['977','豐濱鄉'],
                ['978','瑞穗鄉'],
                ['979','萬榮鄉'],
                ['981','玉里鎮'],
                ['982','卓溪鄉'],
                ['983','富里鄉'],
            ]],
            ['TW', 'T004','宜蘭縣',[
                ['260','宜蘭市'],
                ['261','頭城鎮'],
                ['262','礁溪鄉'],
                ['263','壯圍鄉'],
                ['264','員山鄉'],
                ['265','羅東鎮'],
                ['266','三星鄉'],
                ['267','大同鄉'],
                ['268','五結鄉'],
                ['269','冬山鄉'],
                ['270','蘇澳鎮'],
                ['272','南澳鄉'],
            ]],
            ['TW', 'T020','澎湖縣',[
                ['880','馬公市'],
                ['881','西嶼鄉'],
                ['882','望安鄉'],
                ['883','七美鄉'],
                ['884','白沙鄉'],
                ['885','湖西鄉'],
            ]],
            ['TW', 'T024','金門縣',[
                ['890','金沙鎮'],
                ['891','金湖鎮'],
                ['892','金寧鄉'],
                ['893','金城鎮'],
                ['894','烈嶼鄉'],
                ['896','烏坵鄉'],
            ]],
            ['TW', 'T025','連江縣',[
                ['209','南竿鄉'],
                ['210','北竿鄉'],
                ['211','莒光鄉'],
                ['212','東引鄉'],
            ]],
            ['TW', 'T026','南海諸島',[
                ['817','東沙島'],
                ['819','南沙島'],
            ]],
            ['TW', 'T027','釣魚台列嶼',[
                ['290','釣魚台列嶼'],
            ]],
        ];
    }

    public function deleteExistingRegionData()
    {
        $adapter = $this->moduleDataSetup->getConnection();
        $adapter->delete(
            $adapter->getTableName('directory_country_region'),
            [
                'country_id = ?' => 'TW',
            ]
        );
    }
}

