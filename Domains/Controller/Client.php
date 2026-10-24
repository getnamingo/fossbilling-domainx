<?php

/*
 * Namingo Domain Prices
 *
 * Copyright (c) 2023-2026 Taras Kondratyuk
 * Copyright (c) 2025-2026 Namingo contributors
 * Copyright (c) 2026 Terbora Ltd.
 *
 * Licensed under the Apache License, Version 2.0.
 * May contain portions of FOSSBilling sample modules, also licensed under Apache-2.0.
 * See the LICENSE file for the full license text.
 *
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace Box\Mod\Domains\Controller;

class Client implements \FOSSBilling\InjectionAwareInterface
{
    protected ?\Pimple\Container $di = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    public function register(\Box_App &$app): void
    {
        $app->get('/domains', 'get_index', [], static::class);
    }

    public function get_index(\Box_App $app): string
    {
        $tlds = $this->di['api_guest']->servicedomain_tlds([]);
        usort($tlds, static fn (array $a, array $b): int => strnatcasecmp($a['tld'], $b['tld']));

        $columns = [
            'price_registration' => ['label' => 'Register', 'annual' => true, 'allow' => 'allow_register'],
            'price_renew' => ['label' => 'Renew', 'annual' => true, 'allow' => 'allow_renew'],
            'price_transfer' => ['label' => 'Transfer', 'annual' => false, 'allow' => 'allow_transfer'],
        ];

        foreach ($tlds as $tld) {
            foreach ($tld as $field => $value) {
                if (str_starts_with($field, 'price_') && !isset($columns[$field])) {
                    $action = substr($field, 6);
                    $columns[$field] = [
                        'label' => ucwords(str_replace('_', ' ', $action)),
                        'annual' => false,
                        'allow' => 'allow_' . $action,
                    ];
                }
            }
        }

        return $app->render('mod_domains_index', [
            'tlds' => $tlds,
            'price_columns' => $columns,
            'currency' => $this->di['api_guest']->cart_get_currency(),
        ]);
    }
}