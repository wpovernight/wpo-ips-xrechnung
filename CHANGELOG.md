# Changelog

### v1.0.4 (2025-02-06)
- Tweak: Update root element to include URN
- Fix: incorrect addition of <AccountingSupplierParty> and <AccountingCustomerParty> elements

### v1.0.3 (2025-01-20)

- New: GitHub Updater
- New: Apply sanitization function `wpo_ips_ubl_sanitize_string()` to some strings
- New: Support for Tax Category Reason
- New: Enable support for `cac:PaymentTerms`
- Tweak: Pass order object to `wpo_ips_ubl_get_tax_data_from_fallback()` function
- Tweak: Default item tax handling in `TaxTotalHandler`
- Tweak: Utilize the default `BuyerReferenceHandler` from UBL
- Tweak: Utilize the default `PaymentMeansHandler` from UBL
- Tweak: Set due date to match order paid date for paid orders
- Fix: `InvoiceLine` tax validation issues
- Fix: Bug on `$taxOrderData` not being passed to the `cac:ClassifiedTaxCategory`
- Fix: PayPal transaction meta key

### v1.0.2 (2024-12-17)

- Fix: Invalid XML entities in item title by ensuring UTF-8 compliance

### v1.0.1 (2024-12-17)

- Fix: `paypal` and `stripe` payment codes
- Fix: Includes `cod` payment method code
- Fix: Customer PartyTaxScheme being included while empty
- Fix: Shop country code

### v1.0.0 (2024-12-16)

- First release
