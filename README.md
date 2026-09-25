# Namingo DomainX for FOSSBilling

Secure client access to allowlisted extended registrar operations.

> [!IMPORTANT]
> DomainX is designed exclusively for the Namingo integration described below. Other themes and registrar adapters are not supported.

## Requirements

DomainX requires:

- [Tide FOSSBilling Theme](https://github.com/getnamingo/tide) **v1.2.2 or newer**
- [Namingo FOSSBilling EPP Registrar](https://github.com/getnamingo/fossbilling-epp-registrar) **v1.2.2 or newer**
- The Namingo EPP registrar must be configured and assigned to the domain being managed.
- For DNSSEC, the `secDNS-1.1` EPP extension must be enabled in the registrar configuration.
- For glue hostname management, the registrar must use EPP host objects (hostObj). The generic EPP profile must also include urn:ietf:params:xml:ns:host-1.0 in its login objects.

DomainX exposes each feature only when the assigned registrar adapter supports it for the domain. Glue hostname operations are unavailable for registries using hostAttr.

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

DNSSEC DS record management and glue hostname (EPP host object) management are supported through the Namingo EPP registrar. Tide shows each set of controls only when DomainX reports that feature as available for the domain.

## License

Apache License 2.0