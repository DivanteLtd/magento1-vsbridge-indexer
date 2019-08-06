## [Unreleased]

### Added
- Allow to use Elasticsearch host without specific port - @cewald (#25)
- Update `vsf_tools.php` - @cewald (#26)
  - Add `store` option to `reindex` action
  - Make `store` optional for `full_reindex` action to fully reindex all store views

### Fixed
- Fixed CMS Block and Page identifier to avoid `-` tokenization - @mtarld (#22)
- `stock_status` is now taken into consideration for the value of the synchronized value of `is_in_stock` for configurable products, this makes product listings in Vue Storefront better reflect the ones in native Magento - [@indiebytes](https://github.com/indiebytes) [(#24)](https://github.com/DivanteLtd/magento1-vsbridge-indexer/pull/24)
- Always wrong scheme after enabling HTTPS in backend - @cewald (#25)

## [1.0.0] (2019.04.09)
First version
