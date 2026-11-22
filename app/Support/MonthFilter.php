<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Lê o mês da query string (?mes=2026-11) e oferece o mês anterior e o próximo.
 * Valor ausente ou inválido cai no mês atual.
 */
class MonthFilter
{
    public function __construct(public readonly Carbon $month) {}

    public static function fromRequest(Request $request): self
    {
        $value = (string) $request->query('mes');

        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return new self(Carbon::createFromFormat('!Y-m', $value)->startOfMonth());
        }

        return new self(now()->startOfMonth());
    }

    public function key(): string
    {
        return $this->month->format('Y-m');
    }

    public function label(): string
    {
        return $this->month->locale('pt_BR')->translatedFormat('F \d\e Y');
    }

    public function previous(): string
    {
        return $this->month->copy()->subMonth()->format('Y-m');
    }

    public function next(): string
    {
        return $this->month->copy()->addMonth()->format('Y-m');
    }

    public function isCurrent(): bool
    {
        return $this->month->isSameMonth(now());
    }
}
