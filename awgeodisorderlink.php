<?php
/*
 * Author  : Axel Paillaud - Axelweb
 * Contact : <contact@axelweb.fr>
 * Date    : 2025-06-16
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AwGeodisOrderLink extends Module
{
    private const GEODIS_MODULE_NAME = 'geodisofficiel';

    public function __construct()
    {
        $this->name = 'awgeodisorderlink';
        $this->version = '1.0.0';
        $this->author = 'Axel Paillaud';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '8.2.0',
            'max' => '9.99.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Geodis Order Link', [], 'Modules.Awgeodisorderlink.Admin');
        $this->description = $this->trans('Provide a link to the Geodis order from the admin order page', [], 'Modules.Awgeodisorderlink.Admin');
    }

    public function install()
    {
        return parent::install()
        && $this->registerHook('displayAdminOrderMain');
    }

    public function uninstall()
    {
        return parent::uninstall()
        && $this->unregisterHook('displayAdminOrderMain');
    }

    public function hookDisplayAdminOrderMain($params)
    {
        $order = new Order((int) $params['id_order']);
        $carrier = new Carrier((int) $order->id_carrier);

        $carrierData = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'carrier WHERE id_carrier = '.(int)$carrier->id);

        if (!$carrierData || $carrierData['external_module_name'] !== self::GEODIS_MODULE_NAME) {
            return '';
        }
        return $this->render($this->getModuleTemplatePath() . 'awgeodisorderlink.html.twig', [
            'hello' => $carrierData['external_module_name'],
        ]);
    }

    /**
     * Render a twig template.
     */
    private function render(string $template, array $params = []): string
    {
        /** @var Twig_Environment $twig */
        $twig = $this->get('twig');

        return $twig->render($template, $params);
    }

    /**
     * Get path to this module's template directory
     */
    private function getModuleTemplatePath(): string
    {
        return sprintf('@Modules/%s/views/templates/admin/', $this->name);
    }
}
