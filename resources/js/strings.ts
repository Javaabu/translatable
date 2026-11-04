import * as React from "react"
import { makeReplacements } from "@javaabu/i18n"
import { useTranslation } from "@javaabu/i18n/react"

import { en, locales, type TranslatableMessageKey } from "./locales"
import { useTranslatable } from "./context"

/** The namespace and group of the package lines, as in PHP: `translatable::strings.language`. */
export const STRINGS_PREFIX = "translatable::strings."

type Replace = Record<string, string | number>

function languageOf(locale: string): string {
  return locale.split(/[-_]/)[0]?.toLowerCase() ?? "en"
}

/**
 * The package strings through `@javaabu/i18n`: a line your app loaded
 * (`lang/vendor/translatable/dv/strings.php`), then the bundled `en`/`dv` line.
 * The locale is i18n's, or the shared current locale when i18n has none.
 */
export function useTranslatableStrings() {
  const { __, has, locale: i18nLocale } = useTranslation()
  const { current } = useTranslatable()
  const locale = i18nLocale || current

  return React.useCallback(
    (key: TranslatableMessageKey, replace: Replace = {}): string => {
      const full = STRINGS_PREFIX + key
      if (has(full, null, false)) return String(__(full, replace))
      const bundled = locales[languageOf(locale)] ?? en
      return makeReplacements(bundled[key] ?? en[key], replace)
    },
    [__, has, locale],
  )
}
