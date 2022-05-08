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

class PixPayment extends PaymentModule
{
    const FLAG_DISPLAY_PAYMENT_INVITE = 'PIX_PAYMENT_INVITE';

    protected $_html = '';
    protected $_postErrors = [];

    public $key;
    public $owner;
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
        $this->name = 'pixpayment';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.1';
        $this->ps_versions_compliancy = ['min' => '1.7.6.0', 'max' => _PS_VERSION_];
        $this->author = 'PrestaShop & Matheus Bach';
        $this->controllers = ['payment', 'validation'];
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(['PIX_KEY', 'PIX_OWNER', 'PIX_RESERVATION_DAYS']);
        if (!empty($config['PIX_OWNER'])) {
            $this->owner = $config['PIX_OWNER'];
        }
        if (!empty($config['PIX_KEY'])) {
            $this->key = $config['PIX_KEY'];
        }
        if (!empty($config['PIX_RESERVATION_DAYS'])) {
            $this->reservation_days = $config['PIX_RESERVATION_DAYS'];
        }

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Pagamento PIX', [], 'Modules.PixPayment.Admin');
        $this->description = $this->trans('Aceite pagamentos PIX exibindo suas informações de pagamento durante o checkout.', [], 'Modules.PixPayment.Admin');
        $this->confirmUninstall = $this->trans('Tem certeza que quer remover?', [], 'Modules.PixPayment.Admin');
        if (!count(Currency::checkPaymentCurrencies($this->id)) && $this->active) {
            $this->warning = $this->trans('Nenhuma moeda definida para este módulo.', [], 'Modules.PixPayment.Admin');
        }

