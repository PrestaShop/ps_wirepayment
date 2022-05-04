<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_Wirepayment extends PaymentModule
{
    const FLAG_DISPLAY_PAYMENT_INVITE = 'BANK_WIRE_PAYMENT_INVITE';

    protected $_html = '';
    protected $_postErrors = [];

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;
    /**
     * @var int
     */
    public $is_eu_compatible;
    /**
     * @var false|int
     */
    public $reservation_days;

    public function __construct()
    {
        $this->name = 'ps_wirepayment';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.3';
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
        $this->author = 'PrestaShop';
        $this->controllers = ['payment', 'validation'];
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(['BANK_WIRE_DETAILS', 'BANK_WIRE_OWNER', 'BANK_WIRE_ADDRESS', 'BANK_WIRE_RESERVATION_DAYS']);
        if (!empty($config['BANK_WIRE_OWNER'])) {
            $this->owner = $config['BANK_WIRE_OWNER'];
        }
        if (!empty($config['BANK_WIRE_DETAILS'])) {
            $this->details = $config['BANK_WIRE_DETAILS'];
        }
        if (!empty($config['BANK_WIRE_ADDRESS'])) {
            $this->address = $config['BANK_WIRE_ADDRESS'];
        }
        if (!empty($config['BANK_WIRE_RESERVATION_DAYS'])) {
            $this->reservation_days = $config['BANK_WIRE_RESERVATION_DAYS'];
        }

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Wire payment', [], 'Modules.Wirepayment.Admin');
        $this->description = $this->trans('Accept wire payments by displaying your account details during the checkout.', [], 'Modules.Wirepayment.Admin');
        $this->confirmUninstall = $this->trans('Are you sure about removing these details?', [], 'Modules.Wirepayment.Admin');
        if ((!isset($this->owner) || !isset($this->details) || !isset($this->address)) && $this->active) {
            $this->warning = $this->trans('Account owner and account details must be configured before using this module.', [], 'Modules.Wirepayment.Admin');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id)) && $this->active) {
            $this->warning = $this->trans('No currency has been set for this module.', [], 'Modules.Wirepayment.Admin');
        }

        $this->extra_mail_vars = [
            '{bankwire_owner}' => $this->owner,
            '{bankwire_details}' => nl2br($this->details ?: ''),
            '{bankwire_address}' => nl2br($this->address ?: ''),
        ];
    }

    public function install()
    {
        Configuration::updateValue(self::FLAG_DISPLAY_PAYMENT_INVITE, true);
        if (!parent::install()
            || !$this->registerHook('displayPaymentReturn')
            || !$this->registerHook('paymentOptions')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('BANK_WIRE_CUSTOM_TEXT')
                || !Configuration::deleteByName('BANK_WIRE_DETAILS')
                || !Configuration::deleteByName('BANK_WIRE_OWNER')
                || !Configuration::deleteByName('BANK_WIRE_ADDRESS')
                || !Configuration::deleteByName('BANK_WIRE_RESERVATION_DAYS')
                || !Configuration::deleteByName(self::FLAG_DISPLAY_PAYMENT_INVITE)
                || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue(self::FLAG_DISPLAY_PAYMENT_INVITE,
                Tools::getValue(self::FLAG_DISPLAY_PAYMENT_INVITE));

            if (!Tools::getValue('BANK_WIRE_DETAILS')) {
                $this->_postErrors[] = $this->trans('Account details are required.', [], 'Modules.Wirepayment.Admin');
            } elseif (!Tools::getValue('BANK_WIRE_OWNER')) {
                $this->_postErrors[] = $this->trans('Account owner is required.', [], 'Modules.Wirepayment.Admin');
            }
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('BANK_WIRE_DETAILS', Tools::getValue('BANK_WIRE_DETAILS'));
            Configuration::updateValue('BANK_WIRE_OWNER', Tools::getValue('BANK_WIRE_OWNER'));
            Configuration::updateValue('BANK_WIRE_ADDRESS', Tools::getValue('BANK_WIRE_ADDRESS'));

            $custom_text = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                if (Tools::getIsset('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang'])) {
                    $custom_text[$lang['id_lang']] = Tools::getValue('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang']);
                }
            }
            Configuration::updateValue('BANK_WIRE_RESERVATION_DAYS', Tools::getValue('BANK_WIRE_RESERVATION_DAYS'));
            Configuration::updateValue('BANK_WIRE_CUSTOM_TEXT', $custom_text);
        }
        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
    }

    protected function _displayBankWire()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_displayBankWire();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return [];
        }

        if (!$this->checkCurrency($params['cart'])) {
            return [];
        }

        $this->smarty->assign(
            $this->getTemplateVarInfos()
        );

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Pay by bank wire', [], 'Modules.Wirepayment.Shop'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
                ->setAdditionalInformation($this->fetch('module:ps_wirepayment/views/templates/hook/ps_wirepayment_intro.tpl'));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;
    }

    public function hookDisplayPaymentReturn($params)
    {
        if (!$this->active || !Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)) {
            return;
        }

        $bankwireOwner = $this->owner;
        if (!$bankwireOwner) {
            $bankwireOwner = '___________';
        }

        $bankwireDetails = Tools::nl2br($this->details);
        if (!$bankwireDetails) {
            $bankwireDetails = '___________';
        }

        $bankwireAddress = Tools::nl2br($this->address);
        if (!$bankwireAddress) {
            $bankwireAddress = '___________';
        }

        $totalToPaid = $params['order']->getOrdersTotalPaid() - $params['order']->getTotalPaid();
        $this->smarty->assign([
            'shop_name' => $this->context->shop->name,
            'total' => $this->context->getCurrentLocale()->formatPrice(
                $totalToPaid,
                (new Currency($params['order']->id_currency))->iso_code
            ),
            'bankwireDetails' => $bankwireDetails,
            'bankwireAddress' => $bankwireAddress,
            'bankwireOwner' => $bankwireOwner,
            'status' => 'ok',
            'reference' => $params['order']->reference,
            'contact_url' => $this->context->link->getPageLink('contact', true),
        ]);

        return $this->fetch('module:ps_wirepayment/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Account details', [], 'Modules.Wirepayment.Admin'),
                    'icon' => 'icon-envelope',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Account owner', [], 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_OWNER',
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Account details', [], 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_DETAILS',
                        'desc' => $this->trans('Such as bank branch, IBAN number, BIC, etc.', [], 'Modules.Wirepayment.Admin'),
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Bank address', [], 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_ADDRESS',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
        $fields_form_customization = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Customization', [], 'Modules.Wirepayment.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Reservation period', [], 'Modules.Wirepayment.Admin'),
                        'desc' => $this->trans('Number of days the items remain reserved', [], 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_RESERVATION_DAYS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Information to the customer', [], 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_CUSTOM_TEXT',
                        'desc' => $this->trans('Information on the bank transfer (processing time, starting of the shipping...)', [], 'Modules.Wirepayment.Admin'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Display the invitation to pay in the order confirmation page', [], 'Modules.Wirepayment.Admin'),
                        'name' => self::FLAG_DISPLAY_PAYMENT_INVITE,
                        'is_bool' => true,
                        'hint' => $this->trans('Your country\'s legislation may require you to send the invitation to pay by email only. Disabling the option will hide the invitation on the confirmation page.', [], 'Modules.Wirepayment.Admin'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Yes', [], 'Admin.Global'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('No', [], 'Admin.Global'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure='
            . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form, $fields_form_customization]);
    }

    public function getConfigFieldsValues()
    {
        $custom_text = [];
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $custom_text[$lang['id_lang']] = Tools::getValue(
                'BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang'],
                Configuration::get('BANK_WIRE_CUSTOM_TEXT', $lang['id_lang'])
            );
        }

        return [
            'BANK_WIRE_DETAILS' => Tools::getValue('BANK_WIRE_DETAILS', $this->details),
            'BANK_WIRE_OWNER' => Tools::getValue('BANK_WIRE_OWNER', $this->owner),
            'BANK_WIRE_ADDRESS' => Tools::getValue('BANK_WIRE_ADDRESS', $this->address),
            'BANK_WIRE_RESERVATION_DAYS' => Tools::getValue('BANK_WIRE_RESERVATION_DAYS', $this->reservation_days),
            'BANK_WIRE_CUSTOM_TEXT' => $custom_text,
            self::FLAG_DISPLAY_PAYMENT_INVITE => Tools::getValue(
                self::FLAG_DISPLAY_PAYMENT_INVITE,
                Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)
            ),
        ];
    }

    public function getTemplateVarInfos()
    {
        $cart = $this->context->cart;
        $total = sprintf(
            $this->trans('%1$s (tax incl.)', [], 'Modules.Wirepayment.Shop'),
            $this->context->getCurrentLocale()->formatPrice($cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
        );

        $bankwireOwner = $this->owner;
        if (!$bankwireOwner) {
            $bankwireOwner = '___________';
        }

        $bankwireDetails = Tools::nl2br($this->details);
        if (!$bankwireDetails) {
            $bankwireDetails = '___________';
        }

        $bankwireAddress = Tools::nl2br($this->address);
        if (!$bankwireAddress) {
            $bankwireAddress = '___________';
        }

        $bankwireReservationDays = $this->reservation_days;
        if (false === $bankwireReservationDays) {
            $bankwireReservationDays = 7;
        }

        $bankwireCustomText = Tools::nl2br(Configuration::get('BANK_WIRE_CUSTOM_TEXT', $this->context->language->id));
        if (empty($bankwireCustomText)) {
            $bankwireCustomText = '';
        }

        return [
            'total' => $total,
            'bankwireDetails' => $bankwireDetails,
            'bankwireAddress' => $bankwireAddress,
            'bankwireOwner' => $bankwireOwner,
            'bankwireReservationDays' => (int) $bankwireReservationDays,
            'bankwireCustomText' => $bankwireCustomText,
        ];
    }
}
