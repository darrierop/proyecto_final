<?php
// incluye/paginar.php — Helper de paginación
/**
 * Calcula los datos de paginación.
 * @param int $total      Total de registros
 * @param int $porPagina  Registros por página
 * @param int $actual     Página actual (desde GET 'p')
 * @return array ['offset', 'paginas', 'actual', 'inicio', 'fin']
 */
function paginar(int $total, int $porPagina = 10, ?int $actual = null): array
{
    $actual = max(1, $actual ?? (int) ($_GET['p'] ?? 1));
    $paginas = max(1, (int) ceil($total / $porPagina));
    $actual = min($actual, $paginas);
    $offset = ($actual - 1) * $porPagina;
    $inicio = $total === 0 ? 0 : $offset + 1;
    $fin = min($offset + $porPagina, $total);
    return compact('offset', 'paginas', 'actual', 'inicio', 'fin', 'total', 'porPagina');
}

/**
 * Renderiza los controles de paginación en HTML.
 */
function renderPaginacion(array $p, string $urlBase = ''): string
{
    if ($p['paginas'] <= 1)
        return '';

    $html = '<div class="paginacion">';
    $html .= '<span class="pag-info">Mostrando ' . $p['inicio'] . '–' . $p['fin'] . ' de ' . $p['total'] . '</span>';
    $html .= '<div class="pag-botones">';

    $prev = $p['actual'] - 1;
    $next = $p['actual'] + 1;

    $html .= '<a class="pag-btn' . ($p['actual'] <= 1 ? ' pag-dis' : '') . '" href="' . $urlBase . '?p=' . $prev . '">‹</a>';

    for ($i = 1; $i <= $p['paginas']; $i++) {
        if ($p['paginas'] > 7 && abs($i - $p['actual']) > 2 && $i !== 1 && $i !== $p['paginas']) {
            if ($i === $p['actual'] - 3 || $i === $p['actual'] + 3) {
                $html .= '<span class="pag-sep">…</span>';
            }
            continue;
        }
        $html .= '<a class="pag-btn' . ($i === $p['actual'] ? ' pag-activa' : '') . '" href="' . $urlBase . '?p=' . $i . '">' . $i . '</a>';
    }

    $html .= '<a class="pag-btn' . ($p['actual'] >= $p['paginas'] ? ' pag-dis' : '') . '" href="' . $urlBase . '?p=' . $next . '">›</a>';
    $html .= '</div></div>';
    return $html;
}