        $this->extra_mail_vars = [
            '{pix_owner}' => $this->owner,
            '{pix_key}' => nl2br($this->key),
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
        if (!Configuration::deleteByName('PIX_CUSTOM_TEXT')
                || !Configuration::deleteByName('PIX_KEY')
                || !Configuration::deleteByName('PIX_OWNER')
                || !Configuration::deleteByName('PIX_RESERVATION_DAYS')
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

            if (!Tools::getValue('PIX_KEY')) {
                $this->_postErrors[] = $this->trans('Chave PIX é obrigatória', [], 'Modules.PixPayment.Admin');
            } elseif (!Tools::getValue('PIX_OWNER')) {
                $this->_postErrors[] = $this->trans('Dono de conta é obrigatório.', [], 'Modules.PixPayment.Admin');
            }
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('PIX_KEY', Tools::getValue('PIX_KEY'));
            Configuration::updateValue('PIX_OWNER', Tools::getValue('PIX_OWNER'));

            $custom_text = [];
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                if (Tools::getIsset('PIX_CUSTOM_TEXT_' . $lang['id_lang'])) {
                    $custom_text[$lang['id_lang']] = Tools::getValue('PIX_CUSTOM_TEXT_' . $lang['id_lang']);
                }
            }
            Configuration::updateValue('PIX_RESERVATION_DAYS', Tools::getValue('PIX_RESERVATION_DAYS'));
            Configuration::updateValue('PIX_CUSTOM_TEXT', $custom_text);
        }
        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
    }

    protected function _displayPixWire()
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

        $this->_html .= $this->_displayPixWire();
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
                ->setCallToActionText($this->trans('Pague usando PIX', [], 'Modules.PixPayment.Shop'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
                ->setAdditionalInformation($this->fetch('module:pixpayment/views/templates/hook/pixpayment_intro.tpl'));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active || !Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if (
            in_array(
                $state,
                [
                    Configuration::get('PS_OS_PIXWIRE'),
                    Configuration::get('PS_OS_OUTOFSTOCK'),
                    Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
                ]
        )) {
            $pixOwner = $this->owner;
            if (!$pixOwner) {
                $pixOwner = '___________';
            }

            $pixKey = Tools::nl2br($this->key);
            if (!$pixKey) {
                $pixKey = '___________';
            }

            $totalToPaid = $params['order']->getOrdersTotalPaid() - $params['order']->getTotalPaid();
            $this->smarty->assign([
                'shop_name' => $this->context->shop->name,
                'total' => $this->context->getCurrentLocale()->formatPrice(
                    $totalToPaid,
                    (new Currency($params['order']->id_currency))->iso_code
                ),
                'pixKey' => $pixKey,
                'pixOwner' => $pixOwner,
                'status' => 'ok',
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true),
            ]);
        } else {
            $this->smarty->assign(
                [
                    'status' => 'failed',
                    'contact_url' => $this->context->link->getPageLink('contact', true),
                ]
            );
        }

        return $this->fetch('module:pixpayment/views/templates/hook/payment_return.tpl');
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
                    'title' => $this->trans('Detalhes da Conta', [], 'Modules.PixPayment.Admin'),
                    'icon' => 'icon-envelope',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Dono da Conta', [], 'Modules.PixPayment.Admin'),
                        'name' => 'PIX_OWNER',
                        'desc' => $this->trans('Coloque aqui seu nome. Ex: Thomas Turbando', [], 'Modules.PixPayment.Admin'),
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Chave PIX', [], 'Modules.PixPayment.Admin'),
                        'name' => 'PIX_KEY',
                        'desc' => $this->trans('Sua Chave PIX aqui. Recomendo usar chave aleatória, mas pode ser CPF, CNPJ, telefone ou e-mail', [], 'Modules.PixPayment.Admin'),
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
                    'title' => $this->trans('Customização', [], 'Modules.PixPayment.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Período de reserva', [], 'Modules.PixPayment.Admin'),
                        'desc' => $this->trans('Numero de dias que os itens ficarão reservados', [], 'Modules.PixPayment.Admin'),
                        'name' => 'PIX_RESERVATION_DAYS',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Informações ao cliente', [], 'Modules.PixPayment.Admin'),
                        'name' => 'PIX_CUSTOM_TEXT',
                        'desc' => $this->trans('Informações para enviar ao cliente (Tempo de processamento do pagamento, tempo para começar a enviar o pacote, etc)', [], 'Modules.PixPayment.Admin'),
                        'lang' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Mostrar convite para pagar na página de confirmação de checkout', [], 'Modules.PixPayment.Admin'),
                        'name' => self::FLAG_DISPLAY_PAYMENT_INVITE,
                        'is_bool' => true,
                        'hint' => $this->trans('Lesgislação de alguns países pedem para enviar somente por e-mail. Se não for o caso, você pode ativar', [], 'Modules.PixPayment.Admin'),
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
                'PIX_CUSTOM_TEXT_' . $lang['id_lang'],
                Configuration::get('PIX_CUSTOM_TEXT', $lang['id_lang'])
            );
        }

        return [
            'PIX_KEY' => Tools::getValue('PIX_KEY', $this->key),
            'PIX_OWNER' => Tools::getValue('PIX_OWNER', $this->owner),
            'PIX_RESERVATION_DAYS' => Tools::getValue('PIX_RESERVATION_DAYS', $this->reservation_days),
            'PIX_CUSTOM_TEXT' => $custom_text,
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
            $this->trans('%1$s (tax incl.)', [], 'Modules.PixPayment.Shop'),
            $this->context->getCurrentLocale()->formatPrice($cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
        );

        $pixOwner = $this->owner;
        if (!$pixOwner) {
            $pixOwner = '___________';
        }

        $pixKey = Tools::nl2br($this->details);
        if (!$pixKey) {
            $pixKey = '___________';
        }

        $pixReservationDays = $this->reservation_days;
        if (false === $pixReservationDays) {
            $pixReservationDays = 7;
        }

        $pixCustomText = Tools::nl2br(Configuration::get('PIX_CUSTOM_TEXT', $this->context->language->id));
        if (empty($pixCustomText)) {
            $pixCustomText = '';
        }

        return [
            'total' => $total,
            'pixKey' => $pixKey,
            'pixOwner' => $pixOwner,
            'pixReservationDays' => (int) $pixReservationDays,
            'pixCustomText' => $pixCustomText,
        ];
    }
}
