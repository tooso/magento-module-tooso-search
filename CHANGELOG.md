# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.0.4]

- Added method Bitbull_Tooso_Model_Resource_CatalogSearch_Fulltext_Collection::getFoundIds for compatibility with Magento 1.9.3.1

## [1.0.3]

- Fixed regression introduced with 1.0.2: error raised if a sku is not available in Magento but is returned by Tooso API

## [1.0.2]

- Fixed relevance sorting

## [1.0.1]

- Removed debug email address

## [1.0.0]

- First stable release
- Fulltext search for catalog products (currently advanced search is not supported)
- Scheduled indexing of catalog products
- Automatic typo correction
- Search keywords suggest