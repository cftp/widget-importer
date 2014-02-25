# WP CLI Widget Importer

Based on code by [Gkurtyka Schibsted](https://github.com/gkurtyka-schibsted/wp-cli/commit/c4fe2facc9ee202e037538aea5bbbd7d98cb5182), this adds a new set of commadns for importing and exporting sidebar data.

## Usage

```
wp widget export_sidebars > widgets.json

cat widgets.json | wp widget import_sidebars
```