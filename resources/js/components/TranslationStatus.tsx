import { Status } from "@javaabu/forms"

import { Icon } from "../icons"
import { useTranslatableStrings } from "../strings"
import type { TranslationStatusItem } from "../types"

export interface TranslationStatusProps {
  /** `Translatable::status($model, $url)` from the server. */
  status: TranslationStatusItem[]
  className?: string
}

/** The accessible description of a status entry: "Not translated into ދިވެހި". */
export function useStatusDescription() {
  const t = useTranslatableStrings()

  return (item: TranslationStatusItem) => {
    const locale = item.language.native_name
    if (item.primary) return t("original_in", { locale })
    return item.translated ? t("translated_in", { locale }) : t("not_translated_in", { locale })
  }
}

/** `<x-translatable::translation-status>`: a badge per language, linking to add or edit the translation. */
export function TranslationStatus({ status, className }: TranslationStatusProps) {
  const t = useTranslatableStrings()
  const describe = useStatusDescription()

  if (status.length === 0) return null

  return (
    <ul
      className={["translation-status flex list-none flex-wrap items-center gap-1 p-0", className]
        .filter(Boolean)
        .join(" ")}
      aria-label={t("translation_status")}
      data-translatable="translation-status"
    >
      {status.map((item) => {
        const badge = (
          <Status color={item.translated ? "success" : "secondary"}>
            <Icon name={item.translated ? "check" : "plus"} />
            <span lang={item.language.locale}>{item.language.native_name}</span>
          </Status>
        )

        return (
          <li key={item.locale} data-locale={item.locale} data-translated={item.translated ? "true" : "false"}>
            {item.url ? (
              <a href={item.url} title={describe(item)} aria-label={describe(item)}>
                {badge}
              </a>
            ) : (
              <span title={describe(item)}>{badge}</span>
            )}
          </li>
        )
      })}
    </ul>
  )
}
