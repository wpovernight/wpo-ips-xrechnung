# PDF Invoices & Packing Slips for WooCommerce - XRechnung

This extension adds EN16931 XRechnung (UBL Invoice) support to our main [PDF Invoices & Packing Slips for WooCommerce](https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/) plugin.

## Requirements for Valid XML

For the generated XML file to be valid and compliant, certain data points need to be set.

### WooCommerce Store Data

- Email from address
- Store Address
- Store City
- Store Postcode
- Default Country
- **Note:** To avoid validation issues, ensure that **rounding is disabled** under **WooCommerce > Settings > Tax**.

### PDF Invoices & Packing Slips for WooCommerce General Data

- Shop Name
- Shop Address
- Shop VAT Number
- Shop Phone Number

### Customer Data

- Company Name
- Company VAT Number
- Other default customer data

#### Supported Customer VAT Number Plugins

- WooCommerce EU VAT Number
- WooCommerce EU VAT Compliance
- Aelia EU VAT Assistant
- EU VAT Number for WooCommerce (WP Whale / former Algoritmika)
- YITH WooCommerce EU VAT
- German Market
- Germanized Pro

If you need support for another plugin, feel free to [contact us](https://wpovernight.com/contact/).

## Validation

You can validate the generated XML file using the [XRechnung Validator](https://erechnungsvalidator.service-bw.de/).