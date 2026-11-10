import { NavTabs } from "@javaabu/forms"

import { useTranslatable } from "../context"
import type { TranslatableLocale } from "../types"

export interface LanguageSwitcherProps {
  /** The link for a language. Default: the current URL with `?lang=` (read by the `language` middleware). */
  url?: (locale: TranslatableLocale) => string
  className?: string
}

/** The current URL with `?lang=code`. */
export function switchUrl(locale: string): string {
  if (typeof window === "undefined") return `?lang=${encodeURIComponent(locale)}`
  const url = new URL(window.location.href)
  url.searchParams.set("lang", locale)
  return url.toString()
}

/** `<x-translatable::language-switcher>`: a link per active language. Hidden with fewer than two. */
export function LanguageSwitcher({ url = (locale) => switchUrl(locale.code), className }: LanguageSwitcherProps) {
  const { locales, current } = useTranslatable()

  if (locales.length < 2) return null

  return (
    <div className={["language-switcher", className].filter(Boolean).join(" ")} data-translatable="language-switcher">
      <NavTabs
        tabs={locales.map((locale) => ({
          title: <span lang={locale.locale}>{locale.native_name}</span>,
          url: url(locale),
          active: locale.code === current,
        }))}
      />
    </div>
  )
}
