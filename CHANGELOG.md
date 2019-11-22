## [Unreleased]

### Added
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
