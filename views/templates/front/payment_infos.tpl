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
  <p>{l s='Please send us a bank wire with:' mod='bankwire'}</p>
  <dl>
    <dt>{l s='Amount' mod='bankwire'}</dt>
    <dd>{$total}</dd>
    <dt>{l s='Name of account owner' mod='bankwire'}</dt>
    <dd>{$bankwireOwner}</dd>
    <dt>{l s='Please include these details' mod='bankwire'}</dt>
    <dd>{$bankwireDetails}</dd>
    <dt>{l s='Bank name' mod='bankwire'}</dt>
    <dd>{$bankwireAddress}</dd>
  </dl>
</section>
