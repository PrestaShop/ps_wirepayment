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

<section>
  <p>
    {l s='Por favor, transfira o valor do pedido para a conta PIX. Você vai receber uma confirmação do pedido via e-mail com as informações para fazer a transferência.' d='Modules.PixPayment.Shop'}
    {if $pixReservationDays}
      {l s='Pedidos ficarão reservados por %s dias para você e nós iremos processar os pedidos assim que recebermos sua transferência PIX' sprintf=[$pixReservationDays] d='Modules.PixPayment.Shop'}
    {/if}
    {if $pixCustomText }
        <a data-toggle="modal" data-target="#pix-modal">{l s='Mais Informações' d='Modules.PixPayment.Shop'}</a>
    {/if}
  </p>

  <div class="modal fade" id="pix-modal" tabindex="-1" role="dialog" aria-labelledby="PIX Informações" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h2>{l s='PIX' d='Modules.PixPayment.Shop'}</h2>
        </div>
        <div class="modal-body">
          <p>{l s='Pagamento é feito transferindo o valor do pedido para a Chave PIX:' d='Modules.PixPayment.Shop'}</p>
          {include file='module:pixpayment/views/templates/hook/_partials/payment_infos.tpl'}
          {$pixCustomText nofilter}
        </div>
      </div>
    </div>
  </div>
</section>
