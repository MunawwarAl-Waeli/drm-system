<?php

namespace Modules\ReaderApp\Transformers\Traits;

use Carbon\Carbon;

trait ExtractsFlatRulesTrait
{
    /**
     * دالة موحدة لاستخراج القواعد وتسطيحها لجدول SQLite
     */
    protected function extractFlatRules($document, $pivotOverrides = null): array
    {
        $baseRules = $document->securityControls;

        $maxPrints = $baseRules?->prints_allowed ?? 0;
        $maxViews = $baseRules?->max_views ?? 0;
        $expiryMode = $baseRules?->expiry_mode ?? 'never';
        $expiryDate = $baseRules?->expiry_date ? Carbon::parse($baseRules->expiry_date)->format('Y-m-d H:i:s') : null;
        $expiryDays = $baseRules?->expiry_days ?? 0;

        if ($pivotOverrides && isset($pivotOverrides->access_mode)) {
            $maxPrints = $pivotOverrides->prints_override ?? $maxPrints;
            $maxViews = $pivotOverrides->views_override ?? $maxViews;

            if ($pivotOverrides->access_mode == "limited") {
                $expiryMode = "fixed_date";
                $expiryDate = $pivotOverrides->valid_until ? Carbon::parse($pivotOverrides->valid_until)->format('Y-m-d H:i:s') : null;
                $expiryDays = 0;
            } elseif ($pivotOverrides->access_mode == "unlimited") {
                $expiryMode = "never";
                $expiryDate = null;
                $expiryDays = 0;
            }
        }

        return [
            'expiry_mode' => $expiryMode,
            'expiry_date' => $expiryDate,
            'expiry_days' => $expiryDays,

            'max_views' => $maxViews,
            'max_prints' => $maxPrints,

            'verify_mode' => $baseRules?->verify_mode ?? 'never',
            'verify_frequency_days' => $baseRules?->verify_frequency_days ?? 0,
            'grace_period_days' => $baseRules?->grace_period_days ?? 0,
        ];
    }
}
