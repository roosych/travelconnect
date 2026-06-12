<?php

namespace App\Domain\Proposals\Services;

/**
 * A single priced line of a proposal: one service from one supplier, with its
 * net cost and applied markup resolved. All *_azn amounts are in AZN.
 *
 * Produced by ProposalPricing and consumed both by the proposal total
 * recalculation and the booking cost snapshot, so the two can never diverge.
 */
final readonly class PricedLine
{
    public function __construct(
        public int $offerId,
        public ?int $supplierId,
        public string $supplierName,
        public ?string $serviceType,
        public string $name,
        public ?string $description,
        public int $quantity,
        public float $netUnitPrice,
        public ?string $netCurrency,
        public ?float $netFxRate,
        public float $netAmountAzn,
        public float $markupPct,
        public float $sellAmountAzn,
    ) {}
}
