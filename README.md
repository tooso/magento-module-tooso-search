# Tooso Search for Magento

[Tooso](http://tooso.ai) is a cloud-based, multi-language search tool for e-commerce.

This extension replaces the default search of Magento with a typo-tolerant, fast & relevant search experience backed by [Tooso](http://tooso.ai/Default.aspx).

## Description

This extension replaces the default Magento search engine with one based on Tooso API.
It provide the following features:

* Fulltext search for catalog products (currently advanced search is not supported)
* Scheduled indexing of catalog products
* Automatic typo correction
* Search keywords suggest

## Requirements

The only requirement for the module is the default Magento cronjob execution correctly configured.
Something like the following is a good configuration:

```
*/5 * * * * php -f /absolute/path/to/magento/cron.sh > /dev/null 2>&1
```

This will run the Magento jobs schedule and executions every 5 minutes. *Note:* the Tooso indexing flow start every 15 minutes.

Here is some additional info on how to add cron jobs with Cpanel and Plesk, which are some of the most popular web panels:

* [CPanel - Add a cron job](https://documentation.cpanel.net/display/ALD/Cron+Jobs#CronJobs-Addacronjob)
* [Plesk - How do I set up cron jobs](https://www.interspire.com/support/kb/questions/382/How+do+I+set+up+CRON+on+my+server+using+Plesk%3F)

If you can't access your server configuration, please ask your hosting provider to configure crontab for you.

## External Dependencies

[Mobile Detect](http://mobiledetect.net/)

It is placed under `lib/Bitbull/Mobile` directory, the main class file is renamed from *Mobile_Detect* to *Detect.php* 
and class name is renamed to *Bitbull_Mobile_Detect*. 
This prevent compatibility issues by other modules. 
Mobile Detect class is globally reachable as `Bitbull_Mobile_Detect`. 

## Installation Instructions

You can install the extension with following methods:

### Copying files manually

Copy all files and directories recursively from `src` directory into your document root.

### Using Composer

If you use [Composer](https://getcomposer.org) for manage your module dependencies, you can install the module adding this repository to your `composer.json` file:

```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/bitbull-team/Bitbull_Tooso"
        }
    ]
}
```

Then you can add the module dependency:

```
{
    "require": {
        "magento-hackathon/magento-composer-installer": "*",
        "bitbull/magento-tooso-search": "1.0.*"
    }
}
```

Finally, you can launch the command `composer update` inside your project root directory.

Regardless the method you choose, after installation you need to clear the cache and logout from the admin panel and then login again.

## Customize reindex flow

By default, the catalog reindex process start every 15 minutes. This is done through the standard Magento cron framework (more info [here](http://www.webguys.de/magento-1/tuerchen-08-magento-cron-demystified)).
You can customize the frequency of reindex schedule editing the file `app/code/community/Bitbull/Tooso/etc/config.xml`:

```
    <crontab>
        <jobs>
            <tooso_reindex>
                <schedule>
                    <cron_expr>*/15 * * * *</cron_expr>
                </schedule>
                <run>
                    <model>tooso/observer::rebuildIndex</model>
                </run>
            </tooso_reindex>
        </jobs>
    </crontab>
```

you can change the value of node `<cron_expr>` using any standard [cron expression](https://en.wikipedia.org/wiki/Cron#CRON_expression).

## Module Configuration

### Request your API KEY
Send an email to info@tooso.ai to request your APIKEY

### Set your API KEY: 
1. Under __API Configuration__
* Insert your API key into __API key__ field
* Insert __http://v{apiVersionWithNoDot}.api.tooso.ai/__ into __API base url__ field. The current supported version is 1, so the placeholder {apiVersionWithNoDot} should be replaced by 1.
* __Send report__: __YES__ to send a report to Tooso when an API error occourred	
* __Force logging__:  __YES__ to force logging to {{base_dir}}/var/log/tooso_search.log even when logging is disabled
* __Debug mode__:  __Yes__ to enable more verbose logging for debug purpose
2. Save configuration
* If you are in the Early Adopter phase, it's highly advisable to leave enabled __Send report__ and __Force logging__ configurations under __API Configuration__. This will give to the Tooso support team more information for help you troubleshoot in case of errors.
**Please note:** to be able to send report to Tooso, your Magento installation need to be able to send email.
![API configuration](./docs/apiconfiguration.png)

### Integrate your catalog
1. Access the extension configuration under System -> Configuration -> Tooso Search Engine.
2. Under __Activate Tooso__ set the parameters as follow:
* Enable Search: __NO__
* Enable Indexing: __YES__
* Enable Tracking: __NO__
3. Save configuration
![Integrate your catalog](./docs/indexing.png)

### Activate your Search Bar and your tracking
Once your catalog has been integrated with Tooso, you will get a notification from a member of Tooso’s team and you can enable the search and the tracking
1. Access the extension configuration under System -> Configuration -> Tooso Search Engine.
2. Under __Activate Tooso__ set the parameters as follow:
* Enable Search: __YES__
* Enable Indexing: __YES__
* Enable Tracking: __YES__
3. Save configuration
![Seaching and Tracking](./docs/searchingtracking.png)

**IMPORTANT**: For any question about our early adopters program, reach out anytime to our CCO mattia.pavoni@tooso.ai; for technical enquiries, ping directly our CTO jacopo.tagliabue@tooso.ai. 

