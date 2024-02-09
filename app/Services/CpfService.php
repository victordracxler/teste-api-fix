<?php

namespace App\Services;

class CpfService
{
    public function validaCpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($i = 9; $i < 11; $i++) {
            $digito = 0;
            for ($j = 0; $j < $i; $j++) {
                $digito += $cpf[$j] * (($i + 1) - $j);
            }
            $digito = (($digito % 11) < 2) ? 0 : 11 - ($digito % 11);
            if ($digito != $cpf[$i]) {
                return false;
            }
        }

        return true;
    }
}
