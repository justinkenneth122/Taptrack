# Attendance Section - UI Layout Guide

## Screen Layout: Standard Version

```
╔════════════════════════════════════════════════════════════════════╗
║  📋 Attendance Records                    👥 0 Total Attendees    ║
╠════════════════════════════════════════════════════════════════════╣
║                                                                    ║
║  ┌─ 🔍 FILTER & SEARCH ─────────────────────────────────────────┐ ║
║  │                                                               │ ║
║  │  📅 Event (Required)                                          │ ║
║  │  ┌─────────────────────────────────────────────────────────┐ │ ║
║  │  │ -- Select an Event --                              ▼  │ │ ║
║  │  └─────────────────────────────────────────────────────────┘ │ ║
║  │                                                               │ ║
║  │  🔎 Search Student                                            │ ║
║  │  ┌─────────────────────────────────────────────────────────┐ │ ║
║  │  │ 🔍 Search by name, email, or student number...  │ │ ║
║  │  └─────────────────────────────────────────────────────────┘ │ ║
║  │  ✓ Searches across student name, email, and ID              │ ║
║  │                                                               │ ║
║  │  ┌─ TWO COLUMN GRID ─────────────────────────────────────┐   │ ║
║  │  │                                                       │   │ ║
║  │  │  🎓 Program              📚 Year Level               │   │ ║
║  │  │  ┌.______________────┐  ┌.______________────┐        │   │ ║
║  │  │  │ -- All Programs  │  │ -- All Years    │        │   │ ║
║  │  │  └────────────────┘  └────────────────┘        │   │ ║
║  │  │                                                       │   │ ║
║  │  └───────────────────────────────────────────────────────┘   │ ║
║  │                                                               │ ║
║  │  🔄 Reset Filters                                            │ ║
║  │  [RESET FILTERS BUTTON]                                      │ ║
║  │                                                               │ ║
║  └───────────────────────────────────────────────────────────────┘ ║
║                                                                    ║
║  ┌─ 📊 ATTENDANCE RESULTS FOR: Event Name ──────────────────────┐ ║
║  │                                                               │ ║
║  │  ┌─ STATS BAR ──────────────────────────────────────────┐    │ ║
║  │  │                                                      │    │ ║
║  │  │  Total Attendees    Verified    Filtered Results    │    │ ║
║  │  │       45               38            45              │    │ ║
║  │  │   (blue badge)    (green badge)  (orange badge)     │    │ ║
║  │  │                                                      │    │ ║
║  │  └──────────────────────────────────────────────────────┘    │ ║
║  │                                                               │ ║
║  │  ┌── ATTENDANCE TABLE ──────────────────────────────────────┐ ║
║  │  │ # │ Name           │ ID     │ Email │ Program │ Year │   │ ║
║  │  ├───┼────────────────┼────────┼───────┼─────────┼──────┤   │ ║
║  │  │ 1 │ Juan Dela Cruz│ R2020..│ r20..@│ BS IT   │ 2nd  │   │ ║
║  │  │ 2 │ Maria Santos  │ R2021..│ r20..@│ BS IT   │ 2nd  │   │ ║
║  │  │ 3 │ Carlos Lopez  │ R2020..│ r20..@│ BS Psych│ 3rd  │   │ ║
║  │  │ ... (more rows)                                        │   │ ║
║  │  │                                                        │   │ ║
║  │  └────────────────────────────────────────────────────────┘ ║
║  │                                                               │ ║
║  └───────────────────────────────────────────────────────────────┘ ║
║                                                                    ║
╚════════════════════════════════════════════════════════════════════╝
```

## Screen Layout: Advanced Version with AJAX

