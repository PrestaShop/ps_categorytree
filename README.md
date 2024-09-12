# Category tree links

[![PHP tests](https://github.com/PrestaShop/ps_categorytree/actions/workflows/php.yml/badge.svg)](https://github.com/PrestaShop/ps_categorytree/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/PrestaShop/ps_categorytree/v)](//packagist.org/packages/PrestaShop/ps_categorytree)
[![Total Downloads](https://poser.pugx.org/PrestaShop/ps_categorytree/downloads)](//packagist.org/packages/PrestaShop/ps_categorytree)
[![GitHub license](https://img.shields.io/github/license/PrestaShop/ps_categorytree)](https://github.com/PrestaShop/ps_categorytree/LICENSE.md)

## About

Help navigation on your store, show your visitors current category and subcategories.

## Compatibility

PrestaShop: `1.7.7.0` or later

## How to test

Link to specs: https://docs.prestashop-project.org/functional-documentation/functional-documentation/ux-ui/back-office/improve/modules/category-tree-links-ps_categorytree

Link to test scenario: https://build.prestashop-project.org/test-scenarios/scenarios/modules/ps-categorytree.html

In `BO > Modules`, configure ps_categorytree module - choose a category, set the maximum depth of category sublevels displayed in the block, select an option of sort by (name/position), select an option of sort order (descending /ascending). In FO as a visitor, verify that the current category and subcategories match the configurations in BO.

## Reporting issues

You can report issues with this module in the main PrestaShop repository. [Click here to report an issue][report-issue]. 

## Multistore compatibility

This module is compatible with the multistore :heavy_check_mark: <br/>
It can be configured differently from one store to another.<br/>
It can be configured quickly in the same way on all stores thanks to the all shops context or the group of shops.<br/>
It can be activated on one store and deactivated on another

## Contributing

PrestaShop modules are open source extensions to the [PrestaShop e-commerce platform][prestashop]. Everyone is welcome and even encouraged to contribute with their own improvements!

Just make sure to follow our [contribution guidelines][contribution-guidelines].

## License

This module is released under the [Academic Free License 3.0][AFL-3.0] 

[report-issue]: https://github.com/PrestaShop/PrestaShop/issues/new/choose
[prestashop]: https://www.prestashop-project.org/
[contribution-guidelines]: https://devdocs.prestashop.com/1.7/contribute/contribution-guidelines/project-modules/
[AFL-3.0]: https://opensource.org/licenses/AFL-3.0
