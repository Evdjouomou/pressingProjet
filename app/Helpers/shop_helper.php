<?php

    if (!function_exists('shop_actif_id')) {
        /**
         * Retourne le shop_id de l'utilisateur connecté.
         * NULL = admin central (voit tout).
         */
        function shop_actif_id(): ?int
        {
            $shopId = session()->get('shop_id');
            $role   = session()->get('role');

            // L'admin central n'est pas limité à un shop
            if ($role === 'admin' && !$shopId) {
                $vue = session()->get('shop_id_vue');
                return $vue ? (int) $vue : null;
            }

            return $shopId ? (int) $shopId : null;
        }
    }

    if (!function_exists('est_admin_central')) {
        function est_admin_central(): bool
        {
            return session()->get('role') === 'admin'
                && !session()->get('shop_id');
        }
    }

    if (!function_exists('filtrer_par_shop')) {
        /**
         * Applique automatiquement le filtre shop_id
         * sur un QueryBuilder si l'utilisateur n'est pas admin central.
         */
        function filtrer_par_shop($builder, string $alias = ''): void
        {
            $shopId = shop_actif_id();
            if ($shopId !== null) {
                $col = $alias ? "{$alias}.shop_id" : 'shop_id';
                $builder->where($col, $shopId);
            }
        }
    }