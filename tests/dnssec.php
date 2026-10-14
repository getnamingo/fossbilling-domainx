<?php
/** Run with php tests/dnssec.php; minimal platform doubles, no FOSSBilling or registry needed. */
declare(strict_types=1);

namespace FOSSBilling {
    interface InjectionAwareInterface {}
    class Exception extends \RuntimeException {
        public function __construct(string $message, array $variables = []) {
            parent::__construct(strtr($message, $variables));
        }
    }
    class InformationException extends Exception {}
}
namespace Pimple {
    class Container extends \ArrayObject {}
}
namespace {
    class Model_ClientOrder {}
    class Model_ServiceDomain {
        public string $sld = 'example';
        public string $tld = '.test';
        public int $tld_registrar_id = 1;
    }
    class Model_TldRegistrar {
        public int $id = 1;
        public string $registrar = 'namingo';
    }
    class Registrar_Domain {
        public function setSld(string $sld): void {}
        public function setTld(string $tld): void {}
    }
    class Registrar_Exception extends \RuntimeException {}

    require dirname(__DIR__) . '/Domainx/Service.php';

    function check(bool $condition, string $message): void {
        if (!$condition) throw new \RuntimeException($message);
    }

    $adapter = new class {
        public array $updates = [];
        public array $records = [];
        public function getDNSSEC($domain): array { return $this->records; }
        public function updateDNSSEC($domain, array $params): array {
            $this->updates[] = $params;
            return ['code' => 1000, 'msg' => 'OK'];
        }
    };
    $domainService = new class($adapter) {
        public function __construct(private object $adapter) {}
        public function registrarGetConfiguration($registrar): array {
            return ['host' => 'example.test', 'clid' => 'test', 'pw' => 'test',
                'local_cert' => 'test.crt', 'local_pk' => 'test.key',
                'login_extensions' => ['urn:ietf:params:xml:ns:secDNS-1.1']];
        }
        public function registrarGetRegistrarAdapter($registrar, $order): object { return $this->adapter; }
    };
    $service = new \Box\Mod\Domainx\Service();
    $service->setDi(new \Pimple\Container([
        'db' => new class {
            public function load($type, $id): Model_TldRegistrar { return new Model_TldRegistrar(); }
        },
        'mod_service' => static fn ($name) => $domainService,
    ]));
    $order = new Model_ClientOrder();
    $domain = new Model_ServiceDomain();

    foreach ([8, 13, 14, 15, 16, 17, 18] as $algorithm) {
        foreach ([2 => 64, 4 => 96, 6 => 64] as $digestType => $length) {
            foreach ([0, 65535] as $keyTag) {
                $record = ['key_tag' => $keyTag, 'algorithm' => $algorithm,
                    'digest_type' => $digestType, 'digest' => str_repeat('AB', $length / 2)];
                $input = array_replace($record, ['digest' => chunk_split(strtolower($record['digest']), 16, " \n")]);
                foreach (['add', 'rem', 'addrem'] as $command) {
                    $data = $command === 'addrem'
                        ? ['command' => $command, 'record' => $input, 'new_record' => $input]
                        : ['command' => $command] + $input;
                    check($service->updateDnssec($order, $domain, $data)['success'], 'DNSSEC update failed');
                    $sent = end($adapter->updates);
                    foreach ($command === 'addrem' ? [1, 2] : [1] as $index) {
                        check($sent['keyTag_' . $index] === $keyTag && $sent['alg_' . $index] === $algorithm
                            && $sent['digestType_' . $index] === $digestType && $sent['digest_' . $index] === $record['digest'], 'DS values changed before forwarding');
                    }
                }
                $adapter->records = [$input];
                check($service->getCapabilities($order, $domain)['features']['dnssec']['records'] === [$record], 'DS info normalization failed');
                $adapter->records = [];
                foreach ([$length - 2, $length + 2] as $badLength) {
                    foreach (['add', 'rem', 'addrem'] as $command) {
                        $bad = array_replace($record, ['digest' => str_repeat('A', $badLength)]);
                        $data = $command === 'addrem'
                            ? ['command' => $command, 'record' => $record, 'new_record' => $bad]
                            : ['command' => $command] + $bad;
                        rejects($service, $order, $domain, $adapter, $data);
                    }
                }
            }
        }
    }

    function rejects($service, $order, $domain, $adapter, array $data): void {
        $count = count($adapter->updates);
        try {
            $service->updateDnssec($order, $domain, $data);
        } catch (\FOSSBilling\InformationException $e) {
            check(count($adapter->updates) === $count, 'Invalid record reached the adapter');
            return;
        }
        throw new \RuntimeException('Invalid DS record was accepted');
    }

    $valid = ['key_tag' => 0, 'algorithm' => 18, 'digest_type' => 6, 'digest' => str_repeat('A', 64)];
    foreach (['key_tag' => [-1, 65536, '1x', null], 'algorithm' => [0, 256, '18x'],
        'digest_type' => [0, 256, '6x'], 'digest' => ['', str_repeat('G', 64), str_repeat('A', 63)]] as $field => $values) {
        foreach ($values as $value) {
            rejects($service, $order, $domain, $adapter, array_replace($valid, [$field => $value]));
        }
    }
    foreach (array_keys($valid) as $field) {
        $missing = $valid;
        unset($missing[$field]);
        rejects($service, $order, $domain, $adapter, $missing);
    }
    // Do not add a restrictive algorithm allowlist or break valid older records.
    foreach ([['algorithm' => 5, 'digest_type' => 1, 'digest' => str_repeat('A', 40)],
        ['algorithm' => 253, 'digest_type' => 3]] as $legacy) {
        check($service->updateDnssec($order, $domain, ['command' => 'rem'] + array_replace($valid, $legacy))['success'], 'Legacy DS removal failed');
    }
    $adapter->records = [$valid, $valid];
    rejects($service, $order, $domain, $adapter, $valid);
    echo "DNSSEC validation, info and add/remove/replace forwarding checks passed.\n";
}