```
╔════════════════════════════════════════════════════════════════════╗
║  📋 Attendance Records - Advanced     👥 45 Attendees            ║
╠════════════════════════════════════════════════════════════════════╣
║                                                                    ║
║  ┌─ 🔍 FILTER & SEARCH ─────────────────────────────────────────┐ ║
║  │                                                               │ ║
║  │  (Same filter layout as above)                                │ ║
║  │                                                               │ ║
║  └───────────────────────────────────────────────────────────────┘ ║
║                                                                    ║
║  ⏳ Filtering records...  ← Shows while filtering                │ ║
║                                                                    ║
║  ┌─ 📊 ATTENDANCE RESULTS ───────────────────────────────────────┐ ║
║  │                                                               │ ║
║  │  ┌─ STATS BAR (RESPONSIVE) ─────────────────────────────┐    │ ║
║  │  │                                                      │    │ ║
║  │  │  Total Attendees    │    Verified (Face)            │    │ ║
║  │  │        45           │           38                  │    │ ║
║  │  │     (blue)          │       (green)                 │    │ ║
║  │  │                                                      │    │ ║
║  │  └──────────────────────────────────────────────────────┘    │ ║
║  │                                                               │ ║
║  │  ┌── ATTENDANCE TABLE ──────────────────────────────────────┐ ║
║  │  │ # │ Name           │ Student # │ Email │Program│Year│Time │   │ ║
║  │  ├───┼────────────────┼───────────┼───────┼────────┼───┼─────┤   │ ║
║  │  │ 1 │ Juan Dela Cruz│ R2020001  │r20@..│ BS IT │2nd│✓Ver ││   │ ║
║  │  │ 2 │ Maria Santos  │ R2020002  │r20@..│ BS IT │3rd│ QR  │   │ ║
║  │  │ ... (more rows)                                        │   │ ║
║  │  │                                                        │   │ ║
║  │  └────────────────────────────────────────────────────────┘ ║
║  │                                                               │ ║
║  │  [🔄 RESET FILTERS] [📥 EXPORT CSV]                           │ ║
║  │                                                               │ ║
║  └───────────────────────────────────────────────────────────────┘ ║
║                                                                    ║
╚════════════════════════════════════════════════════════════════════╝
```

## Filter Section Expanded View

```
┌────────────────────────────────────────────────────────┐
│  🔍 FILTER & SEARCH                                    │
├────────────────────────────────────────────────────────┤
│                                                        │
│  📅 Event (Required)                                   │
│  ┌──────────────────────────────────────────────────┐ │
│  │ -- Select an Event --                       ▼   │ │
│  │ • Orientation Week (Apr 10, 2026)               │ │
│  │ • Tech Innovation Summit (Apr 15, 2026)         │ │
│  │ • Psychology Seminar (Apr 20, 2026)             │ │
│  └──────────────────────────────────────────────────┘ │
│                                                        │
│  🔎 Search Student                                     │
│  ┌──────────────────────────────────────────────────┐ │
│  │ 🔍 Search by name, email, or student number...  │ │
│  └──────────────────────────────────────────────────┘ │
│  ✓ Searches across student name, email, and ID      │
│                                                        │
│  ─────────────────────────────────────────────────    │
│  TWO COLUMN LAYOUT (Grid: 1fr 1fr)                    │
│  ─────────────────────────────────────────────────    │
│                                                        │
│  🎓 Program              │  📚 Year Level              │
│  ┌────────────────────┐  │  ┌────────────────────┐    │
│  │ -- All Programs   │  │  │ -- All Years      │    │
│  │ • BS IT            │  │  │ • 1st Year        │    │
│  │ • BS Psychology    │  │  │ • 2nd Year        │    │
│  │ • BS Business      │  │  │ • 3rd Year        │    │
│  │ • BS Nursing       │  │  │ • 4th Year        │    │
│  └────────────────────┘  │  └────────────────────┘    │
│                                                        │
│  ─────────────────────────────────────────────────    │
│  ACTION BUTTONS                                       │
│  ─────────────────────────────────────────────────    │
│                                                        │
│  [🔄 RESET FILTERS]  [📥 EXPORT CSV] (Advanced only) │
│                                                        │
└────────────────────────────────────────────────────────┘
```

## Stats Dashboard

