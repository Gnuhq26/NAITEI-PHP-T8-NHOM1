<?php

namespace App\Services;

class ShippingService
{
    public function calculateShippingFee(float $totalAmount): float
    {
        // < 5m -> free ship
        if ($totalAmount < 5000000) {
            return 0;
        }

        // 5m - 10m -> 200k ship
        if ($totalAmount >= 5000000 && $totalAmount < 10000000) {
            return 200000;
        }

        // 10m - 20m -> 500k ship
        if ($totalAmount >= 10000000 && $totalAmount < 20000000) {
            return 500000;
        }

        // 20m and above -> 800k ship
        return 800000;
    }

    public function getShippingInfo(float $totalAmount): array
    {
        $shippingFee = $this->calculateShippingFee($totalAmount);
        
        return [
            'subtotal' => $totalAmount,
            'shipping_fee' => $shippingFee,
            'total' => $totalAmount + $shippingFee,
            'is_free_shipping' => $shippingFee === 0,
            'shipping_tier' => $this->getShippingTier($totalAmount)
        ];
    }

    private function getShippingTier(float $totalAmount): string
    {
        if ($totalAmount < 5000000) {
            return 'free';
        } elseif ($totalAmount < 10000000) {
            return 'standard';
        } elseif ($totalAmount < 20000000) {
            return 'premium';
        } else {
            return 'express';
        }
    }

    // calculate amount needed to get free shipping
    public function getAmountForFreeShipping(float $totalAmount): ?float
    {
        if ($totalAmount >= 5000000) {
            return null;
        }
        
        return 5000000 - $totalAmount;
    }
}
