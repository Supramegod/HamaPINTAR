<?php

namespace App\Data;

use App\Enums\TrendStatus;

final readonly class TrendPrediction
{
    /**
     * @param  array<string, int>  $history  ['Y-m-d' => total] hari terakhir yang diamati
     * @param  array<string, int>  $forecast  ['Y-m-d' => total] hasil proyeksi
     */
    public function __construct(
        public TrendStatus $status,
        public float $slope,
        public array $history,
        public array $forecast,
    ) {}

    public function message(): string
    {
        return $this->status->message($this->slope);
    }
}
