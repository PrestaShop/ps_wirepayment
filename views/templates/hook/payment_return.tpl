{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $status == 'ok'}
	<p>{l s='Your order on %s is complete.' sprintf=[$shop_name] mod='bankwire'}
	<p>{l s='Please send us a bank wire with:' mod='bankwire'}
		<dl>
			<dt>{l s='Amount' mod='bankwire'}</dt>
			<dd>{$total}</dd>
			<dt>{l s='Name of account owner' mod='bankwire'}</dt>
			<dd>{$bankwireOwner}</dd>
			<dt>{l s='Please include these details' mod='bankwire'}</dt>
			<dd>{$bankwireDetails}</dd>
			<dt>{l s='Bank name' mod='bankwire'}</dt>
			<dd>{$bankwireAddress}</dd>
			<dt>{l s='Order reference' mod='bankwire'}</dt>
			<dd>{$reference}</dd>
		</dl>
	</p>
	<p>{l s='We\'ve also sent you this information by e-mail.' mod='bankwire'}</p>
	<strong>{l s='Your order will be sent as soon as we receive payment.' mod='bankwire'}</strong>
	<p>
		{l s='If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' mod='bankwire' tags=["<a href='{$contact_url}'>"]}
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].' mod='bankwire' tags=["<a href='{$contact_url}'>"]}
	</p>
{/if}
