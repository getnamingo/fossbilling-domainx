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

class Client extends \FOSSBilling\Api\AbstractApi
{
    /**
     * Return the extended registrar functions available for an active domain order.
     *
     * @param array{order_id?: int|string} $data
     */
    public function capabilities($data): array
    {
        [$order, $domain] = $this->getActiveDomain($data);

        return $this->getService()->getCapabilities($order, $domain);
    }

    /**
     * Add one DNSSEC DS record to an active domain.
     *
     * Accepted input is either a nested `record` array or the four record fields
     * at the top level: key_tag, algorithm, digest_type, and digest.
     *
     * @param array<string, mixed> $data
     */
    public function dnssec_add($data): array
    {
        [$order, $domain] = $this->getActiveDomain($data);

        $data['command'] = 'add';

        return $this->getService()->updateDnssec($order, $domain, $data);
    }

    /**
     * Apply a DNSSEC DS update.
     *
     * Commands:
     * - add:    `record` is added.
     * - rem:    `record` is removed.
     * - addrem: `record` is removed and `new_record` is added atomically.
     *
     * @param array<string, mixed> $data
     */
    public function dnssec_update($data): array
    {
        [$order, $domain] = $this->getActiveDomain($data);

        return $this->getService()->updateDnssec($order, $domain, $data);
    }

    /**
     * Use the same authorization gate as FOSSBilling's core Servicedomain client API.
     * An active order is FOSSBilling's canonical indication that the purchased
     * service has been paid for and provisioned.
     *
     * @param array{order_id?: int|string} $data
     *
     * @return array{0: \Model_ClientOrder, 1: \Model_ServiceDomain}
     */
    private function getActiveDomain(array $data): array
    {
        if (!isset($data['order_id']) || !is_numeric($data['order_id'])) {
            throw new \FOSSBilling\Exception('Order ID is required');
        }

        $orderService = $this->getDi()['mod_service']('order');
        $order = $orderService->findForClientById($this->getIdentity(), (int) $data['order_id']);

        if (!$order instanceof \Model_ClientOrder) {
            throw new \FOSSBilling\Exception('Order not found');
        }

        if ($order->status !== \Model_ClientOrder::STATUS_ACTIVE) {
            throw new \FOSSBilling\Exception('Order is not active');
        }

        $domain = $orderService->getOrderService($order);
        if (!$domain instanceof \Model_ServiceDomain) {
            throw new \FOSSBilling\Exception('Order is not an active domain service');
        }

        if ((int) $domain->client_id !== (int) $this->getIdentity()->id) {
            throw new \FOSSBilling\Exception('Domain service does not belong to this client');
        }

        if (!empty($domain->expires_at)) {
            $expiresAt = strtotime((string) $domain->expires_at);
            if ($expiresAt !== false && $expiresAt < time()) {
                throw new \FOSSBilling\Exception('Domain service has expired');
            }
        }

        return [$order, $domain];
    }
}
