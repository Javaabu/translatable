import * as React from "react"
import {
  errorsFor,
  Input,
  selectErrorBag,
  Tabs,
  Textarea,
  useFormsConfig,
  useFormState,
  type InputProps,
  type TextareaProps,
} from "@javaabu/forms"

import { localeInputs, useTranslatable } from "../context"
import { useTranslatableStrings } from "../strings"
import type { LocaleInput } from "../types"

type SharedFieldProps = Omit<InputProps, "name" | "label" | "type" | "value"> &
  Pick<TextareaProps, "rows" | "autoResize">

export interface TranslatableFieldProps extends SharedFieldProps {
  /** The field: `title` gives `title_en`, `title_dv`, … */
  name: string
  label?: string
  /** `text` (default), `textarea`, or any `Input` type. */
  type?: string
  /** `Translatable::inputs()` from the server. Default: built from the shared languages. */
  inputs?: LocaleInput[] | null
  /** The model's `lang`, when `inputs` isn't given. Default: the current locale. */
  primary?: string
  /** Require every locale, not only the primary one. */
  requireAll?: boolean
  /** `tabs` (default) or `stacked`. */
  layout?: "tabs" | "stacked"
  className?: string
}

/** The names of the inputs that have errors, from the surrounding `Form` or `FormsProvider errors`. */
function useInputsWithErrors(inputs: LocaleInput[]): Set<string> {
  const form = useFormState()
  const { errors: provided, router } = useFormsConfig()
  const page = router?.usePageErrors?.()
  const errors = form ? form.errors : selectErrorBag(page ?? provided)

  return new Set(inputs.filter((input) => errorsFor(errors, input.name).length > 0).map((input) => input.name))
}

/**
 * `<x-translatable::field>`: one `@javaabu/forms` field per locale, in tabs or stacked.
 * Without translations (no provider, translations off, or no inputs) it renders a plain field.
 */
export function TranslatableField({
  name,
  label,
  type = "text",
  inputs: given,
  primary,
  requireAll = false,
  layout = "tabs",
  required,
  className,
  ...props
}: TranslatableFieldProps) {
  const shared = useTranslatable()
  const t = useTranslatableStrings()
  const inputs = given ?? (shared.enabled ? localeInputs(name, shared.locales, primary ?? shared.current) : [])
  const withErrors = useInputsWithErrors(inputs)

  const field = (
    fieldName: string,
    fieldLabel: string | undefined,
    extra: Record<string, unknown>,
    isRequired?: boolean,
  ) =>
    type === "textarea" ? (
      <Textarea {...(props as TextareaProps)} {...extra} name={fieldName} label={fieldLabel} required={isRequired} />
    ) : (
      <Input {...props} {...extra} type={type} name={fieldName} label={fieldLabel} required={isRequired} />
    )

  if (inputs.length === 0) {
    return field(name, label, {}, required)
  }

  const render = (input: LocaleInput) =>
    field(
      input.name,
      t("field_in_locale", { label: label ?? humanize(name), locale: input.language.native_name }),
      input.thaana ? { thaana: true } : { dir: input.direction, lang: input.language.locale },
      Boolean(required) && (input.primary || requireAll),
    )

  if (layout === "stacked") {
    return (
      <div className={["translatable-field", className].filter(Boolean).join(" ")} data-translatable-field={name}>
        {inputs.map((input) => (
          <React.Fragment key={input.name}>{render(input)}</React.Fragment>
        ))}
      </div>
    )
  }

  const tabName = (input: LocaleInput) => `${name}-${input.locale}`
  const active = inputs.find((input) => withErrors.has(input.name)) ?? inputs[0]

  return (
    <div className={["translatable-field", className].filter(Boolean).join(" ")} data-translatable-field={name}>
      <Tabs
        active={active ? tabName(active) : undefined}
        tabs={inputs.map((input) => ({
          name: tabName(input),
          title: <span lang={input.language.locale}>{input.language.native_name}</span>,
          content: render(input),
        }))}
      />
    </div>
  )
}

/** `title_text` → `Title text`, like the generated labels of javaabu/forms. */
function humanize(name: string): string {
  const words = name.replace(/[_-]+/g, " ").trim()
  return words.charAt(0).toUpperCase() + words.slice(1)
}

export type TranslatableTextProps = Omit<TranslatableFieldProps, "type">

/** `<x-translatable::text>` */
export function TranslatableText(props: TranslatableTextProps) {
  return <TranslatableField {...props} type="text" />
}

/** `<x-translatable::textarea>` */
export function TranslatableTextarea(props: TranslatableTextProps) {
  return <TranslatableField {...props} type="textarea" />
}
