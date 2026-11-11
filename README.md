# awgeodisorderlink

PrestaShop 9 module (9.0+ only since v2.0.0, PHP 8.1+) that links the admin
order page to the Geodis shipment of the order (`geodisofficiel` module).

The last PrestaShop 8 compatible release is tagged `v1.0.0`.

## Features

- **Admin order page** (`displayAdminOrderMain`), for orders shipped with a
  Geodis carrier (`carrier.external_module_name = 'geodisofficiel'`):
  - "Link to Geodis order" button, opens `AdminGeodisShipment` for the order;
  - "Update status" button, sets the order status to "Shipped" (`PS_OS_SHIPPING`).
- **Geodis shipment page** (`AdminGeodisShipment`): clicking "Transmit" also
  sets the order status to "Shipped" (request sent with `navigator.sendBeacon`,
  so it survives the page reload of the Geodis form).

The status is not added again if the order is already "Shipped". The order
history records the employee. No email is sent to the customer
(`OrderHistory::add()`, not `addWithemail()`).

## Requirements

- PrestaShop 9.0+
- PHP 8.1+
- `geodisofficiel` module (provides the Geodis carriers and `AdminGeodisShipment`)

## Installation

```sh
composer install --no-dev   # in the module directory, generates vendor/
```

Then install the module from Modules > Module Manager. There is no
configuration page.

Upgrade from 1.x: `php bin/console prestashop:module upgrade awgeodisorderlink`.

## Permissions

The AJAX endpoint requires the `update` permission on the Orders tab
(`AdminOrders`) and the admin `_token` of the URL.

## Structure

```
awgeodisorderlink.php                        Module class, hooks
config/routes.yml                            AJAX route (prefixed with /modules by PS 9)
config/admin/services.yml                    Admin container: controller
src/Controller/AwGeodisOrderLinkController   POST ajax-update-state-order
views/templates/admin/awgeodisorderlink.html.twig   Order page block
views/js/awgeodisorderlink.js                "Update status" button (order page)
views/js/awgeodisshipment.js                 "Transmit" button (Geodis shipment page)
translations/fr-FR/                          Domain Modules.Awgeodisorderlink.Admin
```
