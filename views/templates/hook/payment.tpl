{**
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
 *}

<p class="payment_module">
	<a href="{$link->getModuleLink('pixpayment', 'payment')|escape:'html'}" title="{l s='Pague usando PIX' d='Modules.PixPayment.Shop'}">
		<img src="{$this_path_bw}logo.png" alt="{l s='Pague usando PIX' d='Modules.PixPayment.Shop'}" width="86" height="49"/>
		{l s='Pague usando PIX' d='Modules.PixPayment.Shop'}&nbsp;<span>{l s='' d='Modules.PixPayment.Shop'}</span>
	</a>
</p>
