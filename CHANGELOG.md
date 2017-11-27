# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
- Added more info on exported simple product 

## [3.1.0]
- Added backend module's info and log tools

## [3.0.0]
- Added API v2 compatibility
- Catch current_layer null error
- Substituted "+" with " " in query string
- Soft error when 'before_body_end' block not exist
- Refactory search id using cookies

## [2.0.1]
- Get base url to point correct controller endpoint

## [2.0.0]
- Disable email report on empty query
- Increase API response timeout
- Improve indexer performance
- Removed autocomplete num_of_results in frontend
- Added backend attributes options
- Added backend option to disable tracking features
- Added isMobile tracking
- Fix pass parentId params when typo correction is disabled
- Fix parentId params in "Search instead for" link
- Fix product rank tracking
- Fix suggestions max_results
- Fix tracking pixel compatibility with FPC

## [1.3.0]

- Fixed fallback search in case of errors

## [1.2.0]

- Added configuration to change API base url
- Fixed compatibility with EE moving generation of breadcrumbs, title and message with fixed search string to an observer

## [1.1.0]

- Use of [`CURLFile`](http://php.net/manual/en/class.curlfile.php) for indexing file upload if available, fallback on "@" prefix
- Correctly handle the case Tooso search is disabled, and forward to native Magento (fix on 1.0.5 not working properly)
- Inject logger ad dependency in the Client, and the helper logger now implement the interface from library, so we can use the Magento logger in the client code
- Consider "position" as value for attribute that apply Tooso search result sorting
- Moved to https endpoint with self-signed certificate

## [1.0.6]

- Set `CURLOPT_SAFE_UPLOAD` to true for enable catalog upload with "@" prefix, as from PHP 5.6 is set to false by default

## [1.0.5]

- Fix for results page error when Tooso search is disabled from admin

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
