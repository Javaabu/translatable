import { render, screen } from "@testing-library/react"
import userEvent from "@testing-library/user-event"
import { Form, FormsProvider } from "@javaabu/forms"
import {
  localeInputs,
  TranslatableField,
  TranslatableProvider,
  TranslatableText,
  TranslatableTextarea,
} from "@javaabu/translatable"

import { arabic, dhivehi, english, shared } from "./fixtures"

describe("TranslatableField", () => {
  it("renders a tab per locale with the bound values", async () => {
    render(
      <TranslatableProvider value={shared()}>
        <Form model={{ title_en: "Welcome", title_dv: "މަރުޙަބާ" }}>
          <TranslatableText name="title" label="Title" required />
        </Form>
      </TranslatableProvider>,
    )

    expect(screen.getAllByRole("tab").map((tab) => tab.textContent)).toEqual(["English", "ދިވެހި"])
    expect(screen.getByLabelText(/Title \(English\)/)).toHaveValue("Welcome")
    expect(screen.getByLabelText(/Title \(English\)/)).toBeRequired()

    await userEvent.click(screen.getByRole("tab", { name: "ދިވެހި" }))

    const dhivehiInput = screen.getByLabelText(/Title \(ދިވެހި\)/)
    expect(dhivehiInput).toHaveValue("މަރުޙަބާ")
    expect(dhivehiInput).toHaveAttribute("name", "title_dv")
    expect(dhivehiInput).toHaveAttribute("dir", "rtl")
    expect(dhivehiInput).not.toBeRequired()
  })

  it("uses the inputs from the server, primary first", () => {
    render(
      <TranslatableProvider value={shared()}>
        <TranslatableTextarea name="body" inputs={localeInputs("body", [english, dhivehi], "dv")} layout="stacked" />
      </TranslatableProvider>,
    )

    const fields = screen.getAllByRole("textbox")
    expect(fields.map((field) => field.getAttribute("name"))).toEqual(["body_dv", "body_en"])
    expect(fields[0]?.tagName).toBe("TEXTAREA")
  })

  it("requires every locale with requireAll", () => {
    render(
      <TranslatableProvider value={shared()}>
        <TranslatableField name="title" layout="stacked" required requireAll />
      </TranslatableProvider>,
    )

    expect(screen.getAllByRole("textbox").every((field) => field.hasAttribute("required"))).toBe(true)
  })

  it("gives rtl languages without Thaana their direction and language", () => {
    render(
      <TranslatableProvider value={shared({ locales: [english, dhivehi, arabic] })}>
        <TranslatableField name="title" layout="stacked" />
      </TranslatableProvider>,
    )

    const arabicInput = screen.getByLabelText(/العربية/)
    expect(arabicInput).toHaveAttribute("dir", "rtl")
    expect(arabicInput).toHaveAttribute("lang", "ar")
    expect(screen.getByLabelText(/English/)).toHaveAttribute("dir", "ltr")
  })

  it("opens the first tab with an error", () => {
    render(
      <FormsProvider errors={{ title_dv: ["ސުރުޚީ ލިޔުއްވާ"] }}>
        <TranslatableProvider value={shared()}>
          <TranslatableText name="title" />
        </TranslatableProvider>
      </FormsProvider>,
    )

    expect(screen.getByRole("tab", { name: "ދިވެހި" })).toHaveAttribute("aria-selected", "true")
    expect(screen.getByText("ސުރުޚީ ލިޔުއްވާ")).toBeInTheDocument()
  })

  it("renders a plain field without translations", () => {
    const { rerender } = render(<TranslatableText name="title" label="Title" />)

    expect(screen.getByLabelText("Title")).toHaveAttribute("name", "title")
    expect(screen.queryByRole("tab")).not.toBeInTheDocument()

    rerender(
      <TranslatableProvider value={shared({ enabled: false })}>
        <TranslatableText name="title" />
      </TranslatableProvider>,
    )

    expect(screen.getByRole("textbox")).toHaveAttribute("name", "title")
  })
})

describe("localeInputs", () => {
  it("builds the inputs like Translatable::inputs()", () => {
    expect(localeInputs("title", [english, dhivehi], "dv")).toEqual([
      {
        field: "title",
        name: "title_dv",
        locale: "dv",
        direction: "rtl",
        thaana: true,
        primary: true,
        language: dhivehi,
      },
      {
        field: "title",
        name: "title_en",
        locale: "en",
        direction: "ltr",
        thaana: false,
        primary: false,
        language: english,
      },
    ])
  })
})
