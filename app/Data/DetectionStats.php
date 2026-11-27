<?php

namespace App\Data;

final readonly class DetectionStats
{
    /**
     * @param  array<string, int>  $byHour  ['18:00' => n, ...]
     * @param  array<string, int>  $byDay  ['Sen' => n, ...]
     * @param  array<string, int>  $byNode  ['PIR 1' => n, ...]
     */
    public function __construct(
        public int $total,
        public array $byHour,
        public array $byDay,
        public array $byNode,
    ) {}

    /** Label dengan nilai tertinggi, atau null jika semua nol. */
    public static function peakOf(array $series): ?string
    {
        if ($series === [] || max($series) === 0) {
            return null;
        }

        return (string) array_search(max($series), $series, true);
    }

    public function peakHour(): ?string
    {
        return self::peakOf($this->byHour);
    }

    public function peakDay(): ?string
    {
        return self::peakOf($this->byDay);
    }

    public function topNode(): ?string
    {
        return self::peakOf($this->byNode);
    }
}
