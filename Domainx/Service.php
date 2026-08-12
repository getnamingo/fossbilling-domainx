<?php
/**
 * Namingo DomainX module for FOSSBilling (https://fossbilling.org/)
 *
 * Written in 2026 by Namingo Team (https://namingo.org)
 *
 * @license Apache-2.0
 */

declare(strict_types=1);

namespace Box\Mod\Domainx;

use FOSSBilling\InjectionAwareInterface;

class Service implements InjectionAwareInterface
{
    private const SECDNS_11 = 'urn:ietf:params:xml:ns:secDNS-1.1';
    private const MAX_DNSSEC_RECORDS = 2;

    /**
     * This allowlist is intentionally code-owned. Adding a registrar requires a
     * reviewed mapping; merely installing an arbitrary adapter never exposes it.
     *
     * Keys are normalized FOSSBilling tld_registrar.registrar values.
     */
    private const SUPPORTED_ADAPTERS = [
        'namingo' => [
            'name' => 'Namingo EPP Registrar',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'switch' => [
            'name' => 'SWITCH (.ch/.li)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'niccl' => [
            'name' => 'NIC Chile (.cl)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'cocca' => [
            'name' => 'CoCCA Registry Services',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'eurid' => [
            'name' => 'EURid (.eu)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'afnic' => [
            'name' => 'AFNIC (.fr)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'nicge' => [
            'name' => 'NIC.GE (.ge)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'carnet' => [
            'name' => 'CARNET (.hr)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'sidn' => [
            'name' => 'SIDN (.nl)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'nask' => [
            'name' => 'NASK (.pl)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'iis' => [
            'name' => 'Internetstiftelsen (.se)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'hostmaster' => [
            'name' => 'Hostmaster (.ua)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'central' => [
            'name' => 'CentralNic Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'core' => [
            'name' => 'CORE Association',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'dns' => [
            'name' => 'DNS.business',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'godaddy' => [
            'name' => 'GoDaddy Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'google' => [
            'name' => 'Google Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'hello' => [
            'name' => 'Hello Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'identity' => [
            'name' => 'Identity Digital',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'org' => [
            'name' => 'Public Interest Registry (.org)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'itcom' => [
            'name' => '.IT.COM Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'ryce' => [
            'name' => 'RyCE Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'tucows' => [
            'name' => 'Tucows Registry',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
        'verisign' => [
            'name' => 'Verisign (.com/.net)',
            'features' => ['dnssec'],
            'dnssec_method' => 'updateDNSSEC',
            'dnssec_info_method' => 'getDNSSEC',
        ],
    ];

    protected ?\Pimple\Container $di = null;

    public function setDi(\Pimple\Container $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    public function getCapabilities(
        \Model_ClientOrder $order,
        \Model_ServiceDomain $domain
    ): array {
        $domainName = $this->domainName($domain);

        try {
            $context = $this->registrarContext($order, $domain);
            $records = $this->getDnssecRecords($context, $domain);

            return [
                'domain' => $domainName,
                'available' => true,
                'features' => [
                    'dnssec' => [
                        'available' => true,
                        'operations' => ['add', 'rem', 'addrem'],
                        'record_format' => [
                            'key_tag',
                            'algorithm',
                            'digest_type',
                            'digest',
                        ],
                        'records' => $records,
                        'max_records' => self::MAX_DNSSEC_RECORDS,
                        'can_add' => count($records) < self::MAX_DNSSEC_RECORDS,
                    ],
                ],
                'provider' => $context['definition']['name'],
            ];
        } catch (\Throwable $e) {
            $this->logNotice(
                'DomainX capabilities unavailable for %s: %s',
                [$domainName, $e->getMessage()]
            );

            return [
                'domain' => $domainName,
                'available' => false,
                'features' => [
                    'dnssec' => [
                        'available' => false,
                    ],
                ],
            ];
        }
    }

    /**
     * @param array{
     *     adapter: object,
     *     config: array<string, mixed>,
     *     definition: array<string, mixed>
     * } $context
     *
     * @return array<int, array{
     *     key_tag: int,
     *     algorithm: int,
     *     digest_type: int,
     *     digest: string
     * }>
     */
    private function getDnssecRecords(
        array $context,
        \Model_ServiceDomain $domain
    ): array {
        $registrarDomain = new \Registrar_Domain();
        $registrarDomain->setSld((string) $domain->sld);
        $registrarDomain->setTld((string) $domain->tld);

        $adapter = $context['adapter'];
        $method = (string) $context['definition']['dnssec_info_method'];

        $records = $adapter->{$method}($registrarDomain);

        if (!is_array($records)) {
            throw new \FOSSBilling\Exception(
                'The registrar returned invalid DNSSEC record data'
            );
        }

        $normalized = [];

        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new \FOSSBilling\Exception(
                    'The registrar returned an invalid DNSSEC record'
                );
            }

            $normalized[] = $this->readRecord(
                ['record' => $record],
                'record',
                false
            );
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateDnssec(
        \Model_ClientOrder $order,
        \Model_ServiceDomain $domain,
        array $data,
    ): array {
        $context = $this->registrarContext($order, $domain);

        $command = strtolower(trim(
            (string) ($data['command'] ?? 'add')
        ));

        if (!in_array($command, ['add', 'rem', 'addrem'], true)) {
            throw new \FOSSBilling\InformationException(
                'Unsupported DNSSEC command'
            );
        }

        if (
            $command === 'add'
            && count($this->getDnssecRecords($context, $domain))
                >= self::MAX_DNSSEC_RECORDS
        ) {
            throw new \FOSSBilling\InformationException(
                'A maximum of two DNSSEC DS records is allowed'
            );
        }

        $record = $this->readRecord($data, 'record');

        $params = [
            'command' => $command,
            'keyTag_1' => $record['key_tag'],
            'alg_1' => $record['algorithm'],
            'digestType_1' => $record['digest_type'],
            'digest_1' => $record['digest'],
        ];

        if ($command === 'addrem') {
            $newRecord = $this->readRecord(
                $data,
                'new_record',
                false
            );

            $params['keyTag_2'] = $newRecord['key_tag'];
            $params['alg_2'] = $newRecord['algorithm'];
            $params['digestType_2'] = $newRecord['digest_type'];
            $params['digest_2'] = $newRecord['digest'];
        }

        $adapter = $context['adapter'];
        $method = (string) $context['definition']['dnssec_method'];

        $registrarDomain = new \Registrar_Domain();
        $registrarDomain->setSld((string) $domain->sld);
        $registrarDomain->setTld((string) $domain->tld);

        try {
            $result = $adapter->{$method}(
                $registrarDomain,
                $params
            );

            if (!is_array($result)) {
                throw new \FOSSBilling\Exception(
                    'The registry returned an invalid DNSSEC response'
                );
            }

            if (!empty($result['error'])) {
                throw new \FOSSBilling\InformationException(
                    'The registry rejected the DNSSEC update: :error',
                    [':error' => (string) $result['error']]
                );
            }

            $code = (int) ($result['code'] ?? 0);
            $message = $this->responseMessage(
                $result['msg'] ?? 'Unknown registry response'
            );

            if ($code < 1000 || $code >= 2000) {
                throw new \FOSSBilling\InformationException(
                    'The registry rejected the DNSSEC update (:code): :message',
                    [
                        ':code' => $code,
                        ':message' => $message,
                    ]
                );
            }

            $this->logInfo(
                'DomainX DNSSEC %s accepted for %s with EPP code %d',
                [
                    $command,
                    $this->domainName($domain),
                    $code,
                ]
            );

            return [
                'success' => true,
                'domain' => $this->domainName($domain),
                'command' => $command,
                'code' => $code,
                'message' => $message,
            ];
        } catch (\FOSSBilling\Exception $e) {
            throw $e;
        } catch (\Registrar_Exception $e) {
            $this->logNotice(
                'DomainX DNSSEC update rejected for %s: %s',
                [
                    $this->domainName($domain),
                    $e->getMessage(),
                ]
            );

            throw new \FOSSBilling\InformationException(
                'DNSSEC update failed: :error',
                [':error' => $e->getMessage()]
            );
        } catch (\Throwable $e) {
            $this->logNotice(
                'DomainX DNSSEC update failed for %s: %s',
                [
                    $this->domainName($domain),
                    $e->getMessage(),
                ]
            );

            throw new \FOSSBilling\Exception(
                'DNSSEC update failed. Please try again later.'
            );
        }
    }

    /**
     * Resolve an installed registrar through the core Servicedomain service. This
     * is where the existing tld_registrar.config JSON is read and supplied to the
     * same adapter used by normal FOSSBilling domain operations.
     *
     * @return array{adapter: object, config: array<string, mixed>, definition: array<string, mixed>}
     */
    private function registrarContext(\Model_ClientOrder $order, \Model_ServiceDomain $domain): array
    {
        if ((int) $domain->tld_registrar_id < 1) {
            throw new \FOSSBilling\Exception('Domain has no configured registrar');
        }

        $registrar = $this->di['db']->load('TldRegistrar', (int) $domain->tld_registrar_id);
        if (!$registrar instanceof \Model_TldRegistrar || (int) $registrar->id < 1) {
            throw new \FOSSBilling\Exception('Domain registrar is not installed');
        }

        $registrarCode = strtolower(trim((string) $registrar->registrar));
        $definition = self::SUPPORTED_ADAPTERS[$registrarCode] ?? null;
        if (!is_array($definition) || !in_array('dnssec', $definition['features'], true)) {
            throw new \FOSSBilling\Exception('Domain registrar is not allowlisted for DNSSEC');
        }

        $domainService = $this->di['mod_service']('servicedomain');
        $config = $domainService->registrarGetConfiguration($registrar);
        $this->assertEppConfiguration($config);

        $profile = strtolower(trim((string) ($config['registry_profile'] ?? 'generic')));
        if ($profile === 'generic' && !$this->hasSecDnsExtension($config)) {
            throw new \FOSSBilling\Exception('secDNS 1.1 is not enabled in the EPP login extensions');
        }

        $adapter = $domainService->registrarGetRegistrarAdapter(
            $registrar,
            $order
        );

        foreach (['dnssec_method', 'dnssec_info_method'] as $methodKey) {
            $method = (string) ($definition[$methodKey] ?? '');

            if ($method === '' || !is_callable([$adapter, $method])) {
                throw new \FOSSBilling\Exception(
                    'Installed registrar adapter is incompatible with DomainX. Missing public method: :method',
                    [':method' => $method !== '' ? $method : $methodKey]
                );
            }
        }

        return [
            'adapter' => $adapter,
            'config' => $config,
            'definition' => $definition,
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    private function assertEppConfiguration(array $config): void
    {
        foreach (['host', 'clid', 'pw', 'local_cert', 'local_pk'] as $field) {
            if (!isset($config[$field]) || trim((string) $config[$field]) === '') {
                throw new \FOSSBilling\Exception('EPP registrar configuration is incomplete');
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function hasSecDnsExtension(array $config): bool
    {
        $raw = $config['login_extensions'] ?? [];
        if (!is_array($raw)) {
            $raw = preg_split('/[,\s]+/', (string) $raw) ?: [];
        }

        $extensions = array_map(
            static fn (mixed $value): string => trim((string) $value),
            $raw,
        );

        return in_array(self::SECDNS_11, $extensions, true);
    }

    /**
     * Read a friendly DomainX record and validate it before handing it to the
     * upstream library. For add/rem, top-level fields are accepted as a shortcut.
     *
     * @param array<string, mixed> $data
     *
     * @return array{key_tag: int, algorithm: int, digest_type: int, digest: string}
     */
    private function readRecord(array $data, string $key, bool $allowTopLevel = true): array
    {
        $record = $data[$key] ?? null;
        if (!is_array($record) && $allowTopLevel) {
            $record = [
                'key_tag' => $data['key_tag'] ?? null,
                'algorithm' => $data['algorithm'] ?? null,
                'digest_type' => $data['digest_type'] ?? null,
                'digest' => $data['digest'] ?? null,
            ];
        }

        if (!is_array($record)) {
            throw new \FOSSBilling\InformationException('DNSSEC record data is required');
        }

        foreach (['key_tag', 'algorithm', 'digest_type', 'digest'] as $field) {
            if (!array_key_exists($field, $record) || $record[$field] === '') {
                throw new \FOSSBilling\InformationException('DNSSEC field :field is required', [':field' => $field]);
            }
        }

        $keyTag = $this->integerInRange($record['key_tag'], 0, 65535, 'key_tag');
        $algorithm = $this->integerInRange($record['algorithm'], 1, 255, 'algorithm');
        $digestType = $this->integerInRange($record['digest_type'], 1, 255, 'digest_type');
        $digest = strtoupper((string) preg_replace('/\s+/', '', (string) $record['digest']));

        if (!preg_match('/\A[0-9A-F]{40,128}\z/', $digest) || strlen($digest) % 2 !== 0) {
            throw new \FOSSBilling\InformationException(
                'DNSSEC digest must be an even-length hexadecimal value between 40 and 128 characters',
            );
        }

        return [
            'key_tag' => $keyTag,
            'algorithm' => $algorithm,
            'digest_type' => $digestType,
            'digest' => $digest,
        ];
    }

    private function integerInRange(mixed $value, int $min, int $max, string $field): int
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false || $validated < $min || $validated > $max) {
            throw new \FOSSBilling\InformationException(
                'DNSSEC field :field must be an integer between :min and :max',
                [':field' => $field, ':min' => $min, ':max' => $max],
            );
        }

        return $validated;
    }

    private function responseMessage(mixed $message): string
    {
        if (is_array($message)) {
            $message = implode(' ', array_map(
                static fn (mixed $part): string => trim((string) $part),
                $message,
            ));
        }

        $message = trim((string) $message);

        return $message !== '' ? $message : 'Unknown registry response';
    }

    private function domainName(\Model_ServiceDomain $domain): string
    {
        return strtolower(trim((string) $domain->sld . (string) $domain->tld));
    }

    private function asciiDomainName(\Model_ServiceDomain $domain): string
    {
        $name = $this->domainName($domain);
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $name;
        }

        return $name;
    }

    /** @param array<int, mixed> $arguments */
    private function logInfo(string $message, array $arguments): void
    {
        if ($this->di !== null && isset($this->di['logger'])) {
            $this->di['logger']->info(vsprintf($message, $arguments));
        }
    }

    /** @param array<int, mixed> $arguments */
    private function logNotice(string $message, array $arguments): void
    {
        if ($this->di !== null && isset($this->di['logger'])) {
            $this->di['logger']->notice(vsprintf($message, $arguments));
        }
    }
}
