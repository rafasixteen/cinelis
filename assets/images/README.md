# CineLis identity

The teal aperture identity replaces the previous red ticket logo. The symbol combines an open C, aperture blades, and a projection/play cue. The wordmark is Inter Bold converted to paths.

## Assets

| Asset | Use |
| --- | --- |
| `cinelis-logo-light.svg` | Full logo on light surfaces |
| `cinelis-logo-dark.svg` | Full logo on dark surfaces, including the backoffice sidebar in either theme |
| `cinelis-logo-light.png` / `cinelis-logo-dark.png` | Matching 1184 × 288 transparent PNG exports |
| `cinelis-mark-light.svg` / `cinelis-mark-dark.svg` | Standalone 72 × 72 symbol |
| `logo-preview.html` | Open locally to compare both variants and header-size usage |
| `legacy/` | Original red logo PNGs, preserved before replacement |

Both full SVGs use the same 296 × 72 view box and identical geometry. All lettering is outlined, so no external fonts are required. Backgrounds and negative spaces are transparent. Use SVG for the application UI. Recommended full-logo height is 36–40 px; use the symbol alone when space is tighter. Preserve the aspect ratio and allow at least half a symbol's width around the lockup.

## Colors and theme tokens

| Role | Light surface | Dark surface |
| --- | --- | --- |
| Symbol / `--primary` | `#0F766E` | `#5EEAD4` |
| Wordmark | `#161512` | `#F1F0EE` |
| Primary action text / `--primary-foreground` | `#FFFFFF` | `#161512` |

`common/assets/css/layouts/base.css` contains matching values for explicit themes and the system dark preference. The permanent dark sidebar uses `--sidebar-primary: #5EEAD4` in both themes. Surface and semantic status tokens are retained.

The Excalidraw mockups embed these exact SVG file bytes in each screen header. The ordinary application header/sidebar templates still use their existing text markup; these assets are ready for their UI implementation.

## Creation

The concept was explored using the built-in image-generation tool. The final production logo was constructed as clean SVG geometry, with an outlined Inter Bold wordmark, and exported to transparent PNG. The two final variants share identical paths and differ only in fill colors. The rough generated raster alternatives are not the production assets.

Concept prompt:

> Use case: logo-brand. Create the final production logo asset for CineLis, an understated contemporary independent cinema in Lisbon. A clean horizontal logo lockup: a distinctive geometric C-shaped cinema aperture symbol on the left, with a subtle negative-space projection/play cue integrated into the C; then the exact wordmark "CineLis" on the right, correctly capitalized C and L. Sophisticated compact medium-bold modern sans-serif typography, excellent legibility at a 32px-high website header. Simple flat solid vector-style shapes with crisp contours. Light-mode variant: symbol in deep teal #0F766E; wordmark in near-black #161512. Genuine transparent alpha background, no background color, no checkerboard drawn into the artwork. Wide horizontal canvas, tightly composed with minimal equal padding around the logo; symbol height approximately the cap height plus 20%. Text baseline aligned optically with symbol. Only one logo, no presentation board, no caption, no slogan. Avoid the old ticket/reel logo, filmstrip perforations, red, yellow, gold, gradients, shadows, textures, mockup surfaces, or decorative fine details. This is a brand-new replacement identity, not a recoloring of the old logo. Save this light-mode asset so a geometrically identical dark-mode variation can be made next.

Inter's license is included in `INTER-LICENSE.txt`.
