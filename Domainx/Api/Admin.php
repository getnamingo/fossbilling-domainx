<?php

declare(strict_types=1);

/* SPDX-License-Identifier: Apache-2.0 */

namespace Box\Mod\Domainx\Api;

class Admin extends \FOSSBilling\Api\AbstractApi
{
    public function presentation_get($data): array
    {
        return $this->getService()->getAdminPresentation();
    }

    public function presentation_save($data): bool
    {
        if (!isset($data['settings']) || !is_string($data['settings']) || strlen($data['settings']) > 1048576) {
            throw new \FOSSBilling\InformationException('Invalid TLD presentation settings.');
        }
        $settings = json_decode($data['settings'], true);
        if (!is_array($settings)) {
            throw new \FOSSBilling\InformationException('Invalid TLD presentation settings.');
        }

        return $this->getService()->savePresentation($settings);
    }
}
