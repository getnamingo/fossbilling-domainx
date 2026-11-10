<?php

declare(strict_types=1);

/* SPDX-License-Identifier: Apache-2.0 */

namespace Box\Mod\Domainx\Controller;

class Admin implements \FOSSBilling\InjectionAwareInterface
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

    public function fetchNavigation(): array
    {
        return ['subpages' => [[
            'location' => 'extensions',
            'label' => __trans('DomainX'),
            'index' => 2000,
            'uri' => $this->di['url']->adminLink('domainx'),
            'class' => '',
        ]]];
    }

    public function register(\Box_App &$app): void
    {
        $app->get('/domainx', 'get_index', [], static::class);
    }

    public function get_index(\Box_App $app): string
    {
        $this->di['is_admin_logged'];
        $this->di['mod_service']('extension')->hasManagePermission('mod_domainx');

        return $app->render('mod_domainx_index');
    }
}
