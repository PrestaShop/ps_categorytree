<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Ps_CategoryTree extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'ps_categorytree';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'PrestaShop';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->getTranslator()->trans('Category tree links', array(), 'Modules.Categorytree.Admin');
        $this->description = $this->getTranslator()->trans('Adds a block featuring product categories.', array(), 'Modules.Categorytree.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install()
            && Configuration::updateValue('BLOCK_CATEG_MAX_DEPTH', 4)
            && Configuration::updateValue('BLOCK_CATEG_ROOT_CATEGORY', 1)
            && $this->registerHook('displayLeftColumn')
        ;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('BLOCK_CATEG_MAX_DEPTH') ||
            !Configuration::deleteByName('BLOCK_CATEG_ROOT_CATEGORY')) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBlockCategories')) {
            $maxDepth = (int)(Tools::getValue('BLOCK_CATEG_MAX_DEPTH'));
            if ($maxDepth < 0) {
                $output .= $this->displayError($this->getTranslator()->trans('Maximum depth: Invalid number.', array(), 'Admin.Notifications.Error'));
            } else {
                Configuration::updateValue('BLOCK_CATEG_MAX_DEPTH', (int)$maxDepth);
                Configuration::updateValue('BLOCK_CATEG_SORT_WAY', Tools::getValue('BLOCK_CATEG_SORT_WAY'));
                Configuration::updateValue('BLOCK_CATEG_SORT', Tools::getValue('BLOCK_CATEG_SORT'));
                Configuration::updateValue('BLOCK_CATEG_ROOT_CATEGORY', Tools::getValue('BLOCK_CATEG_ROOT_CATEGORY'));

                //$this->_clearBlockcategoriesCache();

                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&conf=6');
            }
        }
        return $output.$this->renderForm();
    }

    private function getCategories($category)
    {
        $maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
        if (Validate::isLoadedObject($category)) {
            $idCategory = $category->id;
        } else {
            $idCategory = null;
        }

        $groups = Customer::getGroupsStatic((int)$this->context->customer->id);
        $orderBy = ' ORDER BY c.`level_depth` ASC, ' . (Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'category_shop.`position`') . ' ' . (Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC');
        $categories = Category::getNestedCategories($idCategory, $this->context->language->id, true, $groups, true, '', $orderBy);

        $tree = $this->getTree($categories, $maxdepth);

        return array_shift($tree);
    }

    public function getTree($categories, $maxDepth = 0, $currentDepth = 0)
    {
        $nodes = [];

        foreach ($categories as $category) {
            $link = $this->context->link->getCategoryLink($category['id_category'], $category['link_rewrite']);

            $children = [];
            if ((!$maxDepth || $currentDepth < $maxDepth) && !empty($category['children'])) {
                $children = $this->getTree($category['children'], $maxDepth, $currentDepth + 1);
            }

            $nodes[] = [
                'id' => $category['id_category'],
                'link' => $link,
                'name' => $category['name'],
                'desc'=> $category['description'],
                'children' => $children,
            ];
        }

        return $nodes;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTranslator()->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->getTranslator()->trans('Category root', array(), 'Modules.Categorytree.Admin'),
                        'name' => 'BLOCK_CATEG_ROOT_CATEGORY',
                        'hint' => $this->getTranslator()->trans('Select which category is displayed in the block. The current category is the one the visitor is currently browsing.', array(), 'Modules.Categorytree.Admin'),
                        'values' => array(
                            array(
                                'id' => 'home',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('Home category', array(), 'Modules.Categorytree.Admin')
                            ),
                            array(
                                'id' => 'current',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Current category', array(), 'Modules.Categorytree.Admin')
                            ),
                            array(
                                'id' => 'parent',
                                'value' => 2,
                                'label' => $this->getTranslator()->trans('Parent category', array(), 'Modules.Categorytree.Admin')
                            ),
                            array(
                                'id' => 'current_parent',
                                'value' => 3,
                                'label' => $this->getTranslator()->trans('Current category, unless it has no subcategories, in which case the parent category of the current category is used', array(), 'Modules.Categorytree.Admin')
                            ),
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->getTranslator()->trans('Maximum depth', array(), 'Modules.Categorytree.Admin'),
                        'name' => 'BLOCK_CATEG_MAX_DEPTH',
                        'desc' => $this->getTranslator()->trans('Set the maximum depth of category sublevels displayed in this block (0 = infinite).', array(), 'Modules.Categorytree.Admin'),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->getTranslator()->trans('Sort', array(), 'Admin.Actions'),
                        'name' => 'BLOCK_CATEG_SORT',
                        'values' => array(
                            array(
                                'id' => 'name',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('By name', array(), 'Admin.Global')
                            ),
                            array(
                                'id' => 'position',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('By position', array(), 'Admin.Global')
                            ),
                        )
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->getTranslator()->trans('Sort order', array(), 'Admin.Actions'),
                        'name' => 'BLOCK_CATEG_SORT_WAY',
                        'values' => array(
                            array(
                                'id' => 'name',
                                'value' => 1,
                                'label' => $this->getTranslator()->trans('Descending', array(), 'Admin.Global')
                            ),
                            array(
                                'id' => 'position',
                                'value' => 0,
                                'label' => $this->getTranslator()->trans('Ascending', array(), 'Admin.Global')
                            ),
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->submit_action = 'submitBlockCategories';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues()
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'BLOCK_CATEG_MAX_DEPTH' => Tools::getValue('BLOCK_CATEG_MAX_DEPTH', Configuration::get('BLOCK_CATEG_MAX_DEPTH')),
            'BLOCK_CATEG_SORT_WAY' => Tools::getValue('BLOCK_CATEG_SORT_WAY', Configuration::get('BLOCK_CATEG_SORT_WAY')),
            'BLOCK_CATEG_SORT' => Tools::getValue('BLOCK_CATEG_SORT', Configuration::get('BLOCK_CATEG_SORT')),
            'BLOCK_CATEG_ROOT_CATEGORY' => Tools::getValue('BLOCK_CATEG_ROOT_CATEGORY', Configuration::get('BLOCK_CATEG_ROOT_CATEGORY'))
        );
    }

    public function setLastVisitedCategory()
    {
        if (method_exists($this->context->controller, 'getCategory') && ($category = $this->context->controller->getCategory())) {
            $this->context->cookie->last_visited_category = $category->id;
        } elseif (method_exists($this->context->controller, 'getProduct') && ($product = $this->context->controller->getProduct())) {
            if (!isset($this->context->cookie->last_visited_category)
                || !Product::idIsOnCategoryId($product->id, array(array('id_category' => $this->context->cookie->last_visited_category)))
                || !Category::inShopStatic($this->context->cookie->last_visited_category, $this->context->shop)
            ) {
                $this->context->cookie->last_visited_category = (int)$product->id_category_default;
            }
        }
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->setLastVisitedCategory();
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch('module:ps_categorytree/views/templates/hook/ps_categorytree.tpl');
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $category = new Category((int)Configuration::get('PS_HOME_CATEGORY'), $this->context->language->id);

        if (Configuration::get('BLOCK_CATEG_ROOT_CATEGORY') && isset($this->context->cookie->last_visited_category) && $this->context->cookie->last_visited_category) {
            $category = new Category($this->context->cookie->last_visited_category, $this->context->language->id);
            if (Configuration::get('BLOCK_CATEG_ROOT_CATEGORY') == 2 && !$category->is_root_category && $category->id_parent) {
                $category = new Category($category->id_parent, $this->context->language->id);
            } elseif (Configuration::get('BLOCK_CATEG_ROOT_CATEGORY') == 3 && !$category->is_root_category && !$category->getSubCategories($category->id, true)) {
                $category = new Category($category->id_parent, $this->context->language->id);
            }
        }

        return [
            'categories' => $this->getCategories($category),
            'currentCategory' => $category->id,
        ];
    }
}
