# UI components

Every piece of UI is built from **shadcn-vue** components. The whole app should look and behave as one system.

## Rules

1. **shadcn first.** Before writing markup, check whether a shadcn-vue component covers it (`Button`, `Input`, `Textarea`, `Label`, `Dialog`, `AlertDialog`, `Badge`, `Card`, `ScrollArea`, `Separator`, `Sidebar`, `Skeleton`, `Tooltip`, …). If it exists, use it. If it isn't installed yet, add it with the CLI: `sail pnpm dlx shadcn-vue@latest add <name>`.
2. **`components/ui/` is CLI-owned.** Never hand-edit files in `resources/js/components/ui/`. To customise, wrap the component in `resources/js/components/` (e.g. `DocumentDialog.vue` wraps `Dialog`).
3. **No raw HTML controls.** No bare `<button>`, `<input>`, `<textarea>`, `<dialog>` or hand-rolled modals in pages or app components. Use `Button`, `Input`, `Textarea`, `Dialog`.
4. **Theme tokens only.** Colours come from the shadcn neutral theme variables (`bg-background`, `text-muted-foreground`, `border-border`, `bg-primary`, …). No hex values, no `bg-[#…]`, no Tailwind palette colours (`bg-gray-900`, `text-blue-500`).
5. **Variants, not classes.** Change a component's look through its `variant` / `size` props (`<Button variant="ghost" size="sm">`). Extra `class` is for layout only (spacing, width, flex/grid), never for colour, radius or typography overrides.
6. **One way to do each thing:**

   | Need | Use |
   |---|---|
   | Modal | `Dialog` |
   | Destructive confirm | `AlertDialog` |
   | Status / origin label | `Badge` |
   | Form field | `Label` + `Input` / `Textarea` + an error line in `text-destructive text-sm` |
   | Search result / Chunk match | `Card` |
   | Scrolling list or thread | `ScrollArea` |
   | Loading | `Skeleton` or a disabled `Button` while submitting |
   | Icons | `lucide-vue-next` only |

7. **Forms** use Inertia `useForm`. Field errors come from `form.errors.<field>` and are shown under the field. Disable the submit `Button` while `form.processing`.
8. **Accessibility is not optional.** Every input has a `Label`. Icon-only buttons have an `aria-label`. Dialogs have a `DialogTitle` (use `sr-only` if it must be hidden).
9. **Dark-only for now.** Don't add light-mode overrides or a theme toggle.
10. **App components are small and typed.** `<script setup lang="ts">`, `defineProps<…>()` with types from `resources/js/types`. One component per file, PascalCase names. Pages live in `pages/`, shared shells in `layouts/`.

## Adding a new component

1. Search the shadcn-vue registry (`shadcn-vue` skill) and add what exists.
2. Compose it into an app component in `resources/js/components/`.
3. If nothing fits, build from `reka-ui` primitives styled with theme tokens, and note why in the component's docblock.
