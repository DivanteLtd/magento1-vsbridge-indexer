

# Divante VueStorefrontIndexer Extension for Vue Storefront
![Branch stable](https://img.shields.io/badge/stable%20branch-master-blue.svg)
![Branch Develop](https://img.shields.io/badge/dev%20branch-develop-blue.svg)
<a href="https://join.slack.com/t/vuestorefront/shared_invite/enQtMzA4MTM2NTE5NjM2LTI1M2RmOWIyOTk0MzFlMDU3YzJlYzcyYzNiNjUyZWJiMTZjZjc3MjRlYmE5ZWQ1YWRhNTQyM2ZjN2ZkMzZlNTg">![Branch Develop](https://img.shields.io/badge/community%20chat-slack-FF1493.svg)</a>

This projects is a native, Magento1 data indexer for [Vue Storefront - first Progressive Web App for e-Commerce](https://github.com/DivanteLtd/vue-storefront). It fills the ElasticSearch data index with all the products, categories and static information required by Vue Storefront to work.

Vue Storefront is a standalone PWA storefront for your eCommerce, possible to connect with any eCommerce backend (eg. Magento, Pimcore, Prestashop or Shopware) through the API.

 ## Video demo
 [![See how it works!](https://github.com/DivanteLtd/vue-storefront/raw/master/docs/.vuepress/public/Fil-Rakowski-VS-Demo-Youtube.png)](https://www.youtube.com/watch?v=L4K-mq9JoaQ)
Sign up for a demo at https://vuestorefront.io/ (Vue Storefront integrated with Pimcore OR Magento2).


## Facts
- version: beta 1.0.0
- extension key: Divante_VueStorefrontIndexer

## Features
This module is in beta, however we've used it in some production sites already. Please do feel free to test it and bring Your feedback + Pull Requests :)

Full synchronization: products, categories, attribute, tax rules, cms blocks.
Synchronization in real time for: products, categories, attributes, cms blocks.

Module listen on following Magento1 events:
- product save (in backend panel),
- product deletion (in backend panel)
- mass product update,
- category save,
- category deletion,
- attribute save,
- attribute deletion (after attribute is removed full product synchronization will be fired),
- cms block save,
- cms block deletion.

## Requirements
- PHP >= 5.5.0
- Magento 1.9.*
- ElasticSearch 5.*
- ...

## Compatibility
- Magento >= 1.9
- Vue Storefront >= 1.7

## Installation Instructions

### Setup ElasticSearch Connection

System --> Configuration -> VueStorefront -> Elasticsearch Client

![](docs/images/elastic-search-config.png)

### Change Indices Settings
System --> Configuration -> VueStorefront -> Indices Settings

- adjust indexing batch size to your data
- setup index name prefix. 

### Run full synchronization for chosen store views:

```
cd [magento root dir]/shell
php -f vsf_tools.php --action full_reindex --store STORE_ID
```

### Setup Cron job to update data in ElasticSearch in real time (for products, categories, attributes, cms blocks)

e.g.

```
*/5 * * * * cd [full path to magento directory]/shell && /usr/bin/flock -n /tmp/vsf_index.lock  /usr/bin/php vsf_tools.php --action reindex 
```

## Support

[Join our Slack channel](http://slack.vuestorefront.io)


## Licence

MIT


Copyright
---------
(c) 2018 Divante
