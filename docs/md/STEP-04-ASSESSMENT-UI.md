# STEP-04: Assessment UI

## Overview

The React assessment interface lets users complete the personality assessment in the browser. It fetches questions from the Laravel API, collects answers on a 1–5 scale, submits them, and displays the personality profile and recommended skills.

**Location:** `frontend/`

---

## Design System

### Color Palette

| Token | Hex | Usage |
|-------|-----|-------|
| Primary Blue | #272361 | Headers, primary button, progress bar |
| Secondary Brown | #412312 | Subtle accents, labels, secondary button |
| Black | #000000 | Primary text |
| White | #FFFFFF | Card backgrounds, button text |
| Accent Red | #D83030 | Errors only |

### Guidelines

- Clean, professional layout
- Sharp edges (minimal rounded corners)
- High readability
- Blue for primary actions
- Brown for subtle accents
- Red only for errors
- No gradients or flashy effects
- Mobile friendly

---

## Assessment Flow

### 1. Intro Screen

- Welcome text and short description
- "Start Assessment" button
- Triggers `GET /api/questions`

### 2. Questions Screen

- Progress bar and "X of Y answered" counter
- Each question shown in a card with:
  - Question number
  - Statement text
  - Scale labels: Strongly disagree ↔ Strongly agree
  - 1–5 scale buttons (numeric)
- "View Results" enabled only when all questions are answered
- Submit triggers `POST /api/assessment/submit`

### 3. Submitting

- "Calculating your profile…" message while awaiting response

### 4. Results Screen

- Personality profile (energy, orientation, structure, drive, reaction)
- Recommended skills list
- "Take Assessment Again" button

### 5. Error Handling

- Error message in red
- "Try Again" or inline retry where appropriate

---

## Components & Files

| File | Purpose |
|------|---------|
| `frontend/src/App.jsx` | Assessment flow, state, API calls |
| `frontend/src/App.css` | Component styles |
| `frontend/src/index.css` | Design tokens, base styles |

---

## API Integration

- **GET /api/questions** – Fetched on "Start Assessment"; returns up to 50 questions
- **POST /api/assessment/submit** – Called when all questions answered; request body: `{ answers: [{ question_id, score }] }`; response: `{ personality, skills }`

Questions use the Vite dev proxy: requests to `/api/*` are forwarded to Laravel at `http://localhost:8000`.

---

## How to Run

**Prerequisites:** Laravel server and Vite dev server both running.

**Terminal 1 – Laravel:**
```powershell
cd C:\Dox\Projects\excalibur-l
php artisan serve
```

**Terminal 2 – React:**
```powershell
cd C:\Dox\Projects\excalibur-l\frontend
npm run dev
```

**Open:** `http://localhost:5173`

---

## Build for Production

```powershell
cd frontend
npm run build
```

Output goes to `frontend/dist/`. Serve via your web server or Laravel public directory.
