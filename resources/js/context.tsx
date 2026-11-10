import * as React from "react"

import type { LocaleInput, TranslatableLocale, TranslatableShared } from "./types"

const EMPTY: TranslatableShared = { enabled: false, current: "en", default: "en", direction: "ltr", locales: [] }

const TranslatableContext = React.createContext<TranslatableShared | null>(null)

export interface TranslatableProviderProps {
  /** `Translatable::shared()`; with Inertia, `usePage().props.translatable`. */
  value: TranslatableShared | null | undefined
  children?: React.ReactNode
}

/**
 * Gives the components the languages. Put it in your layout:
 * `<TranslatableProvider value={usePage().props.translatable}>`.
 */
export function TranslatableProvider({ value, children }: TranslatableProviderProps) {
  return <TranslatableContext.Provider value={value ?? EMPTY}>{children}</TranslatableContext.Provider>
}

/** The shared languages, or "disabled" outside a `TranslatableProvider`. */
export function useTranslatable(): TranslatableShared {
  return React.useContext(TranslatableContext) ?? EMPTY
}

/** `Translatable::inputName()`: `title_dv`. */
export function inputName(field: string, locale: string): string {
  return `${field}_${locale}`
}

/**
 * `Translatable::inputs()` on the client: one input per locale, the primary first.
 * The primary locale is the model's `lang`; for a new model, the current locale.
 */
export function localeInputs(field: string, locales: TranslatableLocale[], primary: string): LocaleInput[] {
  const inputs = locales.map((language) => ({
    field,
    name: inputName(field, language.code),
    locale: language.code,
    direction: language.direction,
    thaana: language.thaana,
    primary: language.code === primary,
    language,
  }))

  return [...inputs.filter((input) => input.primary), ...inputs.filter((input) => !input.primary)]
}
