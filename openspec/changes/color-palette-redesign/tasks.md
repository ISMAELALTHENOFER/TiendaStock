# Tasks: Color Palette Redesign

## Review Workload Forecast

- **Estimated changed lines**: ~300-400 (across ~23 files)
- **400-line budget risk**: Low — scope is well within single-PR threshold
- **Chained PRs recommended**: No
- **Decision needed before apply**: No

## Tasks

### Task 1: Tailwind Config — Add brand/sky/blush colors, remove purple/pink

**Files**:
- `src/tailwind.config.js`

**Changes**:
1. Add `brand` color object with `DEFAULT: '#FFD16D'`, `light: '#FFE8B6'`, `dark: '#C8963A'`
2. Add `sky` color object with `DEFAULT: '#AAD6E2'`, `light: '#D5EBF0'`, `dark: '#6DB5C8'`
3. Add `blush` color object with `DEFAULT: '#FCD2DF'`, `light: '#FEE9EF'`, `dark: '#E6A3B8'`
4. Remove entire `purple: { ... }` block
5. Remove entire `pink: { ... }` block
6. Remove `gradientColorStops` object (purple-start, pink-end)
7. Replace `boxShadow`: rename `purple-glow` → `brand-glow` (hex #FFD16D), rename `pink-glow` → `sky-glow` (hex #AAD6E2)

**Verification**:
- Tailwind config parses without error: `npx tailwindcss --help` (compile check)
- No references to `purple-*`, `pink-*` remain in config
- New color keys resolve: brand.DEFAULT, brand.light, brand.dark, etc.

**Dependencies**: None (foundation for all other tasks)

---

### Task 2: Layout Files — app, guest, sidebar, topbar

**Files**:
- `resources/views/layouts/app.blade.php` — 1 match
- `resources/views/layouts/guest.blade.php` — 4 matches
- `resources/views/layouts/sidebar.blade.php` — 36 matches
- `resources/views/layouts/topbar.blade.php` — 7 matches

**Changes**:
- **app.blade.php**: `to-purple-50` → `to-sky-light`
- **guest.blade.php**: gradient `from-purple-50 via-white to-pink-50` → `from-sky-light via-white to-blush`; logo `text-purple-600` → `text-brand`; border `border-purple-100` → `border-sky-light`
- **sidebar.blade.php**: Sidebar bg `from-purple-900 to-purple-800` → `from-brand-dark to-brand-dark`; nav item active states `from-purple-700 to-pink-600` → `from-brand-dark to-sky`; nav text `text-purple-200` → `text-white/70`; hover states `hover:bg-purple-700` → `hover:bg-brand-dark`; submenu bg `from-purple-800 to-purple-700` → `from-brand-dark to-brand-dark`; submenu text `text-purple-300` → `text-white/50`
- **topbar.blade.php**: border `border-purple-100` → `border-sky-light`; hover/text `hover:text-purple-600 hover:bg-purple-50 focus:ring-purple-500` → `hover:text-brand hover:bg-brand-light focus:ring-brand`; dropdown btn gradient `from-purple-600 to-pink-500` → `from-brand to-sky`; ring `focus:ring-purple-500` → `focus:ring-brand`

**Verification**:
- Every layout renders without Tailwind class errors
- Sidebar shows gold accent on active nav, sky on hover gradients
- Guest layout has warm sky/blush gradient background

**Dependencies**: Task 1 (config)

---

### Task 3: Components — buttons, inputs, nav-links, alert

**Files**:
- `resources/views/components/primary-button.blade.php` — 6 matches
- `resources/views/components/secondary-button.blade.php` — 5 matches
- `resources/views/components/danger-button.blade.php` — 0 matches (red stays, only verify)
- `resources/views/components/text-input.blade.php` — 2 matches
- `resources/views/components/nav-link.blade.php` — 2 matches
- `resources/views/components/responsive-nav-link.blade.php` — 6 matches
- `resources/views/components/alert.blade.php` — 7 matches

**Changes**:
- **primary-button**: gradient `from-purple-600 to-pink-500` → `from-brand to-sky`; hover `hover:from-purple-700 hover:to-pink-600` → `hover:from-brand-dark hover:to-sky-dark`; ring `focus:ring-purple-500` → `focus:ring-brand`; shadow `hover:shadow-pink-glow` → `hover:shadow-sky-glow`
- **secondary-button**: border/text `border-purple-200 text-purple-700` → `border-brand-light text-brand`; hover `hover:bg-purple-50 hover:border-purple-300` → `hover:bg-brand-light hover:border-brand`; ring `focus:ring-purple-500` → `focus:ring-brand`
- **danger-button**: no changes — only verify 0 purple/pink/indigo/amber references
- **text-input**: `focus:ring-purple-500 focus:border-purple-500` → `focus:ring-brand focus:border-brand`
- **nav-link**: `border-indigo-400` → `border-sky`; `focus:border-indigo-700` → `focus:border-sky-dark`
- **responsive-nav-link**: active `border-indigo-400 text-indigo-700 bg-indigo-50` → `border-sky text-sky bg-sky-light`; focus `focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700` → `focus:text-sky-dark focus:bg-sky focus:border-sky-dark`
- **alert (info variant)**: bg `bg-purple-50 border-purple-200 text-purple-800` → `bg-sky-light border-sky-light text-sky-dark`; icon `text-purple-400` → `text-brand`; close btn `text-purple-500 hover:bg-purple-100 focus:ring-purple-600` → `text-brand hover:bg-brand-light focus:ring-brand-dark`

**Verification**:
- Primary buttons show gold→sky gradient
- Secondary buttons show gold borders/outline
- Text inputs show gold focus ring on click
- Nav links show sky accent border on active page
- Alert info variant shows sky/blush background with brand icon
- Danger button still uses red classes (unchanged)

**Dependencies**: Task 1 (config)

---

### Task 4: Dashboard — stat cards, activity, gradients

**Files**:
- `resources/views/dashboard.blade.php` — 21 matches

**Changes**:
- Title gradient `from-purple-600 to-pink-600` → `from-brand to-sky`
- Stat card 1 (inventory): border `border-purple-100` → `border-sky-light`; hover border → `border-brand-light`; icon gradient `from-purple-100 to-purple-50` → `from-sky-light to-sky-light`; icon text `text-purple-600` → `text-brand`
- Stat card 2 (active products): border `border-pink-100` → `border-blush`; hover border → `border-blush-dark`; icon gradient `from-pink-100 to-pink-50` → `from-blush to-blush`; icon text `text-pink-600` → `text-sky`
- Activity section gradient `from-purple-600 to-pink-500` → `from-brand to-sky`; text `text-purple-100` → `text-white/80`
- CTA footer: gradient `from-purple-600 to-pink-500` → `from-brand to-sky`
- Activity divider: `from-purple-50 to-transparent border-purple-500` → `from-brand-light to-transparent border-brand`; gradient bar `from-purple-600 to-purple-400` → `from-brand to-brand-light`

**Verification**:
- Dashboard header shows gold→sky gradient
- Stat cards show sky icons (inventory) and blush icons (products)
- Activity section has gold/sky accent
- No purple/pink classes remain in dashboard

**Dependencies**: Tasks 1, 2, 3 (config + layouts + components provide the foundation)

---

### Task 5: Products CRUD — index, create, edit, show

**Files**:
- `resources/views/productos/index.blade.php` — 42 matches
- `resources/views/productos/create.blade.php` — 23 matches
- `resources/views/productos/edit.blade.php` — 23 matches
- `resources/views/productos/show.blade.php` — 5 matches

**Changes**:

**index.blade.php**:
- Head gradient: `from-purple-600 to-pink-600` → `from-brand to-sky`
- Create btn: `from-purple-600 to-pink-500` → `from-brand to-sky`; hover → `brand-dark/sky-dark`
- Search input: `border-purple-200 focus:ring-purple-500 focus:border-purple-500` → `border-brand-light focus:ring-brand focus:border-brand`
- Search results banner: `bg-purple-50 border-purple-200 text-purple-700` → `bg-brand-light border-brand-light text-brand-dark`; dismiss `text-purple-600 hover:text-purple-800` → `text-brand hover:text-brand-dark`
- Card border: `border-purple-100` → `border-sky-light`
- Table header: `from-purple-50 to-pink-50 border-purple-200` → `from-sky-light to-blush border-sky-light`
- Header text: `text-purple-900` → `text-gray-700`
- Row hover: `hover:bg-purple-50` → `hover:bg-sky-light`
- Badge: `from-purple-100 to-pink-100 text-purple-800` → `from-brand-light to-blush text-brand-dark`
- View icon: `text-purple-600 hover:text-purple-800 hover:bg-purple-50` → `text-brand hover:text-brand-dark hover:bg-brand-light`
- Edit icon: `text-pink-600 hover:text-pink-800 hover:bg-pink-50` → `text-sky hover:text-sky-dark hover:bg-sky-light`
- Empty CTA: gradient `from-purple-600 to-pink-500` → `from-brand to-sky`

**create.blade.php**:
- Header: `from-indigo-600 to-indigo-700` → `from-sky to-sky-dark`
- Header text: `text-indigo-100` → `text-white/80`
- All focus rings: `focus:ring-indigo-500 focus:border-indigo-500` → `focus:ring-brand focus:border-brand`
- Submit btn: `from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800` → `from-brand to-brand-dark hover:from-brand-dark hover:to-brand`
- **Fix bug**: `text-black` on submit btn → `text-white` (indigo bg was dark enough for black text to pass, but brand-dark is also dark — white is correct)

**edit.blade.php**:
- Header: `from-amber-600 to-amber-700` → `from-brand to-brand-dark`
- Header text: `text-amber-100` → `text-white/80`
- All focus rings: `focus:ring-amber-500 focus:border-amber-500` → `focus:ring-brand focus:border-brand`
- Submit btn: `from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800` → `from-brand to-brand-dark hover:from-brand-dark hover:to-brand`

**show.blade.php**:
- Header: `from-indigo-600 to-indigo-700` → `from-sky to-sky-dark`
- Sale price card: `bg-purple-50 text-purple-600 text-purple-700` → `bg-brand-light text-brand text-brand-dark`

**Verification**:
- Index: table header has sky/blush gradient, badges show gold/blush, action icons use brand (view) and sky (edit)
- Create: form header sky gradient, all focus rings gold, submit button brand gradient
- Edit: form header brand gradient, submit button brand gradient
- Show: header sky gradient, sale price card brand background
- `text-black` bug fixed to `text-white` on create submit button

**Dependencies**: Tasks 1, 2, 3

---

### Task 6: Categories CRUD — index, create, edit, show

**Files**:
- `resources/views/categorias/index.blade.php` — 25 matches
- `resources/views/categorias/create.blade.php` — 11 matches
- `resources/views/categorias/edit.blade.php` — 11 matches
- `resources/views/categorias/show.blade.php` — 6 matches

**Changes**:

**index.blade.php**:
- Head gradient: `from-purple-600 to-pink-600` → `from-brand to-sky`
- Create btn: `from-purple-600 to-pink-500` → `from-brand to-sky`; hover → `brand-dark/sky-dark`
- Card border: `border-purple-100 hover:border-purple-300` → `border-sky-light hover:border-brand-light`
- Card top gradient: `from-purple-600 via-pink-500 to-pink-400` → `from-brand via-sky to-sky-light`
- Badge: `from-purple-100 to-pink-100 text-purple-800` → `from-brand-light to-blush text-brand-dark`
- View btn: `bg-purple-50 text-purple-600 hover:bg-purple-100` → `bg-sky-light text-brand hover:bg-brand-light`
- Edit btn: `bg-pink-50 text-pink-600 hover:bg-pink-100` → `bg-blush text-sky hover:bg-sky-light`
- Empty CTA: `border-purple-100` / `from-purple-600 to-pink-500` → `border-sky-light` / `from-brand to-sky`

**create.blade.php**:
- Header: `from-indigo-600 to-indigo-700` → `from-sky to-sky-dark`
- Header text: `text-indigo-100` → `text-white/80`
- Focus rings: `focus:ring-indigo-500` → `focus:ring-brand`
- Submit btn: `from-indigo-600 to-indigo-700` → `from-brand to-brand-dark`

**edit.blade.php**:
- Header: `from-amber-600 to-amber-700` → `from-brand to-brand-dark`
- Header text: `text-amber-100` → `text-white/80`
- Focus rings: `focus:ring-amber-500` → `focus:ring-brand`
- Submit btn: `from-amber-600 to-amber-700` → `from-brand to-brand-dark`

**show.blade.php**:
- Header: `from-indigo-600 to-indigo-700` → `from-sky to-sky-dark`
- Product count: `text-indigo-600` → `text-brand`
- Prod section header: `from-purple-600 to-purple-700 text-purple-100` → `from-brand to-brand-dark text-white/80`

**Verification**:
- Index: cards show brand→sky gradient on top, gold badges, sky/blush action buttons
- Create/edit: form headers match product CRUD pattern (sky for create, brand for edit)
- Show: product count in brand, section header in brand gradient
- No purple/pink/indigo/amber classes remain

**Dependencies**: Tasks 1, 2, 3

---

### Task 7: Auth Views — login, register, verify-email

**Files**:
- `resources/views/auth/login.blade.php` — 9 matches
- `resources/views/auth/register.blade.php` — 4 matches
- `resources/views/auth/verify-email.blade.php` — 1 match
- `resources/views/auth/forgot-password.blade.php` — 0 matches (but inherits guest layout — already covered in Task 2)
- `resources/views/auth/confirm-password.blade.php` — 0 matches
- `resources/views/auth/reset-password.blade.php` — 0 matches

**Changes**:
- **login.blade.php**: Title gradient `from-purple-600 to-pink-600` → `from-brand to-sky`; checkbox `border-purple-300 text-purple-600 focus:ring-purple-500` → `border-brand-light text-brand focus:ring-brand`; links `text-purple-600 hover:text-purple-700` → `text-brand hover:text-brand-dark`
- **register.blade.php**: Title gradient `from-purple-600 to-pink-600` → `from-brand to-sky`; link `text-purple-600 hover:text-purple-700` → `text-brand hover:text-brand-dark`
- **verify-email.blade.php**: Resend/logout btn ring `focus:ring-indigo-500` → `focus:ring-brand`
- **forgot-password, confirm-password, reset-password**: Verify-only — no changes expected, they inherit the guest layout already updated in Task 2

**Verification**:
- Login/register titles show gold→sky gradient
- Login checkbox shows gold accent and ring
- All auth links use brand color
- All focus rings in auth forms use brand

**Dependencies**: Tasks 1, 2

---

### Task 8: Profile — update-profile-information-form partial

**Files**:
- `resources/views/profile/partials/update-profile-information-form.blade.php` — 1 match
- `resources/views/profile/edit.blade.php` — 0 matches
- `resources/views/profile/partials/delete-user-form.blade.php` — 0 matches
- `resources/views/profile/partials/update-password-form.blade.php` — 0 matches

**Changes**:
- **update-profile-information-form.blade.php**: Resend verification btn ring `focus:ring-indigo-500` → `focus:ring-brand`

**Verification**:
- Profile edit page renders correctly
- Resend verification button has brand focus ring
- No remaining purple/pink/indigo/amber references in any profile file

**Dependencies**: Tasks 1, 2

---

### Task 9: Final Cleanup — sweep and remove stale tokens

**Files**:
- `src/tailwind.config.js` (reverify)
- All Blade files under `resources/views/`

**Changes**:
1. Double-check `tailwind.config.js` has no leftover `purple`, `pink`, `gradientColorStops`, `purple-glow`, or `pink-glow` references
2. Run sweep: `Select-String -Pattern "purple-|pink-|indigo-|amber-" -Path D:\DESARROLLOS\TiendaStock\src\resources\views\**\*.blade.php`
3. For any remaining match: determine if it's a false positive (e.g., `purple` in a comment or in an unchanged scope like chart libraries) or a missed replacement
4. Fix any missed replacements found

**Verification**:
- Zero matches across all Blade files for `purple-|pink-|indigo-|amber-`
- Zero stale tokens in `tailwind.config.js`

**Dependencies**: Tasks 1 through 8

---

## Summary

| Task | Files | Est. Changes | Dependencies |
|------|-------|-------------|-------------|
| 1. Config | 1 | ~12 lines | None |
| 2. Layouts | 4 | ~48 lines | Task 1 |
| 3. Components | 7 | ~28 lines | Task 1 |
| 4. Dashboard | 1 | ~21 lines | Tasks 1-3 |
| 5. Products CRUD | 4 | ~93 lines | Tasks 1-3 |
| 6. Categories CRUD | 4 | ~53 lines | Tasks 1-3 |
| 7. Auth Views | 3 | ~14 lines | Tasks 1-2 |
| 8. Profile | 1 | ~1 line | Tasks 1-2 |
| 9. Final Cleanup | ~30 | sweep only | Tasks 1-8 |
| **Total** | **~23** | **~270 lines** | |

**Total match count across all files**: ~270 Tailwind class substitutions
**Estimated actual line changes** (including surrounding indentation): ~300-350 lines
