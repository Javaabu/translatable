/** A language, as `Locale::toArray()` gives it. */
export interface TranslatableLocale {
  code: string
  /** The HTML `lang`. */
  locale: string
  name: string
  /** The language's own name: ދިވެހި */
  native_name: string
  direction: "ltr" | "rtl"
  rtl: boolean
  /** Free-text inputs use the Thaana keyboard. */
  thaana: boolean
  default: boolean
  flag_url: string | null
}

/** One locale's input for a translatable field, as `LocaleInput::toArray()` gives it. */
export interface LocaleInput {
  field: string
  /** `title_dv` */
  name: string
  locale: string
  direction: "ltr" | "rtl"
  thaana: boolean
  /** The model's own locale (its `lang`); required fields are only required here. */
  primary: boolean
  language: TranslatableLocale
}

/** `Translatable::shared()`, shared with Inertia pages as the `translatable` prop. */
export interface TranslatableShared {
  enabled: boolean
  current: string
  default: string
  direction: "ltr" | "rtl"
  locales: TranslatableLocale[]
}

/** One entry of `Translatable::status($model, $url)`. */
export interface TranslationStatusItem {
  locale: string
  translated: boolean
  primary: boolean
  url: string | null
  language: TranslatableLocale
}
