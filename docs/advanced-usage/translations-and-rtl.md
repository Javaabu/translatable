---
title: Dhivehi, RTL and the package's strings
sidebar_position: 3
---

# The package's strings

Every string the views show is in `lang/en/strings.php` and `lang/dv/strings.php` (`translatable::strings.*`), and bundled for React in `resources/js/locales`. A test keeps the PHP and JS lines identical.

Override a line in your app:

```php
// lang/vendor/translatable/dv/strings.php
return [
    'translation_status' => 'ބަސްތައް',
];
```

For React, load your lines into @javaabu/i18n (for example with javaabu/js-localization); lines under `translatable::strings.*` win over the bundled ones.

# Right to left

- Every language has a direction (`is_rtl`). Inputs get their language's `dir` and `lang`, so a Dhivehi input is right to left on an English page and an English input is left to right on a Dhivehi page.
- The views use logical layout only (`gap`, `start`/`end`); nothing is placed left or right.
- `Languages::getDirection()`, `locale_direction()` and `$model->locale_direction` give the direction for your layout's `<html dir>`.

# Thaana

Dhivehi inputs (`translatable.thaana_locales`) get `dir="rtl" lang="dv" data-thaana`, which javaabu/forms' script turns into the Thaana keyboard: typing on a Latin keyboard enters Thaana (phonetic layout). In React, the inputs get `@javaabu/forms`' `thaana` prop.

# Dhivehi lines to review

These were translated by the package authors and should be checked by a Dhivehi speaker:

| Key | English | Dhivehi |
| --- | --- | --- |
| `language` | Language | ބަސް |
| `translation_status` | Translations | ތަރުޖަމާތައް |
| `original_in` | Original: :locale | އަސްލު: :locale |
| `translated_in` | Translated into :locale | :locale އަށް ތަރުޖަމާކުރެވިފައި |
| `not_translated_in` | Not translated into :locale | :locale އަށް ތަރުޖަމާކުރެވިފައެއް ނުވޭ |
| `add_translation_in` | Add :locale translation | :locale ތަރުޖަމާ އިތުރުކުރުން |
| `edit_translation_in` | Edit :locale translation | :locale ތަރުޖަމާ ބަދަލުކުރުން |
| `adding_translation` | You are adding the :locale translation of “:title”. | “:title” ގެ :locale ތަރުޖަމާ އިތުރުކުރަނީ. |

`field_in_locale` (":label (:locale)") is the same in both.
