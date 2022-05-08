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

/**
 * @deprecated 1.5.0 This file is deprecated, use moduleFrontController instead
 */
include __DIR__ . '/../../config/config.inc.php';
include __DIR__ . '/../../header.php';
include __DIR__ . '/../../init.php';

$context = Context::getContext();
$cart = $context->cart;
/** @var PixPayment $pix */
$pix = Module::getInstanceByName('pixpayment');

if ($cart->id_customer == 0 or $cart->id_address_delivery == 0 or $cart->id_address_invoice == 0 or !$pix->active) {
    Tools::redirect('index.php?controller=order&step=1');
}

// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module) {
    if ($module['name'] == 'pixpayment') {
        $authorized = true;
        break;
    }
}
if (!$authorized) {
    exit($pix->getTranslator()->trans('Este método de pagamento não está disponível', [], 'Modules.PixPayment.Shop'));
}

$customer = new Customer((int) $cart->id_customer);

if (!Validate::isLoadedObject($customer)) {
    Tools::redirect('index.php?controller=order&step=1');
}

$currency = $context->currency;
$total = (float) ($cart->getOrderTotal(true, Cart::BOTH));

$pix->validateOrder($cart->id, (int) Configuration::get('PS_OS_PIX'), $total, $pix->displayName, null, [], (int) $currency->id, false, $customer->secure_key);

$order = new Order($pix->currentOrder);
Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $pix->id . '&id_order=' . $pix->currentOrder . '&key=' . $customer->secure_key);
