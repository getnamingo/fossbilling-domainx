<?php
/**
 * Namingo DomainX module for FOSSBilling (https://fossbilling.org/)
 *
 * Written in 2026 by Namingo Team (https://namingo.org)
 *
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Box\Mod\Domainx\Api;

use FOSSBilling\Validation\Api\RequiredParams;

class Guest extends \FOSSBilling\Api\AbstractApi
{
    /**
     * Check an SLD against active registration TLDs, subject to the code-owned limit.
     * Uses each TLD's assigned registrar, like servicedomain/check.
     *
     * An optional TLD is always returned first and included within the limit.
     *
     * @param array{sld: string, tld?: string} $data
     */
    #[RequiredParams(['sld' => 'SLD is missing'])]
    public function check_all($data): array
    {
        // Charge once for the batch, including requests served from cache.
        $this->getDi()['rate_limiter']->consumeOrThrow('domain_lookup_ip', (string) $this->getIp());

        if (!isset($data['sld']) || !is_string($data['sld'])) {
            throw new \FOSSBilling\InformationException('SLD is missing or invalid');
        }

        if (isset($data['tld']) && !is_string($data['tld'])) {
            throw new \FOSSBilling\InformationException('TLD is invalid.');
        }

        return $this->getService()->checkAll($data['sld'], (string) $this->getIp(), $data['tld'] ?? '');
    }
}