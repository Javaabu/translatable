/** Keys match lang/en/strings.php; `:name` placeholders are replaced. */
export const en = {
  field_in_locale: ":label (:locale)",
  language: "Language",
  translation_status: "Translations",
  original_in: "Original: :locale",
  translated_in: "Translated into :locale",
  not_translated_in: "Not translated into :locale",
  add_translation_in: "Add :locale translation",
  edit_translation_in: "Edit :locale translation",
  adding_translation: "You are adding the :locale translation of “:title”.",
}

export type TranslatableMessages = typeof en
export type TranslatableMessageKey = keyof TranslatableMessages
