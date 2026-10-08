# Namingo DomainX for FOSSBilling

Secure client access to allowlisted extended registrar operations, plus bulk domain availability checks.

> [!IMPORTANT]
> The DNSSEC/glue integration requires the supported theme and registrar adapters described below. The FOSSBilling 0.8.8 bulk availability API works with any theme and uses each TLD's assigned registrar adapter.

## Requirements

DomainX requires:

- [Tide FOSSBilling Theme](https://github.com/getnamingo/tide) **v1.2.11 or newer**
- [Namingo FOSSBilling EPP Registrar](https://github.com/getnamingo/fossbilling-epp-registrar) **v1.2.4 or newer**
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
- Ensure Tide v1.2.10 or newer is the active client theme.

## Upgrade

Before upgrading, make a database backup.

To upgrade, replace the existing module files with the latest version and open the FOSSBilling admin panel. The module update routine will ensure that the required database tables exist.

## Usage

### Bulk availability (FOSSBilling 0.8.8)

Call the public guest endpoint `domainx/check_all` with an SLD. Optionally pass `tld` to preserve the explicitly searched suffix:

```javascript
FOSSBilling.api.guest.post('domainx/check_all', { sld: 'example', tld: '.com' }, function (data) {
    data.results.forEach(function (result) {
        // result.available: true = available, false = unavailable, null = check failed.
        // Use result.sld and result.tld with the existing pricing/cart form.
        console.log(result.domain, result.available, result.error);
    });
});
```

The result inside FOSSBilling's normal API response envelope has this shape:

```json
{
  "sld": "example",
  "results": [
    {"sld": "example", "tld": ".com", "domain": "example.com", "available": true, "cached": false, "error": null},
    {"sld": "example", "tld": ".net", "domain": "example.net", "available": false, "cached": true, "error": null},
    {"sld": "example", "tld": ".org", "domain": "example.org", "available": null, "cached": false, "error": "Domain availability could not be determined."}
  ]
}
```

- By default, checks every `active = 1 AND allow_register = 1` TLD in TLD order. An optional `tld` is normalized (including compound and IDN suffixes), selected before checking, and returned first regardless of availability. Unsupported, inactive or non-registerable requested suffixes return an unknown result without a registrar call. With no requested suffix, an empty configuration returns `results: []`.
- `LOOKUP_RESULT_LIMIT = null` near the top of `Domainx/Service.php` means **no result limit**. Set it to a positive integer, for example `10`, to cap both returned results and selected TLDs **before cache reads or registrar calls**. The requested suffix counts toward that cap, including an unsupported-suffix result. Cached results also count; this is separate from the existing live-check rate budgets. There are no admin settings or per-request limit overrides.
- Reuses `Servicedomain::isDomainAvailable`, including registrar configuration validation and test mode. The DNSSEC/glue adapter llowlist does not apply.
- Uses the same `domain_lookup_ip` limiter as `servicedomain/check`, consuming **one lookup per batch**, including cache hits.
- Additional code-owned live-check budgets prevent a batch from multiplying that public allowance: **600 registrar checks per IP per hour** and **600 registrar checks site-wide per minute**, using FOSSBilling's shared rate-limit cache. Cache hits consume neither budget. When a budget runs out, remaining uncached TLDs return `available: null` and a limit message; cache hits are still returned. A rate-limit storage failure prevents live checks. These controls use the same Symfony limiter mechanism as core FOSSBilling; server timeouts and web-server protections still matter for slow adapters and concurrent traffic.
- Continues after individual failures, logging the domain and exception class server-side and returning a generic error. Raw registrar exception messages are not logged by this endpoint. Failed lookups are not cached.
- Uses FOSSBilling's application cache for boolean availability results. In `Domainx/Service.php`, `LOOKUP_CACHE_ENABLED = true` and `LOOKUP_CACHE_TTL = 60` enable caching for **60 seconds**. Set the former to `false` to disable it; no admin settings or request overrides exist. Cache failures fall back to live checks.
- The active TLD list is read each time; reassigning a TLD to a different registrar changes its cache key. Other registrar configuration changes may take up to 60 seconds to appear.
- Checks run sequentially, as with the core API. Large TLD lists may need longer server/registrar timeouts. Availability remains advisory; checkout uses the core live validation.

### Extended operations

DomainX adds allowlisted extended registrar operations to active and paid domain services.

DNSSEC DS record management and glue hostname (EPP host object) management are supported through the Namingo EPP registrar. Tide shows each set of controls only when DomainX reports that feature as available for the domain.

## License

Apache License 2.0