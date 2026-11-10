import type { TranslatableLocale, TranslatableShared, TranslationStatusItem } from "@javaabu/translatable"

export const english: TranslatableLocale = {
  code: "en",
  locale: "en",
  name: "English",
  native_name: "English",
  direction: "ltr",
  rtl: false,
  thaana: false,
  default: true,
  flag_url: null,
}

export const dhivehi: TranslatableLocale = {
  code: "dv",
  locale: "dv",
  name: "Dhivehi",
  native_name: "ދިވެހި",
  direction: "rtl",
  rtl: true,
  thaana: true,
  default: false,
  flag_url: null,
}

export const arabic: TranslatableLocale = {
  code: "ar",
  locale: "ar",
  name: "Arabic",
  native_name: "العربية",
  direction: "rtl",
  rtl: true,
  thaana: false,
  default: false,
  flag_url: null,
}

export function shared(overrides: Partial<TranslatableShared> = {}): TranslatableShared {
  return { enabled: true, current: "en", default: "en", direction: "ltr", locales: [english, dhivehi], ...overrides }
}

export const status: TranslationStatusItem[] = [
  { locale: "en", translated: true, primary: true, url: "/en/posts/1/edit", language: english },
  { locale: "dv", translated: false, primary: false, url: "/dv/posts/create?lang_parent=1", language: dhivehi },
]
