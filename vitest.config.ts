import { fileURLToPath } from "node:url"
import { defineConfig } from "vitest/config"

export default defineConfig({
  resolve: {
    alias: {
      "@javaabu/translatable": fileURLToPath(new URL("./resources/js", import.meta.url)),
    },
  },
  test: {
    environment: "jsdom",
    globals: true,
    include: ["tests/js/**/*.test.{ts,tsx}"],
    setupFiles: ["tests/js/setup.ts"],
    coverage: {
      provider: "v8",
      include: ["resources/js/**"],
      reporter: ["text", "clover", "lcov"],
    },
  },
})
