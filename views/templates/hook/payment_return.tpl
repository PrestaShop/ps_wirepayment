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

{if $status == 'ok'}
    <p>
      {l s='Seu pedido em %s está completo.' sprintf=[$shop_name] d='Modules.PixPayment.Shop'}<br/>
      {l s='Por favor, nos envie um PIX de:' d='Modules.PixPayment.Shop'}
    </p>
    {include file='module:pixpayment/views/templates/hook/_partials/payment_infos.tpl'}

    <p>
      {l s='No campo de mensagem/comentário do PIX, escreva: %s' sprintf=[$reference] d='Modules.PixPayment.Shop'}<br/>
      {l s='Nós te enviamos essas informações também por e-mail.' d='Modules.PixPayment.Shop'}
    </p>
    <strong>{l s='Seu pedido será enviado assim que verificarmos o pagamento' d='Modules.PixPayment.Shop'}</strong>
    <p>
      {l s='Se você tem dúvidas, comentários ou sugestões, escreva para nosso [1]suporte[/1].' d='Modules.PixPayment.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{else}
    <p class="warning">
      {l s='Nós percebemos um problema com seu pedido. Se você acha que isso é um erro, por favor, diga para nosso [1]suporte[/1].' d='Modules.PixPayment.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{/if}