```
┌─────────────────────────────────────────────────────┐
│                                                     │
│  ┌──────────────┬──────────────┬──────────────┐   │
│  │              │              │              │   │
│  │   Total      │   Verified   │  Filtered    │   │
│  │ Attendees    │   (Face)     │   Results    │   │
│  │              │              │              │   │
│  │     45       │      38      │      45      │   │
│  │              │              │              │   │
│  │  (Blue #)    │ (Green #)    │ (Orange #)  │   │
│  │              │              │              │   │
│  └──────────────┴──────────────┴──────────────┘   │
│                                                     │
│  Each stat box:                                     │
│  • Has a label (smaller text)                       │
│  • Has a large number                               │
│  • Has a colored left border (visual indicator)     │
│                                                     │
└─────────────────────────────────────────────────────┘
```

## Attendance Table - Column Details

```
┌──────────────────────────────────────────────────────────────────┐
│  # │ Student Name    │ Student # │ Email      │ Program  │ Year │
├────┼─────────────────┼───────────┼────────────┼──────────┼──────┤
│ 1  │ Juan Dela Cruz  │ R2020001  │ r20@feu..  │ BS IT    │ 2nd  │
│ 2  │ Maria Santos    │ R2020002  │ r20@feu..  │ BS Psych │ 3rd  │
│ 3  │ Carlos Lopez    │ R2020003  │ r20@feu..  │ BS Bus   │ 1st  │
└────┴─────────────────┴───────────┴────────────┴──────────┴──────┘

CONTINUED:
───────────────────────────────────────────────────────
│ Time Scanned               │ Status                    │
├────────────────────────────┼───────────────────────────┤
│ Apr 15, 2026 · 9:30 AM    │ ✓ Verified                │
│ Apr 15, 2026 · 9:32 AM    │ QR Scanned                │
│ Apr 15, 2026 · 9:28 AM    │ QR Scanned                │
───────────────────────────────────────────────────────

TIME FORMAT: "Mon j, Y · g:i A"
Example: "Apr 15, 2026 · 9:30 AM"

STATUS BADGES:
• ✓ Verified  (green badge) - Face recognition verified
• QR Scanned  (blue badge)  - Scanned via QR code only
```

## Responsive Behavior

### Desktop (> 1024px)
- All columns visible
- Side-by-side filter dropdowns
- Stats in 3-column grid

### Tablet (640px - 1024px)
- All columns visible (may scroll horizontally)
- Side-by-side filter dropdowns
- Stats in 2-column grid (responsive)

### Mobile (< 640px)
- All columns visible (horizontal scroll)
- Stacked filter dropdowns
- Stats in single column
- Optimized touch targets

## Color Scheme

```
Primary Colors:
├─ Blue (#0066cc)      - Event filter, verified badge
├─ Green (#28a745)     - Verified count badge
├─ Orange (#ffc107)    - Filtered results, warnings
└─ Dark (#333333)      - Text, borders

Backgrounds:
├─ Label background (#f5f5f5)
├─ Table header (#f9f9f9)
├─ Alert boxes (#f0f7ff, #fff3cd, etc.)
└─ Default (#ffffff)

Text:
├─ Primary (#333333)
├─ Secondary (#666666)
├─ Muted/Tertiary (#999999)
└─ Link (#0066cc)
```

## Interactive Elements

### Dropdowns
```
Hover: Background color change, subtle shadow
Active: Border highlight, arrow icon rotation
```

### Search Input
```
Focus: Blue border, shadow glow
Typing: Results update in real-time (Advanced version)
```

### Buttons
```
Normal: Light outline
Hover: Background fill
Active: Bold, highlight
Disabled: Grayed out
```

### Badge Elements
```
✓ Verified (green)  - Background color #28a745, white text
QR Scanned (blue)   - Background color #007bff, white text
```

---

## UI Component Library Used

The Attendance Section appears to use **DaisyUI/Tailwind CSS**:
- `.card` - Card containers
- `.select` - Dropdown selects
- `.input` - Text inputs
- `.btn` - Buttons
- `.badge` - Badge labels
- `.table` - Table styling
- `.alert` - Alert boxes

All styles are responsive and mobile-friendly.
