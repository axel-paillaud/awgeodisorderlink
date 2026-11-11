<?php
/**
 * @author    Axelweb <contact@axelweb.fr>
 * @copyright 2026 Axelweb
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AwGeodisOrderLink extends Module
{
    private const GEODIS_MODULE_NAME = 'geodisofficiel';

    private const UPDATE_STATE_ROUTE = 'axelweb_awgeodisorderlink_ajax_update_state_order';

    public function __construct()
    {
        $this->name = 'awgeodisorderlink';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'Axel Paillaud';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => '9.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Geodis Order Link', [], 'Modules.Awgeodisorderlink.Admin');
        $this->description = $this->trans('Provide a link to the Geodis order from the admin order page', [], 'Modules.Awgeodisorderlink.Admin');
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('displayAdminOrderMain')
            && $this->registerHook('actionAdminControllerSetMedia');
    }

    /**
     * Link to the Geodis shipment and "Update status" button, on the admin
     * order page of orders shipped with a Geodis carrier.
     */
    public function hookDisplayAdminOrderMain(array $params): string
    {
        try {
            $orderId = (int) ($params['id_order'] ?? 0);
            $order = new Order($orderId);
            if (!Validate::isLoadedObject($order)) {
                return '';
            }

            $carrier = new Carrier((int) $order->id_carrier);
            if ($carrier->external_module_name !== self::GEODIS_MODULE_NAME) {
                return '';
            }

            $twig = $this->getTwig();
            if (null === $twig) {
                return '';
            }

            return $twig->render('@Modules/awgeodisorderlink/views/templates/admin/awgeodisorderlink.html.twig', [
                'geodis_url' => $this->context->link->getAdminLink('AdminGeodisShipment', true, [], ['id_order' => $orderId]),
                'logo_url' => $this->getPathUri() . 'views/img/logo-small.png',
                'order_id' => $orderId,
            ]);
        } catch (Throwable $e) {
            $this->logError('displayAdminOrderMain', $e);

            return '';
        }
    }

    /**
     * Scripts of the admin order page and of the Geodis shipment page.
     */
    public function hookActionAdminControllerSetMedia(): void
    {
        try {
            $controllerName = Tools::getValue('controller');
            if ($controllerName !== 'AdminOrders' && $controllerName !== 'AdminGeodisShipment') {
                return;
            }

            Media::addJsDef([
                'awGeodisOrderLinkUpdateStateOrderAjaxControllerUri' => $this->get('router')->generate(self::UPDATE_STATE_ROUTE),
            ]);

            if ($controllerName === 'AdminOrders') {
                // The order id is read from the button (displayAdminOrderMain)
                $this->context->controller->addJS($this->getPathUri() . 'views/js/awgeodisorderlink.js');

                return;
            }

            Media::addJsDef([
                'awGeodisOrderLinkOrderId' => (int) Tools::getValue('id_order'),
            ]);
            $this->context->controller->addJS($this->getPathUri() . 'views/js/awgeodisshipment.js');
        } catch (Throwable $e) {
            $this->logError('actionAdminControllerSetMedia', $e);
        }
    }

    private function logError(string $hookName, Throwable $e): void
    {
        PrestaShopLogger::addLog(
            sprintf('awgeodisorderlink: hook %s failed: %s', $hookName, $e->getMessage()),
            3,
            null,
            'Module',
            (int) $this->id,
            true
        );
    }
}
