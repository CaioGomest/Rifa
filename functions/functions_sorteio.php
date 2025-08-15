<?php

if (!function_exists('selecionar_numeros_disponiveis')) {
    function selecionar_numeros_disponiveis($quantidade, $numeros_disponiveis)
    {
        $numeros_selecionados = [];
        for ($i = 0; $i < $quantidade; $i++) {
            if (empty($numeros_disponiveis)) break;
            $indice = array_rand($numeros_disponiveis);
            $numeros_selecionados[] = $numeros_disponiveis[$indice];
            unset($numeros_disponiveis[$indice]);
        }
        return $numeros_selecionados;
    }
}

if (!function_exists('normalizar_e_filtrar_premiadas')) {
    function normalizar_e_filtrar_premiadas($lista_cotas_premiadas_raw, $numeros_disponiveis)
    {
        if (is_string($lista_cotas_premiadas_raw)) {
            $itens = array_map('trim', explode(',', $lista_cotas_premiadas_raw));
        } elseif (is_array($lista_cotas_premiadas_raw)) {
            $itens = $lista_cotas_premiadas_raw;
        } else {
            $itens = [];
        }

        $itens = array_filter($itens, function ($v) {
            if ($v === null || $v === '') return false;
            if (!is_numeric($v)) return false;
            return true;
        });

        $itens = array_map(function ($v) { return intval($v); }, $itens);
        $itens = array_values(array_unique($itens));

        $premiadas_validas = array_values(array_intersect($itens, $numeros_disponiveis));
        return $premiadas_validas;
    }
}

if (!function_exists('preencher_ate_quantidade')) {
    function preencher_ate_quantidade($numeros_selecionados, $numeros_disponiveis, $quantidade)
    {
        $numeros_selecionados = array_values(array_unique($numeros_selecionados));
        $numeros_selecionados = array_values(array_intersect($numeros_selecionados, $numeros_disponiveis));

        if (count($numeros_selecionados) < $quantidade) {
            $faltam = $quantidade - count($numeros_selecionados);
            $restantes = array_values(array_diff($numeros_disponiveis, $numeros_selecionados));
            if ($faltam > 0 && !empty($restantes)) {
                $complemento = selecionar_numeros_disponiveis($faltam, $restantes);
                $numeros_selecionados = array_merge($numeros_selecionados, $complemento);
            }
        }

        return $numeros_selecionados;
    }
}

if (!function_exists('obter_numeros_com_premiadas')) {
    function obter_numeros_com_premiadas($quantidade, $numeros_disponiveis, $lista_cotas_premiadas_raw, $status_cotas)
    {
        $status_cotas = is_string($status_cotas) ? strtolower(trim($status_cotas)) : '';
        $premiadas_validas = normalizar_e_filtrar_premiadas($lista_cotas_premiadas_raw, $numeros_disponiveis);

        if ($status_cotas === 'bloqueado') {
            $disponiveis_sem_premiadas = array_values(array_diff($numeros_disponiveis, $premiadas_validas));
            $numeros_selecionados = selecionar_numeros_disponiveis($quantidade, $disponiveis_sem_premiadas);
            return preencher_ate_quantidade($numeros_selecionados, $disponiveis_sem_premiadas, $quantidade);
        }

        $numeros_selecionados = selecionar_numeros_disponiveis($quantidade, $numeros_disponiveis);

        if ($status_cotas === 'imediato' && !empty($premiadas_validas)) {
            $premiadas_disponiveis_nao_selecionadas = array_values(array_diff($premiadas_validas, $numeros_selecionados));

            if (!empty($premiadas_disponiveis_nao_selecionadas) && !empty($numeros_selecionados)) {
                $idx_premiada = array_rand($premiadas_disponiveis_nao_selecionadas);
                $numero_premiado = $premiadas_disponiveis_nao_selecionadas[$idx_premiada];

                $indices_nao_premiados = [];
                $map_premiadas = array_flip($premiadas_validas);
                foreach ($numeros_selecionados as $i => $n) {
                    if (!isset($map_premiadas[$n])) {
                        $indices_nao_premiados[] = $i;
                    }
                }

                if (!empty($indices_nao_premiados)) {
                    $idx_sub = $indices_nao_premiados[array_rand($indices_nao_premiados)];
                    $numeros_selecionados[$idx_sub] = $numero_premiado;
                }
            }
        }

        return preencher_ate_quantidade($numeros_selecionados, $numeros_disponiveis, $quantidade);
    }
}

?>


