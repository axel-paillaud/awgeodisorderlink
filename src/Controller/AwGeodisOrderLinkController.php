<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2025 Axelweb
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Axelweb\AwGeodisOrderLink\Controller;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AwGeodisOrderLinkController extends FrameworkBundleAdminController
{
    public function ajaxUpdateStateOrder(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $orderId = (int) $data['orderId'] ?? 0;

        if (!$orderId) {
            return new JsonResponse(['success' => false, 'message' => 'ID manquant'], 400);
        }

        try {
            $order = new \Order((int) $orderId);
            if (!\Validate::isLoadedObject($order)) {
                throw new \PrestaShopException("Invalid Order ID");
            }

            $this->updateOrderStatus($order);

            return new JsonResponse([
                "success" => true,
                "message" => $this->trans(
                    "Order status updated successfully",
                    "Modules.Awgeodisorderlink.Admin"
                ),
            ]);
        } catch (\PrestaShopException $e) {
            return new JsonResponse([
                "success" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

   /*
    * @param object $order Order
    * @return void
    */
   private function updateOrderStatus(object $order): void
   {
       $orderHistory = new \OrderHistory();
       $orderHistory->id_order = (int) $order->id;

       $orderHistory->changeIdOrderState(_PS_OS_PREPARATION_, $order);

       // Save
       if (!$orderHistory->add()) {
           throw new \PrestaShopException("Failed to add order history");
       }
   }
}
