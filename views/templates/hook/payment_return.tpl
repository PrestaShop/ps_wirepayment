{*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $status == 'ok'}
    <p>
      {l s='Your order on %s is complete.' sprintf=[$shop_name] mod='ps_wirepayment'}<br/>
      {l s='Please send us a bank wire with:' mod='ps_wirepayment'}
    </p>
    {include file='module:ps_wirepayment/views/templates/hook/_partials/payment_infos.tpl'}

    <p>
      {l s='Please specify your order reference %s in the bankwire description.' sprintf=[$reference] mod='ps_wirepayment'}<br/>
      {l s='We\'ve also sent you this information by e-mail.' mod='ps_wirepayment'}
    </p>
    <strong>{l s='Your order will be sent as soon as we receive payment.' mod='ps_wirepayment'}</strong>
    <p>
      {l s='If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' mod='ps_wirepayment' tags=["<a href='{$contact_url}'>"]}
    </p>
{else}
    <p class="warning">
      {l s='We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].' mod='ps_wirepayment' tags=["<a href='{$contact_url}'>"]}
    </p>
{/if}
