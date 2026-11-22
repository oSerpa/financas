<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Lê o mês da query string (?mes=2026-11) e oferece o mês anterior e o próximo.
 * Valor ausente ou inválido cai no mês atual.
 *
 * É usado no painel e na lista de lançamentos para a navegação entre meses
 * (componente month-nav).
 */
class MonthFilter
{
    // Promoção de propriedade no construtor: declara e preenche $this->month
    // numa linha só. readonly impede que o valor mude depois de criado.
    public function __construct(public readonly Carbon $month) {}

    /** Cria o filtro a partir da requisição. Ex.: MonthFilter::fromRequest($request). */
    public static function fromRequest(Request $request): self
    {
        $value = (string) $request->query('mes');

        // Aceita só AAAA-MM com mês de 01 a 12. Qualquer outra coisa é ignorada.
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            // O "!" zera os campos não informados (dia e hora), evitando
            // que o dia de hoje "vaze" para a data criada.
            return new self(Carbon::createFromFormat('!Y-m', $value)->startOfMonth());
        }

        return new self(now()->startOfMonth());
    }

    /** O mês no formato da URL: "2026-11". */
    public function key(): string
    {
        return $this->month->format('Y-m');
    }

    /** O mês por extenso para o título: "novembro de 2026". */
    public function label(): string
    {
        return $this->month->locale('pt_BR')->translatedFormat('F \d\e Y');
    }

    /** Valor de ?mes= para o link do mês anterior. */
    public function previous(): string
    {
        return $this->month->copy()->subMonth()->format('Y-m');
    }

    /** Valor de ?mes= para o link do próximo mês. */
    public function next(): string
    {
        return $this->month->copy()->addMonth()->format('Y-m');
    }

    /** true quando o filtro é o mês de hoje (a view esconde o botão "voltar para hoje"). */
    public function isCurrent(): bool
    {
        return $this->month->isSameMonth(now());
    }
}
