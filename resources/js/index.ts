// The Inertia React components of javaabu/translatable, imported as `@javaabu/translatable`
// through javaabu/manifest. They mirror the Blade components (<x-translatable::…>).

export {
  TranslatableProvider,
  useTranslatable,
  inputName,
  localeInputs,
  type TranslatableProviderProps,
} from "./context"
export { useTranslatableStrings, STRINGS_PREFIX } from "./strings"
export { en, dv, type TranslatableMessages, type TranslatableMessageKey } from "./locales"
export {
  TranslatableField,
  TranslatableText,
  TranslatableTextarea,
  type TranslatableFieldProps,
  type TranslatableTextProps,
} from "./components/TranslatableField"
export { LanguageSwitcher, switchUrl, type LanguageSwitcherProps } from "./components/LanguageSwitcher"
export { TranslationStatus, useStatusDescription, type TranslationStatusProps } from "./components/TranslationStatus"
export {
  TranslationHeadings,
  TranslationCells,
  type TranslationHeadingsProps,
  type TranslationCellsProps,
} from "./components/TranslationTable"
export { TranslationNotice, type TranslationNoticeProps } from "./components/TranslationNotice"
export type { TranslatableLocale, LocaleInput, TranslatableShared, TranslationStatusItem } from "./types"
