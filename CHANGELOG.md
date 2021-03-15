## [1.5.2](https://github.com/ambimax/magento-module-vsf-indexer/compare/1.5.1...1.5.2) (2021-03-15)


### Bug Fixes

* Fix original image sizes ([8791c93](https://github.com/ambimax/magento-module-vsf-indexer/commit/8791c93bac70236075b7006dc9e99af08274bff4))

## [1.5.1](https://github.com/ambimax/magento-module-vsf-indexer/compare/1.5.0...1.5.1) (2021-03-15)


### Bug Fixes

* **DELPHIN-1481:** Fix thumbnail image in products ([55cc5dc](https://github.com/ambimax/magento-module-vsf-indexer/commit/55cc5dcf609315a290750fd3d595d79076f23a39))

# [1.5.0](https://github.com/ambimax/magento-module-vsf-indexer/compare/1.4.0...1.5.0) (2021-03-12)


### Features

* **DELPHIN-1481:** Apply LazyCatalog to all images ([792372a](https://github.com/ambimax/magento-module-vsf-indexer/commit/792372ac7f00e205dc61f3993ce5e8577a0df7b0))

# [1.4.0](https://github.com/ambimax/magento-module-vsf-indexer/compare/1.3.2...1.4.0) (2021-03-11)


### Features

* **DELPHIN-1480:** Add `salespromotion` index action ([941abab](https://github.com/ambimax/magento-module-vsf-indexer/commit/941ababe7a358be0a91c13d6063d0e6ecac5610c))
* **DELPHIN-1480:** Products in the index now contain a new `salespromotion_sold_amount` property used by the salespromotion ([2a64de6](https://github.com/ambimax/magento-module-vsf-indexer/commit/2a64de6893a9c163c40856b3b6a5d5dfda338221))

# 1.0.0 (2021-03-11)


### Bug Fixes

* The install script doesn't crash anymore when run twice ([4f81b91](https://github.com/ambimax/magento-module-vsf-indexer/commit/4f81b91a22c44f6f04a339e1d9181344ed0e108c))

## [Unreleased]

### Added
- Add option label for configurable_options.values
- Add option to export "attributes_metadata" for products. Only user defined attributes are exported by default.
Sample: `docs/sample/attribute-metadata.json`
More information here:  https://github.com/DivanteLtd/vue-storefront/pull/4001
- Support for aliases

## [1.2.0] (2019.10.24)

### Fixes 
- Export all tax rates ([#29](https://github.com/DivanteLtd/magento1-vsbridge-indexer/issues/29))
- Wrong product-children website configurable prices when more than one website-price exists - @cewald (#46)

### Added
- Export ratings for reviews

## [1.1.0] (2019.08.30)

### Added
- Add option to export reviews to ES
- Add option to export cms pages to ES
- Set mapping for `url_path` to keyword
- Allow to use Elasticsearch host without specific port - @cewald (#25)
- Update `vsf_tools.php` - @cewald (#26)
  - Add `store` option to `reindex` action
  - Make `store` optional for `full_reindex` action to fully reindex all store views

### Fixed
- Fixed CMS Block and Page identifier to avoid `-` tokenization - @mtarld (#22)
- Set is_in_stock/stock_status value - base on Stock Avaibility of configurable product and children stock status. `stock_status` value is calculated in inventory indexer and might be out of date. #34
- Always wrong scheme after enabling HTTPS in backend - @cewald (#25)

## [1.0.0] (2019.04.09)
First version
