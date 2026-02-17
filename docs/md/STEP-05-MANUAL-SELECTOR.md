# STEP-05: Manual Trait Selector

## Overview

The Manual Trait Selector lets users choose their personality traits directly and generate a learning path without taking the assessment. It uses the same skill-mapping logic as the assessment results.

**Route:** `/manual`

---

## Route

| Path   | Description                    |
|--------|--------------------------------|
| `/manual` | Manual trait selector page |

---

## Components Created

| File | Purpose |
|------|---------|
| `frontend/src/pages/ManualSelector.jsx` | Manual selector page: form, API call, results display |
| `frontend/src/App.jsx` | Updated with `Routes`, `Route` for `/manual`, intro link |
| `frontend/src/App.css` | Styles for `.manual-screen`, `.manual-form`, `.manual-select`, etc. |
| `frontend/src/main.jsx` | Wrapped with `BrowserRouter` |

---

## API Flow

### POST /api/manual/generate

**Request body:**
```json
{
  "energy": "Introverted",
  "orientation": "Practical",
  "structure": "Organized",
  "drive": "Cooperative",
  "reaction": "Reflective"
}
```

**Valid values per dimension:**
- `energy`: Introverted | Extroverted | Balanced
- `orientation`: Practical | Imaginative | Balanced
- `structure`: Organized | Spontaneous | Balanced
- `drive`: Cooperative | Competitive | Balanced
- `reaction`: Reflective | Responsive | Balanced

**Response:**
```json
{
  "personality": {
    "energy": "Introverted",
    "orientation": "Practical",
    "structure": "Organized",
    "drive": "Cooperative",
    "reaction": "Reflective"
  },
  "skills": [
    "Active Listening",
    "Workflow Design",
    "Knowledge Management",
    "Time Management",
    "Collaboration"
  ]
}
```

The API looks up the matching row in `skill_mappings` and returns the five recommended skills. If no row exists, `skills` is an empty array.

---

## UI Layout

- **Header:** Same as assessment ("Personality Assessment")
- **Title:** "Manual Trait Selector"
- **Form:** Five dropdowns (Energy, Orientation, Structure, Drive, Reaction), each with three options including Balanced
- **Button:** "Generate Learning Path"
- **Results:** Personality profile grid + Recommended Skills list
- **Link:** "← Back to Assessment" to return to `/`

---

## How to Use

1. Open `http://localhost:5173` (with Laravel and Vite running).
2. On the assessment intro screen, click **Manual Trait Selector**.
3. Select traits from each dropdown.
4. Click **Generate Learning Path**.
5. View your personality profile and recommended skills.
6. Use **← Back to Assessment** to return to the main flow.

---

## Styling

Uses the shared design system:
- Primary Blue (#272361) for title and accents
- Secondary Brown (#412312) for labels
- Sharp edges, no gradients
- Accent Red (#D83030) for errors only
- Mobile-friendly layout
