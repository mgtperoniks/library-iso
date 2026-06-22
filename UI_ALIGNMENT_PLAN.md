# FORENSIC UI AUDIT & STANDARDIZATION PLAN

This plan outlines the forensic differences between the `/departments` and `/categories` pages, identifies why they diverged in styling/architecture, and provides an actionable blueprint to standardize the Categories module using the Departments module as the reference standard.

---

## 1. Root Cause Analysis

### Why the visual results differ
The UI difference is a direct result of two distinct coding styles used during development:
1. **Departments Page**: Implemented using **ad-hoc inline styles** directly on HTML elements. It bypasses the centralized design system classes completely.
2. **Categories Page**: Implemented using **class-based styles** defined in the centralized Design System stylesheet (`public/css/style.css`).

### Overriding Behaviors
* **The Card Nesting Bug**: Categories utilizes `<div class="page-card">` as the outer container. In `layouts/iso.blade.php`, `.page-card` is configured with `background: transparent; box-shadow: none; padding: 0;` to fit the layout shell. Categories then relies on a nested `.card-section .card-inner` combination to render a card background, creating double padding and border noise.
* **Inline-Styling Overrides**: Departments bypasses `.page-card` entirely and uses raw HTML with a custom card container (`style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(20,40,80,0.04);"`), dictating its own layout dimensions.
* **Badge Styling divergence**: Categories uses Solid Badges with white text (`.badge-success` and `.badge-warning` from CSS). Departments uses pastel, transparent-tinted backgrounds with colored text (e.g. blue background with blue text for active documents).
* **Missing Title Section**: `categories/index.blade.php` does not define a `@section('title')` directive, causing the topbar title to fallback to `ISO Library`. `departments/index.blade.php` defines `@section('title', 'Daftar Departemen')`, which correctly updates the header bar.
* **Icon Ligature Rendering Failure (Application-wide bug)**: During local asset migration, external font endpoints were removed. However, the local material symbols class definition lacks ligature configuration (`font-feature-settings: 'liga'`). As a result, browsers render literal text strings (e.g., `folder_open Categories`) instead of icon glyphs.

---

## 2. File Inventory

* **Routes**:
  * `routes/web.php` (contains category/department index/show definitions)
* **Controllers**:
  * `app/Http/Controllers/CategoryController.php`
  * `app/Http/Controllers/DepartmentController.php`
* **Blade Views**:
  * `resources/views/categories/index.blade.php`
  * `resources/views/categories/show.blade.php`
  * `resources/views/departments/index.blade.php`
  * `resources/views/departments/show.blade.php`
* **Layouts**:
  * `resources/views/layouts/iso.blade.php`
* **Assets / CSS**:
  * `public/css/style.css`
  * `public/vendor/material-symbols/material-symbols.css`

---

## 3. Blade Hierarchy Diagram

```mermaid
graph TD
    A[web.php] -->|Route: /departments| B[DepartmentController@index]
    A[web.php] -->|Route: /categories| C[CategoryController@index]
    
    B --> D[departments/index.blade.php]
    C --> E[categories/index.blade.php]
    
    D -->|Inherits| F[layouts/iso.blade.php]
    E -->|Inherits| F[layouts/iso.blade.php]
    
    F -->|Yields Content inside| G[.iso-shell .iso-main wrapper]
    F -->|Loads| H[public/css/style.css]
    F -->|Loads| I[public/vendor/material-symbols/material-symbols.css]
```

---

## 4. Visual Difference Matrix

| Component | Departments (Reference Standard) | Categories (Current State) | Same? | Action Plan |
| :--- | :--- | :--- | :--- | :--- |
| **Topbar Title** | "Daftar Departemen" (Defined via `@section('title')`) | "ISO Library" (Default fallback; no title section) | **No** | Add `@section('title', 'Daftar Kategori')` to Categories views. |
| **Header Card** | None (Page title "Daftar Departemen" resides inside the main card) | `.site-header` containing title, subtitle, and primary action button | **No** | Remove `.site-header` and move title inside the main card wrapper. |
| **Main Card wrapper** | Inline styled: `border-radius:16px; padding:24px; box-shadow: 0 8px 24px rgba(20,40,80,0.04)` | Class-based: `.page-card` (transparent override) + `.card-inner` (14px padding, shadow) | **No** | Update outer Categories wrapper to use the exact inline layout styles of Departments. |
| **Table Headers** | Title Case, left-aligned, no background, simple bottom border | Uppercase, light-purple background (`#f3f3fe`), solid dark border (`#c3c6d7`) | **No** | Remove headers background, change to Title Case, match borders and cell padding. |
| **Active Badge** | Pastel Blue (`background:#e8f0ff; color:#2563eb; font-weight:600`) | Solid Green (`background:#16a34a; color:#ffffff; font-weight:700`) | **No** | Update Active badge styling to match Departments' blue pastel design. |
| **In Progress Badge** | If > 0: clickable link, Pastel Yellow (`background:#fff4cc; color:#8a6d00`). If 0: Gray (`background:#f3f4f6`). | Solid Orange (`badge-warning`: `background:#f59e0b; color:#ffffff`) | **No** | Change badge to Pastel Yellow / Gray pill with conditional link to approvals filter. |
| **Create Button** | None (Access documents via Detail page) | "+ New Document" button on top right of header | **No** | Remove top-right Create button to align with Departments' minimalistic index design. |
| **Detail Route / Action** | Route: `departments.show` (Opens dedicated detail layout) | Search route query: `url('documents') . '?search=CODE.'` | **No** | Register the `/categories/{category}` route and redirect Categories index actions there. |

