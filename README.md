<p align="center"><a href="https://slick-pay.com" target="_blank"><img src="https://azimutbscenter.com/logos/slick-pay.png" width="380" height="auto" alt="Slick-Pay Logo"></a></p>

## Description

[WordPress](https://wordpress.org) plugin for [Slick-Pay](https://slick-pay.com) Quick Transfer API implementation, it provides a new payment gateway to the [WooCommerce](https://wordpress.org/plugins/woocommerce) payment methods.

* [Prerequisites](#prerequisites)
* [Installation](#installation)
* [Configuration](#configuration)
* [How to use?](#how-to-use)
* [More help](#morehelp)

## Prerequisites

   - PHP 7.4 or above ;
   - [curl](https://secure.php.net/manual/en/book.curl.php) extension must be enabled ;
   - [WordPress](https://wordpress.org) 5.0 or above.
   - [WooCommerce](https://wordpress.org/plugins/woocommerce) 6.0 or above.

## Installation

1. First download this repository content from **Code** >> **Download ZIP**
2. Unzip and Copy the **slick-pay-quick-transfer** folder inside the **wp-content**/**plugins** website directory.
3. Now, from your website dashboard, go to **Plugins** and click on **Install** under the plugin named **Slick-Pay.com Quick Transfer**, and voilà !!

## Configuration

After installed the plugin, it provides you a new payment method for your e-commerce website.

To enable this bright new payment method, you have to go from your website dashboard to **WooCommerce*** >> **Settings**, then click on the **Payments** tab to see the whole list of the payment methods available on your website.

From there, switch-on the method named **Slick-Pay.com Quick Transfer** and click **Save changes**.

Finally, don't forget to setup your informations within the plugin settings by clicking on the **Manage** button.

Each of options is explained below :

### RIB

It will define the merchant bank account ID, it should be a string with the exact size of 20 characters.

### First Name

Used as the merchant first name, it should be a string with minimum length of 2 characters.

### Last Name

Used as the merchant last name, it should be a string with minimum length of 2 characters.

### Address

Used as the merchant address, it should be a string with minimum length of 5 characters.

## How to use?

Nothing else to do, now from your front-office website, you will be able to select the **Slick-Pay.com Quick Transfer** as a payment method for your orders.

## More help
   * [Slick-Pay website](https://slick-pay.com)
   * [Reporting Issues / Feature Requests](https://github.com/Slick-Pay-Algeria/quick-transfer/issues)