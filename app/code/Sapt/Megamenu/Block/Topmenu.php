<?php
namespace Sapt\Megamenu\Block;

use Amore\GaTagging\Model\CommonVariable;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Topmenu extends \Magento\Framework\View\Element\Template
{

    protected $_categoryHelper;
    protected $_categoryFlatConfig;
    protected $_topMenu;
    protected $_categoryFactory;
    protected $_helper;
    protected $_filterProvider;
    protected $_blockFactory;
    protected $_megamenuConfig;
    protected $_storeManager;
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Sapt\Megamenu\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Theme\Block\Html\Topmenu $topMenu,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {

        $this->_categoryHelper = $categoryHelper;
        $this->_categoryFlatConfig = $categoryFlatState;
        $this->_categoryFactory = $categoryFactory;
        $this->_topMenu = $topMenu;
        $this->_helper = $helper;
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
        $this->categoryRepository = $categoryRepository;
        $this->_storeManager = $context->getStoreManager();

        parent::__construct($context);
    }

    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    public function getCategoryModel($id)
    {
        $_category = $this->_categoryFactory->create();
        $_category->load($id);

        return $_category;
    }

    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        return $this->_topMenu->getHtml($outermostClass, $childrenWrapClass, $limit);
    }

    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }

    public function getChildCategories($category)
    {
        if ($this->_categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }

        return $subcategories;
    }

    public function getActiveChildCategories($category)
    {
        $children = [];
        if ($this->_categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        foreach($subcategories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $children[] = $category;
        }
        return $children;
    }

    public function getBlockContent($content = '') {
        if(!$this->_filterProvider)
            return $content;
        return $this->_filterProvider->getBlockFilter()->filter(trim($content));
    }

    public function getCustomBlockHtml($type='after') {
        $html = '';

        $block_ids = $this->_megamenuConfig['custom_links']['staticblock_'.$type];

        if (!$block_ids) return '';

        $block_ids = preg_replace('/\s/', '', $block_ids);
        $ids = explode(',', $block_ids);
        $store_id = $this->_storeManager->getStore()->getId();

        foreach($ids as $block_id) {
            $block = $this->_blockFactory->create();
            $block->setStoreId($store_id)->load($block_id);

            if(!$block) continue;

            $block_content = $block->getContent();

            if(!$block_content) continue;

            $content = $this->_filterProvider->getBlockFilter()->setStoreId($store_id)->filter($block_content);
            if(substr($content, 0, 4) == '<ul>')
                $content = substr($content, 4);
            if(substr($content, strlen($content) - 5) == '</ul>')
                $content = substr($content, 0, -5);

            $html .= $content;
        }

        return $html;
    }
    public function getSubmenuItemsHtml($children, $level = 1, $max_level = 0, $column_width=12, $menu_type = 'fullwidth', $columns = null)
    {
        $html = '';

        if(!$max_level || ($max_level && $max_level == 0) || ($max_level && $max_level > 0 && $max_level-1 >= $level)) {
            $column_class = "";
            if($level == 1 && $columns && ($menu_type == 'fullwidth' || $menu_type == 'staticwidth')) {
                $column_class = "col-md-".$column_width." ";
                $column_class .= "mega-columns columns".$columns;
            }
            $i = 0;
            $html = '<ul class="subchildmenu submenu-toggle '.$column_class.'">';
            foreach($children as $child) {
                $parentCategoryName = CommonVariable::CLICK_TAG_GNB;
                $clickArea = CommonVariable::CLICK_AREA;
                $categoryDefaultName = $this->getDefaultStoreCategory($child->getId(), 0)->getName();
                $sub_children = $this->getActiveChildCategories($child);

                $item_class = 'level'.$level.' ';
                $item_class_active = '';

                if ($i == 0){
                    $item_class_active = 'active';
                }

                if(count($sub_children) > 0)
                    $item_class .= 'parent ';
                $html .= '<li class="ui-menu-item '.$item_class.' '.$item_class_active.'">';
                if(count($sub_children) > 0) {
                    $html .= '<div class="open-children-toggle"></div>';
                }
                $html .= '<a href="'.$this->_categoryHelper->getCategoryUrl($child).'" title="'.$child->getName().'"'
                    . ' ap-click-area="'. $clickArea .'"'
                    . 'ap-click-name="'. $parentCategoryName .'"'
                    . 'ap-click-data="'. strtoupper($categoryDefaultName) .'"'
                    . '>';
                $html .= '<span>'.$child->getName();
                $html .= '</span></a>';
                if(count($sub_children) > 0) {
                    $html .= $this->getSubmenuItemsHtml($sub_children, $level+1, $max_level, $column_width, $menu_type);
                }
                $html .= '</li>';
                $i ++;
            }
            $html .= '</ul>';
        }

        return $html;
    }

    public function getMegamenuHtml()
    {
        $html = '';

        $categories = $this->getStoreCategories(true,false,true);

        $this->_megamenuConfig = $this->_helper->getConfig('sapt_megamenu');

        $max_level = $this->_megamenuConfig['general']['max_level'];
        //$html .= $this->getCustomBlockHtml('before');
        foreach($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $cat_model = $this->getCategoryModel($category->getId());
            $children = $this->getActiveChildCategories($category);
            $sapt_menu_cat_columns = $cat_model->getData('sapt_menu_cat_columns');
            $sapt_menu_float_type = $cat_model->getData('sapt_menu_float_type');

            if(!$sapt_menu_cat_columns){
                $sapt_menu_cat_columns = 1;
            }

            $menu_type = $this->_megamenuConfig['general']['menu_type'];

            $custom_style = '';

            $item_class = 'level0 ';
            $item_class .= $menu_type.' ';

            $menu_right_content = $cat_model->getData('sapt_menu_block_right_content');
            $menu_right_width = $cat_model->getData('sapt_menu_block_right_width');
            if(!$menu_right_content || !$menu_right_width)
                $menu_right_width = 0;
            if($sapt_menu_float_type)
                $sapt_menu_float_type = 'fl-'.$sapt_menu_float_type.' ';
            if(count($children) > 0 || (($menu_type=="fullwidth" || $menu_type=="staticwidth") && ($menu_right_content)))
                $item_class .= 'parent ';
            $html .= '<li class="ui-menu-item '.$item_class.$sapt_menu_float_type.'">';
            if(count($children) > 0) {
                $html .= '<div class="open-children-toggle"></div>';
            }

            $parentCategoryName = CommonVariable::CLICK_TAG_GNB;
            $clickArea = CommonVariable::CLICK_AREA;
            if ($category->getParent()->getName()) {
                $parentCategoryName = CommonVariable::CLICK_TAG_GNB;
            }
            $categoryDefaultName = $this->getDefaultStoreCategory($category->getId(), 0)->getName();

            $html .= '<a href="'.$this->_categoryHelper->getCategoryUrl($category).'" class="level-top" title="'.$category->getName().'"'
                . ' ap-click-area="'. $clickArea .'"'
                . 'ap-click-name="'. $parentCategoryName .'"'
                . 'ap-click-data="'. strtoupper($categoryDefaultName) .'"'
                .'>';
            $html .= '<span>'.$category->getName().'</span>';
            $html .= '</a>';
            if(count($children) > 0 || (($menu_type=="fullwidth" || $menu_type=="staticwidth") && ($menu_right_content))) {
                $html .= '<div class="level0 submenu submenu-toggle"'.$custom_style.'>';
                if(($menu_type=="fullwidth" || $menu_type=="staticwidth")) {
                    $html .= '<div class="container">';
                }
                if(count($children) > 0 || (($menu_type=="fullwidth" || $menu_type=="staticwidth") && ($menu_right_content))) {
                    $html .= '<div class="row">';
                    $html .= $this->getSubmenuItemsHtml($children, 1, $max_level, 12-$menu_right_width, $menu_type, $sapt_menu_cat_columns);
                    if(($menu_type=="fullwidth" || $menu_type=="staticwidth") && $menu_right_content && $menu_right_width > 0) {
                        $html .= '<div class="menu-right-block col-md-'.$menu_right_width.'">'.$this->getBlockContent($menu_right_content).'</div>';
                    }
                    $html .= '</div>';
                }
                if(($menu_type=="fullwidth" || $menu_type=="staticwidth")) {
                    $html .= '<a class="btn-close" href="#"></a>';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</li>';
        }
        //$html .= $this->getCustomBlockHtml('after');

        return $html;
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @return CategoryInterface|string
     */
    protected function getDefaultStoreCategory($categoryId, $storeId)
    {
        $categoryIdFiltered = explode("-", $categoryId);
        try {
            return $this->categoryRepository->get(end($categoryIdFiltered), $storeId);
        } catch (NoSuchEntityException $e) {
            return 'MENU';
        }
    }
}
