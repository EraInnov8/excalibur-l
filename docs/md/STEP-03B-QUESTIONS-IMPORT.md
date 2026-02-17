# STEP-03B: Questions Import

## Overview

Assessment questions are imported from a CSV file into the `questions` table. The `QuestionSeeder` reads the file, normalizes values, skips malformed rows, and avoids duplicate inserts.

---

## CSV Source

**File path:** `database/seeders/data/questions.csv`

**Structure:**

| Column   | Description                                      |
|----------|--------------------------------------------------|
| dimension| Personality dimension (Energy, Orientation, etc.)|
| trait    | Trait within that dimension (Introvert, Practical, etc.) |
| text     | Question text                                    |

**Expected headers:** `dimension,trait,text` (case-insensitive)

**Blank rows:** Ignored.

---

## Import Logic

1. **Validation:** Confirms the file exists and headers match `dimension,trait,text`.

2. **Per-row processing:**
   - Skip rows with fewer than 3 columns.
   - Trim whitespace from all values.
   - Skip rows where dimension, trait, or text is empty.
   - Normalize dimension to lowercase (e.g. `Energy` → `energy`).
   - Normalize traits for compatibility with `skill_mappings`:
     - `Introvert` → `Introverted`
     - `Extrovert` → `Extroverted`
     - `Organised` → `Organized`

3. **Duplicate avoidance:** Uses `firstOrCreate` on `(dimension, trait, text)`. Re-running the seeder adds no duplicates.

4. **Malformed rows:** Rows with missing columns or empty required fields are skipped; the skipped count is reported.

---

## Total Questions Imported

**699** questions

---

## Distribution by Dimension & Trait

### By dimension

| Dimension   | Count |
|------------|-------|
| drive      | 140   |
| energy     | 139   |
| orientation| 140   |
| reaction   | 140   |
| structure  | 140   |

### By trait

| Trait       | Count |
|-------------|-------|
| Competitive | 70    |
| Cooperative | 70    |
| Extroverted | 69    |
| Imaginative | 70    |
| Introverted | 70    |
| Organized   | 70    |
| Practical   | 70    |
| Reflective  | 70    |
| Responsive  | 70    |
| Spontaneous | 70    |

---

## How to Re-run Seeder

**Run only the question seeder:**

```powershell
cd C:\Dox\Projects\excalibur-l
php artisan db:seed --class=QuestionSeeder
```

**Run all seeders:**

```powershell
php artisan db:seed
```

**Clean re-import** (replace existing questions with CSV contents):

```powershell
php artisan tinker --execute="App\Models\Question::truncate();"
php artisan db:seed --class=QuestionSeeder
```

Re-running the seeder without truncating will not create duplicates; existing rows are skipped via `firstOrCreate`.