---

## 5. Screenshots

### Departments Page (Reference Design)
![Departments Page Screenshot](C:\Users\ppic2\.gemini\antigravity\brain\98cadfcc-4f15-46d2-b775-c2233e3ca6cd\departments_page.png)
* **Callout A (Title & Card)**: The page title resides inside a single card container with `padding: 24px` and a soft shadow.
* **Callout B (Table Styling)**: Simple borders with Title Case column headers and transparent header backgrounds.
* **Callout C (Badges)**: Rounded pastel colored pills (`active_count` is blue-tinted, `pending_count` is orange/gray-tinted).

### Categories Page (Current State)
![Categories Page Screenshot](C:\Users\ppic2\.gemini\antigravity\brain\98cadfcc-4f15-46d2-b775-c2233e3ca6cd\categories_page.png)
* **Callout D (Double Wrapper)**: Outer container lacks padding/margin context due to `.page-card` transparent reset, while inner table uses `.card-inner` border.
* **Callout E (Badges)**: Green (`badge-success`) and Orange (`badge-warning`) solid color blocks.
* **Callout F (Table Headers)**: Distinctive purple/gray background band above columns.

---

## 6. Standardization Recommendation

### KEEP
1. Keep the `CategoryController@index` data retrieval logic (which counts document quantities per category).
2. Keep the `@extends('layouts.iso')` base layout inheritance.
3. Keep the search link/action fallback (if detail view is not desired, but detail view is recommended to match Departments detail).

### CHANGE
1. **Outer Layout & Header Structure**:
   * Replace `.page-card` and `.site-header` in `categories/index.blade.php`.
   * Implement a single card container matching the Departments page:
     `<div style="max-width:1200px;margin:28px auto;padding:0 16px;box-sizing:border-box;">`
       `<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 8px 24px rgba(20,40,80,0.04);">`
   * Place the `h2` title ("Daftar Kategori") inside the card.
2. **Table Design**:
   * Format table headers in Title Case: `Kode`, `Tipe Dokumen`, `Dokumen Aktif`, `In Progress`, `Aksi`.
   * Apply transparent background with `#eef2f7` bottom borders and padding of `12px 10px` on headers and cells.
3. **Badges Styling**:
   * Update the active count badge:
     `<span style="display:inline-block;background:#e8f0ff;color:#2563eb;padding:6px 12px;border-radius:10px;font-weight:600;">`
   * Update the in-progress badge:
     * If > 0: render a link to approval list filtered by category using pastel gold styling.
     * If 0: render a gray-tinted pill (`#f3f4f6`).

### REMOVE
1. Remove the separate `.site-header` block containing the subtitle and the `+ New Document` button from the Categories index page.
2. Remove the `.card-section` and `.card-inner` nested wrapper structure to eliminate duplicate borders.

### RISK
1. **Missing Detail Route**: While `/departments/{department}` is defined and renders a beautiful grouped document list, `/categories/{category}` route is missing in the web routes list. Users clicking "Buka Dokumen" in Categories are sent to a search results page. To achieve complete architectural standardization, the `/categories/{category}` route must be mapped, and `categories/show.blade.php` must be aligned to the style of `departments/show.blade.php`.
2. **Application-wide Icon Display Defect**: Re-localizing the Material Symbols font broke icon rendering in the sidebar and topbar (they render as literal text strings instead of glyphs). This is caused by missing `font-feature-settings: 'liga';` inside `.material-symbols-outlined`.

---

## 7. Estimated Files To Modify

1. **`routes/web.php`**
   * Register the route for `/categories/{category}` pointing to `CategoryController@show`.
2. **`resources/views/categories/index.blade.php`**
   * Rewrite layout hierarchy, card structure, table padding, badge styles, and add `@section('title')`.
3. **`resources/views/categories/show.blade.php`**
   * Redesign to align with `departments/show.blade.php` (uses grouped document lists with inline layouts).
4. **`public/vendor/material-symbols/material-symbols.css`**
   * Add `-webkit-font-feature-settings: 'liga'; font-feature-settings: 'liga';` to fix the application-wide icon rendering issue.
5. **`resources/views/layouts/iso.blade.php`**
   * Apply the same ligature fix inside the inline styles of the baseline definition for `.material-symbols-outlined`.

---

## 8. Regression Risk Assessment

* **Namespace / Route Collision**: Adding a new route for Category Detail might clash if not named correctly. Standardizing it as `categories.show` is safe and follows current conventions.
* **Sidebar Link Status**: Sidebar active states rely on `request()->routeIs('categories.*')`. Standardizing categories routes will keep the active state highlighting correct.
* **Impact of Ligature Fix**: Adding `font-feature-settings` to the local Material Symbols CSS is low-risk and will fix the broken sidebar icons across the entire site without breaking any layout styles.
