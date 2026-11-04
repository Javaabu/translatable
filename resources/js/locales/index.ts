import { dv } from "./dv"
import { en, type TranslatableMessageKey, type TranslatableMessages } from "./en"

export const locales: Record<string, TranslatableMessages> = { en, dv }

export { en, dv, type TranslatableMessages, type TranslatableMessageKey }
