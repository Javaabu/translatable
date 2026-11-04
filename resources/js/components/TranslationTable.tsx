import { TableCell, TableHeading } from "@javaabu/forms"

import { useTranslatable } from "../context"
import { Icon } from "../icons"
import { useTranslatableStrings } from "../strings"
import type { TranslationStatusItem } from "../types"

export interface TranslationHeadingsProps {
  className?: string
}

/** `<x-translatable::titles>`: a heading per language except the current one. */
export function TranslationHeadings({ className }: TranslationHeadingsProps) {
  const { locales, current } = useTranslatable()

  return (
    <>
      {locales
        .filter((locale) => locale.code !== current)
        .map((locale) => (
          <TableHeading
            key={locale.code}
            className={["text-center", className].filter(Boolean).join(" ")}
            lang={locale.locale}
            data-locale={locale.code}
          >
            {locale.native_name}
          </TableHeading>
        ))}
    </>
  )
}

export interface TranslationCellsProps {
  /** `Translatable::status($model, $url)` for the row's model. */
  status: TranslationStatusItem[]
}

/** `<x-translatable::table-cells>`: a cell per language except the current one, linking to add or edit. */
export function TranslationCells({ status }: TranslationCellsProps) {
  const { current } = useTranslatable()
  const t = useTranslatableStrings()

  return (
    <>
      {status
        .filter((item) => item.locale !== current)
        .map((item) => {
          const title = t(item.translated ? "edit_translation_in" : "add_translation_in", {
            locale: item.language.native_name,
          })

          return (
            <TableCell key={item.locale} data-locale={item.locale}>
              <div className="flex items-center justify-center gap-1">
                {item.url ? (
                  <a href={item.url} title={title} aria-label={title} data-translated={String(item.translated)}>
                    <Icon name={item.translated ? "pencil" : "plus"} />
                  </a>
                ) : (
                  <span title={title} data-translated={String(item.translated)}>
                    <Icon name={item.translated ? "check" : "plus"} />
                  </span>
                )}
              </div>
            </TableCell>
          )
        })}
    </>
  )
}
