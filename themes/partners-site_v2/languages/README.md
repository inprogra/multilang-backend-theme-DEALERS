# WordPress Theme Translations

This directory contains translation files for the partners-site_v2 theme.

## File Structure

```
languages/
├── partners-site_v2.pot          # Template file (generated)
├── partners-site_v2-pl_PL.po     # Polish translations
├── partners-site_v2-pl_PL.mo     # Polish compiled
├── partners-site_v2-en_US.po     # English translations
├── partners-site_v2-en_US.mo     # English compiled
├── partners-site_v2-de_DE.po     # German translations
├── partners-site_v2-de_DE.mo     # German compiled
└── ... (other languages)
```

## Generating Translation Template

Use WP-CLI to generate the .pot file:

```bash
wp i18n make-pot /path/to/theme /path/to/theme/languages/partners-site_v2.pot --domain=partners-site_v2
```

Or use Poedit:
1. Open Poedit
2. Create new translation from PHP sources
3. Select theme directory
4. Save as partners-site_v2.pot

## Creating Language Files

### Using Poedit (Recommended)

1. Open Poedit
2. File → New from POT/PO file
3. Select partners-site_v2.pot
4. Choose target language
5. Translate strings
6. Save as partners-site_v2-{locale}.po
7. Poedit automatically generates .mo file

### Using Command Line

```bash
# Create .po file from template
msginit --input=partners-site_v2.pot --locale=de_DE --output=partners-site_v2-de_DE.po

# Edit .po file with translations
nano partners-site_v2-de_DE.po

# Compile to .mo
msgfmt -o partners-site_v2-de_DE.mo partners-site_v2-de_DE.po
```

## Translation Functions Used in Theme

### Basic Translation
```php
__('Text to translate', 'partners-site_v2')  // Returns translated string
_e('Text to translate', 'partners-site_v2')  // Echoes translated string
```

### With Context
```php
_x('Post', 'noun', 'partners-site_v2')  // Disambiguates same word with different meanings
```

### Plural Forms
```php
_n('%s car', '%s cars', $count, 'partners-site_v2')
```

### Escaped Output
```php
esc_html__('Text', 'partners-site_v2')  // Escaped for HTML
esc_attr__('Text', 'partners-site_v2')  // Escaped for attributes
```

## ACF Field Translation

All ACF field labels and instructions should use translation functions:

```php
'label' => __('Car Model', 'partners-site_v2'),
'instructions' => __('Select the car model from the list', 'partners-site_v2'),
'choices' => array(
    'xc40' => __('XC40', 'partners-site_v2'),
    'xc60' => __('XC60', 'partners-site_v2'),
    'xc90' => __('XC90', 'partners-site_v2'),
),
```

## Common Strings to Translate

### Navigation
- Menu items
- Breadcrumbs
- Page titles

### Forms
- Field labels
- Placeholders
- Validation messages
- Submit buttons
- Success/error messages

### Content
- Headings
- Descriptions
- Call-to-action text
- Footer text

### ACF
- Field group titles
- Field labels
- Field instructions
- Choice labels
- Conditional logic messages

## Testing Translations

1. **Switch WordPress language**:
   ```php
   update_option('WPLANG', 'de_DE');
   ```

2. **Clear cache**:
   ```bash
   wp cache flush
   ```

3. **Verify in browser**:
   - Check all pages
   - Test forms
   - Verify ACF fields in admin

## Updating Translations

### After Code Changes

1. Regenerate .pot file
2. Update .po files with new strings
3. Recompile .mo files
4. Upload to server
5. Clear WordPress cache

### Via Plugin Interface

1. Network Admin → Translations
2. Select site and language
3. Upload new .po/.mo files
4. Changes take effect immediately

## Locale Codes

| Language | Code | WordPress Locale |
|----------|------|------------------|
| Polish | pl | pl_PL |
| English | en | en_US |
| German | de | de_DE |
| French | fr | fr_FR |
| Spanish | es | es_ES |
| Italian | it | it_IT |
| Dutch | nl | nl_NL |
| Swedish | sv | sv_SE |
| Danish | da | da_DK |
| Finnish | fi | fi |
| Norwegian | no | nb_NO |
| Czech | cs | cs_CZ |
| Hungarian | hu | hu_HU |
| Romanian | ro | ro_RO |

## Best Practices

1. **Always use text domain**: 'partners-site_v2'
2. **Use descriptive context**: For ambiguous words
3. **Keep strings complete**: Don't split sentences
4. **Use placeholders**: For dynamic content
5. **Translate alt text**: For images
6. **Translate meta data**: Titles, descriptions
7. **Test with long text**: German translations are typically 30% longer
8. **Version control**: Track .po files in git
9. **Backup before update**: Always backup .mo files
10. **Document custom strings**: Keep list of custom translation keys

## Troubleshooting

### Translations not showing

1. Check .mo file exists and is readable
2. Verify text domain matches
3. Clear WordPress object cache
4. Check WPLANG option in database
5. Verify load_theme_textdomain() is called

### ACF labels not translated

1. Ensure ACF fields use __() function
2. Regenerate .pot file to include ACF strings
3. Update .po files with ACF translations
4. Recompile .mo files

### Mixed languages

1. Check locale setting for current site
2. Verify correct .mo file is loaded
3. Check for hardcoded strings
4. Clear all caches

## Resources

- [WordPress i18n Documentation](https://developer.wordpress.org/apis/handbook/internationalization/)
- [Poedit](https://poedit.net/) - Translation editor
- [WP-CLI i18n](https://developer.wordpress.org/cli/commands/i18n/) - Command line tools
- [GlotPress](https://wordpress.org/plugins/glotpress/) - Collaborative translation platform
