# Namingo DomainX for FOSSBilling

Secure client access to allowlisted extended registrar operations.

> [!IMPORTANT]
> DomainX is designed exclusively for the Namingo integration described below. Other themes and registrar adapters are not supported.

## Requirements

DomainX requires:

- [Tide FOSSBilling Theme](https://github.com/getnamingo/tide) **v1.2.2 or newer**
- [Namingo FOSSBilling EPP Registrar](https://github.com/getnamingo/fossbilling-epp-registrar) **v1.1.13 or newer**
- The Namingo EPP registrar must be configured and assigned to the domain being managed.
- The `secDNS-1.1` EPP extension must be enabled in the registrar configuration.

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

The currently supported operation is DNSSEC DS record management through the Namingo EPP registrar. DNSSEC options are displayed by the Tide theme only when DomainX confirms that the assigned registrar supports them.

## License

Apache License 2.0