<?php

if (!function_exists('employe_connecte_id')) {
    function employe_connecte_id(): ?int
    {
        $id = session()->get('id_employe');
        return $id ? (int) $id : null;
    }
}

if (!function_exists('employe_connecte')) {
    function employe_connecte(): ?array
    {
        return [
            'id'          => session()->get('id_employe'),
            'nom_complet' => session()->get('nom_complet'),
            'matricule'   => session()->get('matricule'),
            'role'        => session()->get('role'),
        ];
    }
}