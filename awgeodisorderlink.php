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
        $this->description = $this->trans('Provide a link to the Geodis order from the back office order page', [], 'Modules.Awgeodisorderlink.Admin');
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}
