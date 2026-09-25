# Namingo DomainX for FOSSBilling

Secure client access to allowlisted extended registrar operations.

> [!IMPORTANT]
> DomainX is designed exclusively for the Namingo integration described below. Other themes and registrar adapters are not supported.

## Requirements

DomainX requires:

- [Tide FOSSBilling Theme](https://github.com/getnamingo/tide) **v1.2.2 or newer**
- [Namingo FOSSBilling EPP Registrar](https://github.com/getnamingo/fossbilling-epp-registrar) **v1.1.13 or newer**
- The Namingo EPP registrar must be configured and assigned to the domain being managed.
- For DNSSEC, the `secDNS-1.1` EPP extension must be enabled in the registrar configuration.

DomainX will not expose DNSSEC functionality when the supported registrar adapter is unavailable, incompatible, inactive, or not assigned to the domain.

## Installation

Install and configure the required EPP registrar and Tide theme first.

```bash
git clone https://github.com/getnamingo/fossbilling-domainx
mv fossbilling-domainx/Domainx /var/www/modules/
```

- Go to Extensions > Overview in the admin panel and activate "DomainX".
- Ensure Tide v1.2.2 or newer is the active client theme.

## Upgrade

Before upgrading, make a database backup.

To upgrade, replace the existing module files with the latest version and open the FOSSBilling admin panel. The module update routine will ensure that the required database tables exist.

## Usage

DomainX adds allowlisted extended registrar operations to active and paid domain services.

DNSSEC DS record management is supported through the Namingo EPP registrar. DNSSEC options are displayed by the Tide theme only when DomainX confirms that the assigned registrar supports them.

### Glue hostname API (step 1)

DomainX now defines four client API actions for host objects. Each action requires an active domain order owned by the authenticated client, with an allowlisted registrar that opts into glue support for that TLD. The hostname must be inside the purchased domain, such as `ns1.example.com` for `example.com`. IP addresses are validated as IPv4 or IPv6.

| Action | Additional parameters | Result |
| --- | --- | --- |
| `domainx/glue_info` | `hostname` | `hostname`, `ip_addresses` |
| `domainx/glue_create` | `hostname`, `ip_address` | EPP result and hostname |
| `domainx/glue_update` | `hostname`, `current_ip_address` and/or `new_ip_address` | EPP result and hostname |
| `domainx/glue_delete` | `hostname` | EPP result and hostname |

All actions also require `order_id`. An update with only `new_ip_address` adds an address; with only `current_ip_address` it removes an address; with both it replaces an address. Creation accepts one address; add another with `glue_update`. Deleting a host used by a domain can be rejected by the registry. Check `domainx/capabilities` → `features.glue.available` before showing the actions.

The next EPP registrar step must implement the public adapter methods `supportsGlue(Registrar_Domain $domain): bool` and `getGlueHost`, `createGlueHost`, `updateGlueHost`, `deleteGlueHost` (each accepting `Registrar_Domain $domain, array $params` and returning the EPP response array). `supportsGlue` must return true only for TLD profiles where host objects work. The other methods accept EPP-style `hostname`, `ipaddress`, `currentipaddress`, and `newipaddress` keys; `getGlueHost` must return `addr` as an array of IP strings. DomainX leaves glue unavailable until this contract is present. The Tide interface is planned as step 3.

## License

Apache License 2.0
