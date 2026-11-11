<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
declare(strict_types=1);

namespace Axelweb\AwGeodisOrderLink\Controller;

use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AJAX endpoint called from the admin order page ("Update status" button)
 * and from the Geodis shipment page ("Transmit" button, via sendBeacon):
 * sets the order status to "Shipped" (PS_OS_SHIPPING).
 */
final class AwGeodisOrderLinkController extends PrestaShopAdminController
{
    public function __construct(
        private readonly ConfigurationInterface $configuration,
    ) {
    }

    #[AdminSecurity(
        "is_granted('update', request.get('_legacy_controller'))",
        message: 'You do not have permission to update this.',
        jsonResponse: true,
    )]
    public function ajaxUpdateStateOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $orderId = is_array($data) ? (int) ($data['orderId'] ?? 0) : 0;

        if ($orderId <= 0) {
            return $this->jsonError(
                $this->trans('Missing order ID', [], 'Modules.Awgeodisorderlink.Admin'),
                Response::HTTP_BAD_REQUEST
            );
        }

        $order = new \Order($orderId);
        if (!\Validate::isLoadedObject($order)) {
            return $this->jsonError(
                $this->trans('Order not found', [], 'Modules.Awgeodisorderlink.Admin'),
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $shippedStateId = (int) $this->configuration->get('PS_OS_SHIPPING');

            // Transmitting a shipment twice, or clicking the button twice,
            // must not add the same status to the order history again.
            if ((int) $order->getCurrentState() !== $shippedStateId) {
                $this->updateOrderStatus($order, $shippedStateId);
            }
        } catch (\Throwable $e) {
            \PrestaShopLogger::addLog(
                sprintf('awgeodisorderlink: status of order %d not updated: %s', $orderId, $e->getMessage()),
                3,
                null,
                'Order',
                $orderId,
                true
            );

            return $this->jsonError(
                $this->trans('Order status could not be updated', [], 'Modules.Awgeodisorderlink.Admin'),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Discard any output left by the order status hooks of other modules
        $this->cleanOutputBuffer();

        return new JsonResponse([
            'success' => true,
            'message' => $this->trans('Order status updated successfully', [], 'Modules.Awgeodisorderlink.Admin'),
        ]);
    }

    private function updateOrderStatus(\Order $order, int $orderStateId): void
    {
        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = (int) $order->id;
        $orderHistory->id_employee = $this->getEmployeeContext()->getEmployee()?->getId() ?? 0;
        $orderHistory->changeIdOrderState($orderStateId, $order);

        if (!$orderHistory->add()) {
            throw new \PrestaShopException('Failed to add order history');
        }
    }

    private function jsonError(string $message, int $statusCode): JsonResponse
    {
        $this->cleanOutputBuffer();

        return new JsonResponse(['success' => false, 'message' => $message], $statusCode);
    }

    private function cleanOutputBuffer(): void
    {
        if (ob_get_level()) {
            ob_clean();
        }
    }
}
