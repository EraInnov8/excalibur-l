# STEP-03: Assessment Engine

## Overview

The personality assessment engine exposes two API endpoints: one to fetch questions and one to submit answers and receive a personality profile with recommended skills.

---

## Endpoints

### GET /api/questions

Returns up to 50 questions for the personality assessment.

- **Method:** GET
- **Response:** JSON with `questions` array. Each question has `id`, `dimension`, `trait`, and `text`.
- **Selection logic:** Randomly selects 5 questions per trait across 10 traits (energy: Introverted/Extroverted; orientation: Practical/Imaginative; structure: Organized/Spontaneous; drive: Cooperative/Competitive; reaction: Reflective/Responsive), then shuffles the result.
- **Fallback:** If fewer than 5 questions exist for a trait, returns all available for that trait. Total may be fewer than 50.

### POST /api/assessment/submit

Submits answers and returns personality profile + skills.

- **Method:** POST
- **Content-Type:** application/json
- **Request body:** `{ "answers": [ { "question_id": number, "score": number }, ... ] }`
- **Response:** JSON with `personality` (energy, orientation, structure, drive, reaction) and `skills` (array of strings).
- **Validation:** `question_id` must exist; `score` must be 1–5.

---

## Personality Calculation Logic

1. **Group answers by dimension**  
   Each answer is associated with a question; the question's `dimension` and `trait` define where the score goes.

2. **Sum scores by trait within each dimension**  
   For each dimension (energy, orientation, structure, drive, reaction), sum the `score` values for each trait (e.g. Introverted vs Extroverted for energy).

3. **Determine dominant trait**  
   Compare the two trait totals within each dimension:
   - Higher trait wins.
   - If totals are equal → use **"Balanced"**.

4. **Build personality profile**  
   Combine the dominant trait for each dimension into a 5-field profile:
   - `energy`: Introverted | Extroverted | Balanced
   - `orientation`: Practical | Imaginative | Balanced
   - `structure`: Organized | Spontaneous | Balanced
   - `drive`: Cooperative | Competitive | Balanced
   - `reaction`: Reflective | Responsive | Balanced

---

## Skill Mapping Retrieval

1. Look up the `skill_mappings` row where `(energy, orientation, structure, drive, reaction)` match the computed personality.
2. If a row exists: return `skill_1` through `skill_5` as the `skills` array.
3. If no row exists: return `skills: []` (empty array).

The `skill_mappings` table is populated from `database/seeders/data/mapping.csv` (see [STEP-02-SKILL-MAPPINGS.md](./STEP-02-SKILL-MAPPINGS.md)). The last row is the "Balanced" profile (energy=Balanced, orientation=Balanced, etc.) with default skills.

---

## Request & Response Examples

### GET /api/questions

**Request:**
```
GET /api/questions
```

**Response (excerpt):**
```json
{
  "questions": [
    {
      "id": 49,
      "dimension": "reaction",
      "trait": "Responsive",
      "text": "I prefer to act and adjust rather than delay."
    },
    ...
  ]
}
```

### POST /api/assessment/submit

**Request:**
```json
{
  "answers": [
    { "question_id": 1, "score": 5 },
    { "question_id": 2, "score": 4 },
    { "question_id": 6, "score": 2 }
  ]
}
```

**Response (Introverted, Practical, Organized, Cooperative, Reflective):**
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

**Response (Balanced profile):**
```json
{
  "personality": {
    "energy": "Balanced",
    "orientation": "Balanced",
    "structure": "Balanced",
    "drive": "Balanced",
    "reaction": "Balanced"
  },
  "skills": [
    "Strategic Thinking",
    "Emotional Intelligence",
    "Collaboration",
    "Problem Solving",
    "People Leadership"
  ]
}
```

---

## Edge Cases

| Scenario | Behavior |
|----------|----------|
| **Insufficient questions** | Returns all available questions. Fewer than 50 if any trait has &lt; 5 questions. |
| **Tied scores in a dimension** | Dominant trait = `"Balanced"` for that dimension. |
| **No mapping for profile** | Returns `skills: []`. Personality still reflects computed traits. |
| **Empty answers array** | All dimensions become `"Balanced"` (0 vs 0). Matches Balanced row if present. |
| **Invalid question_id** | Validation error: 422 with message. |
| **Invalid score (e.g. 0 or 6)** | Validation error: 422. Score must be 1–5. |
| **Duplicate question_id in answers** | All answers for that question are included; scores are summed per trait, so duplicates affect totals. |
| **Missing dimension in answers** | That dimension gets 0 for both traits → `"Balanced"`. |

---

## Database Schema

### questions

| Column    | Type    |
|-----------|---------|
| id        | bigint  |
| dimension | string  |
| trait     | string  |
| text      | text    |
| created_at| timestamp |
| updated_at| timestamp |

### Seeding Questions

Run the question seeder to populate 50 questions (5 per trait):

```powershell
php artisan db:seed --class=QuestionSeeder
```

Or run all seeders:

```powershell
php artisan db:seed
```
