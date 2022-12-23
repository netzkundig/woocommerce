<?php

namespace UnzerPayments\Gateways;


use UnzerPayments\Services\PaymentService;

if (!defined('ABSPATH')) {
    exit;
}

class Ideal extends AbstractGateway
{
    const GATEWAY_ID = 'unzer_ideal';
    public $method_title = 'Unzer iDEAL';
    public $method_description;
    public $title = 'iDEAL';
    public $description = '';
    public $id = self::GATEWAY_ID;
    public $plugin_id;
    public $supports = [
        'products',
        'refunds',
    ];

    public function has_fields()
    {
        return true;
    }

    public function payment_fields()
    {
        $description = $this->get_description();
        if ($description) {
            echo wpautop(wptexturize($description));
        }
        ?>
        <div id="unzer-ideal-form" class="unzerUI form" novalidate>
            <input type="hidden" id="unzer-ideal-id" name="unzer-ideal-id" value=""/>
            <div id="unzer-ideal" class="field"></div>
        </div>
        <?php
    }

    public function get_form_fields()
    {
        return apply_filters(
            'wc_unzer_settings',
            [

                'enabled' => [
                    'title' => __('Enable/Disable', UNZER_PLUGIN_NAME),
                    'label' => __('Enable Unzer iDEAL', UNZER_PLUGIN_NAME),
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no',
                ],
                'title' => [
                    'title' => __('Title', UNZER_PLUGIN_NAME),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', UNZER_PLUGIN_NAME),
                    'default' => __('iDEAL', UNZER_PLUGIN_NAME),
                ],
                'description' => [
                    'title' => __('Description', UNZER_PLUGIN_NAME),
                    'type' => 'text',
                    'description' => __('This controls the description which the user sees during checkout.', UNZER_PLUGIN_NAME),
                    'default' => '',
                ],
            ]
        );
    }

    public function process_payment($order_id)
    {
        $this->logger->debug('start payment for #' . $order_id . ' with ' . self::GATEWAY_ID);
        $return = [
            'result' => 'success',
        ];
        $transaction = (new PaymentService())->performChargeForOrder($order_id, $this, $_POST['unzer-ideal-id']);
        if ($transaction->getPayment()->getRedirectUrl()) {
            $return['redirect'] = $transaction->getPayment()->getRedirectUrl();
        }
        return $return;
    }
}
