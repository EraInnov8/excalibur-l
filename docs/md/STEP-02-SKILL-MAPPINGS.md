# STEP-02: Skill Mappings

## Overview

Personality-to-skill mappings are imported from a CSV file into the `skill_mappings` table. Each row maps a personality profile (energy, orientation, structure, drive, reaction) to five recommended skills.

---

## CSV Source

**File path:** `database/seeders/data/mapping.csv`

**Structure:**

| Column     | Description                          |
|------------|--------------------------------------|
| energy     | Introverted / Extroverted / Balanced |
| orientation| Practical / Imaginative / Balanced    |
| structure  | Organized / Spontaneous / Balanced    |
| drive      | Cooperative / Competitive / Balanced  |
| reaction   | Reflective / Responsive / Balanced    |
| skill_1    | First recommended skill              |
| skill_2    | Second recommended skill             |
| skill_3    | Third recommended skill              |
| skill_4    | Fourth recommended skill             |
| skill_5    | Fifth recommended skill              |

**Headers (expected):**

```
energy,orientation,structure,drive,reaction,skill_1,skill_2,skill_3,skill_4,skill_5
```

**Row count:** 33 data rows (1 header row).

---

## Seeder Logic

The `SkillMappingSeeder`:

1. Opens `database/seeders/data/mapping.csv`.
2. Validates that headers match the expected list.
3. For each data row:
   - Looks up existing record by the unique combination `(energy, orientation, structure, drive, reaction)`.
   - If found: **updates** the skill columns (no duplicate insert).
   - If not found: **inserts** a new row.
4. Outputs how many rows were inserted and updated.

**Idempotency:** Running the seeder multiple times does not create duplicate rows. Existing rows are updated instead.

---

## Table Population

**Table:** `skill_mappings`

**Row count inserted:** 33

**Unique constraint:** `(energy, orientation, structure, drive, reaction)`

**Columns:** `id`, `energy`, `orientation`, `structure`, `drive`, `reaction`, `skill_1`, `skill_2`, `skill_3`, `skill_4`, `skill_5`, `created_at`, `updated_at`

---

## How to Re-run Seeder

**Run only the skill mapping seeder:**

```powershell
cd C:\Dox\Projects\excalibur-l
php artisan db:seed --class=SkillMappingSeeder
```

**Run all seeders (includes `SkillMappingSeeder`):**

```powershell
php artisan db:seed
```

**Fresh migrate + seed:**

```powershell
php artisan migrate:fresh --seed
```

---

## Notes

- The seeder was created along with a migration for the `skill_mappings` table. The migration adds the table and a unique index on the five personality columns.
- Duplicate avoidance is based on the composite key `(energy, orientation, structure, drive, reaction)`.
- `SkillMappingSeeder` is registered in `DatabaseSeeder.php` and runs when `php artisan db:seed` is executed.
- Empty or malformed rows are skipped. Rows with fewer than 10 columns are ignored.
- On re-run, existing rows are updated rather than duplicated; the output reports both inserted and updated counts.
