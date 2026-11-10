import { Alert } from "@javaabu/forms"

import { useTranslatable } from "../context"
import { useTranslatableStrings } from "../strings"

export interface TranslationNoticeProps {
  /** The default translation's key (`lang_parent`). */
  parentId: string | number
  /** Its title, shown in the message. */
  parentTitle: string
  /** The locale being added. Default: the current locale. */
  locale?: string
  className?: string
}

/** `<x-translatable::translation-notice>`: adding a translation of a DB translatable model. */
export function TranslationNotice({ parentId, parentTitle, locale, className }: TranslationNoticeProps) {
  const { locales, current } = useTranslatable()
  const t = useTranslatableStrings()
  const code = locale ?? current
  const language = locales.find((item) => item.code === code)

  return (
    <>
      <div className={className} data-translatable="translation-notice">
        <Alert type="info">
          {t("adding_translation", { locale: language?.native_name ?? code, title: parentTitle })}
        </Alert>
      </div>
      <input type="hidden" name="lang" value={code} />
      <input type="hidden" name="lang_parent" value={String(parentId)} />
    </>
  )
}
