# WP CLI Widget Importer

Based on code by [Gkurtyka Schibsted](https://github.com/gkurtyka-schibsted/wp-cli/commit/c4fe2facc9ee202e037538aea5bbbd7d98cb5182), this adds a new set of commands for importing and exporting sidebar data.

## Usage

```
wp sidebars export > sidebars.tmp

cat sidebars.tmp | wp sidebars import
```