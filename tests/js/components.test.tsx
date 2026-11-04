import { render, screen, within } from "@testing-library/react"
import { addLines, setLocale } from "@javaabu/i18n"
import {
  LanguageSwitcher,
  switchUrl,
  TranslatableProvider,
  TranslationCells,
  TranslationHeadings,
  TranslationNotice,
  TranslationStatus,
  useTranslatable,
} from "@javaabu/translatable"

import { english, shared, status } from "./fixtures"

afterEach(() => setLocale("en"))

describe("LanguageSwitcher", () => {
  it("links to the current page in each language", () => {
    window.history.replaceState(null, "", "/dv/about?tab=2")

    render(
      <TranslatableProvider value={shared({ current: "dv" })}>
        <LanguageSwitcher />
      </TranslatableProvider>,
    )

    const links = screen.getAllByRole("link")
    expect(links.map((link) => link.textContent)).toEqual(["English", "ދިވެހި"])
    expect(links[0]).toHaveAttribute("href", "http://localhost:3000/dv/about?tab=2&lang=en")
    expect(links[1]).toHaveAttribute("aria-current", "page")
    expect(within(links[1]!).getByText("ދިވެހި")).toHaveAttribute("lang", "dv")
  })

  it("takes the links from a callback", () => {
    render(
      <TranslatableProvider value={shared()}>
        <LanguageSwitcher url={(locale) => `/${locale.code}/posts/1`} />
      </TranslatableProvider>,
    )

    expect(screen.getAllByRole("link").map((link) => link.getAttribute("href"))).toEqual(["/en/posts/1", "/dv/posts/1"])
  })

  it("is hidden with one language", () => {
    const { container } = render(
      <TranslatableProvider value={shared({ locales: [english] })}>
        <LanguageSwitcher />
      </TranslatableProvider>,
    )

    expect(container).toBeEmptyDOMElement()
  })

  it("builds a query string link", () => {
    window.history.replaceState(null, "", "/contact")

    expect(switchUrl("dv")).toBe("http://localhost:3000/contact?lang=dv")
  })
})

describe("TranslationStatus", () => {
  it("shows a badge per language linking to add or edit", () => {
    render(<TranslationStatus status={status} />)

    const list = screen.getByRole("list", { name: "Translations" })
    const items = within(list).getAllByRole("listitem")

    expect(items.map((item) => item.dataset.translated)).toEqual(["true", "false"])
    expect(within(items[1]!).getByRole("link")).toHaveAttribute("href", "/dv/posts/create?lang_parent=1")
    expect(within(items[1]!).getByRole("link")).toHaveAccessibleName("Not translated into ދިވެހި")
    expect(within(items[0]!).getByRole("link")).toHaveAccessibleName("Original: English")
  })

  it("speaks Dhivehi and shows entries without links", () => {
    setLocale("dv")

    render(<TranslationStatus status={status.map((item) => ({ ...item, url: null }))} />)

    expect(screen.getByRole("list", { name: "ތަރުޖަމާތައް" })).toBeInTheDocument()
    expect(screen.queryByRole("link")).not.toBeInTheDocument()
    expect(screen.getByTitle("ދިވެހި އަށް ތަރުޖަމާކުރެވިފައެއް ނުވޭ")).toBeInTheDocument()
  })

  it("renders nothing without entries", () => {
    const { container } = render(<TranslationStatus status={[]} />)

    expect(container).toBeEmptyDOMElement()
  })

  it("uses lines the app loaded", () => {
    addLines({ "strings.translation_status": "Languages" }, "en", "translatable")

    render(<TranslationStatus status={status} />)

    expect(screen.getByRole("list", { name: "Languages" })).toBeInTheDocument()
  })
})

describe("TranslationHeadings and TranslationCells", () => {
  it("render the other languages with add and edit links", () => {
    render(
      <TranslatableProvider value={shared()}>
        <table>
          <thead>
            <tr>
              <TranslationHeadings />
            </tr>
          </thead>
          <tbody>
            <tr>
              <TranslationCells status={status} />
            </tr>
            <tr>
              <TranslationCells status={status.map((item) => ({ ...item, translated: true, url: null }))} />
            </tr>
          </tbody>
        </table>
      </TranslatableProvider>,
    )

    expect(screen.getAllByRole("columnheader").map((th) => th.textContent)).toEqual(["ދިވެހި"])
    expect(screen.getByRole("columnheader")).toHaveAttribute("lang", "dv")
    expect(screen.getByRole("link", { name: "Add ދިވެހި translation" })).toHaveAttribute(
      "href",
      "/dv/posts/create?lang_parent=1",
    )
    expect(screen.getByTitle("Edit ދިވެހި translation")).toHaveAttribute("data-translated", "true")
  })
})

describe("TranslationNotice", () => {
  it("names the original and adds the hidden inputs", () => {
    setLocale("dv")

    const { container } = render(
      <TranslatableProvider value={shared({ current: "dv" })}>
        <TranslationNotice parentId={7} parentTitle="Welcome" />
      </TranslatableProvider>,
    )

    expect(screen.getByText("“Welcome” ގެ ދިވެހި ތަރުޖަމާ އިތުރުކުރަނީ.")).toBeInTheDocument()
    expect(container.querySelector('input[name="lang"]')).toHaveValue("dv")
    expect(container.querySelector('input[name="lang_parent"]')).toHaveValue("7")
  })
})

describe("useTranslatable", () => {
  it("is disabled outside a provider", () => {
    function Probe() {
      return <span>{String(useTranslatable().enabled)}</span>
    }

    render(<Probe />)

    expect(screen.getByText("false")).toBeInTheDocument()
  })
})
