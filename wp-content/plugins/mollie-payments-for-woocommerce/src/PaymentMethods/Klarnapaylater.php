<?php

declare (strict_types=1);
namespace Mollie\WooCommerce\PaymentMethods;

class Klarnapaylater extends \Mollie\WooCommerce\PaymentMethods\AbstractPaymentMethod implements \Mollie\WooCommerce\PaymentMethods\PaymentMethodI
{
    protected function getConfig(): array
    {
        return ['id' => 'klarnapaylater', 'defaultTitle' => 'Klarna Pay later', 'settingsDescription' => 'To accept payments via Klarna, all default WooCommerce checkout fields should be enabled and required.', 'defaultDescription' => '', 'paymentFields' => \false, 'instructions' => \false, 'supports' => ['products', 'refunds'], 'filtersOnBuild' => \false, 'confirmationDelayed' => \false, 'docs' => 'https://www.mollie.com/gb/payments/klarna'];
    }
    public function initializeTranslations(): void
    {
        if ($this->translationsInitialized) {
            return;
        }
        $this->config['defaultTitle'] = __('Klarna Pay later', 'mollie-payments-for-woocommerce');
        $this->config['settingsDescription'] = __('To accept payments via Klarna, all default WooCommerce checkout fields should be enabled and required.', 'mollie-payments-for-woocommerce');
        $this->translationsInitialized = \true;
    }
    public function getFormFields($generalFormFields): array
    {
        return $generalFormFields;
    }
}
