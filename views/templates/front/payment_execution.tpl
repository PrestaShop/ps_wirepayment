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

<section>
	<h1>{l s='Bank-wire payment' mod='bankwire'}</h1>
	<form action="{$confirm_url}" method="post">
		<p>
			<img src="{$image_url}" alt="{l s='Bank wire' mod='bankwire' js='1'}">
			<p>{l s='You have chosen to pay by bank wire.' mod='bankwire'}</p>
			<p>{l s='Here is a short summary of your order:' mod='bankwire'}</p>
			<ul>
				<li>{l s='The total amount of your order is %1$s' mod='bankwire' sprintf=[$total]}</li>
				<li>
					{if $currencies|count}
						<p>{l s='We allow several currencies to be sent via bank wire.' mod='bankwire'}</p>
						<p>{l s='Please choose one of the following:' mod='bankwire'}
							<select id="currency_payement" name="currency_payement">
								{foreach from=$currencies item=currency}
									<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>
										{$currency.name}
									</option>
								{/foreach}
							</select>
						</p>
					{else}
						<p>{l s='We allow the following currency to be sent via bank wire:' mod='bankwire'} {$currencies.0.name}</p>
						<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
					{/if}
				</li>
			</ul>
		</p>
		<p>
			<p>{l s='Bank wire account information will be displayed on the next page.' mod='bankwire'}</p>
			<strong>{l s='Please confirm your order by clicking "I confirm my order".' mod='bankwire'}</strong>
		</p>
		<p>
			<button type="submit">{l s='I confirm my order' mod='bankwire'}</button>
			<a href="{$back_url}">{l s='Other payment methods' mod='bankwire'}</a>
		</p>
	</form>
</section>
