import { useState, useCallback } from 'react'
import { Link } from 'react-router-dom'

const DIMENSION_OPTIONS = {
  energy: ['Introverted', 'Extroverted', 'Balanced'],
  orientation: ['Practical', 'Imaginative', 'Balanced'],
  structure: ['Organized', 'Spontaneous', 'Balanced'],
  drive: ['Cooperative', 'Competitive', 'Balanced'],
  reaction: ['Reflective', 'Responsive', 'Balanced'],
}

const DIMENSION_LABELS = {
  energy: 'Energy',
  orientation: 'Orientation',
  structure: 'Structure',
  drive: 'Drive',
  reaction: 'Reaction',
}

export default function ManualSelector() {
  const [form, setForm] = useState({
    energy: 'Balanced',
    orientation: 'Balanced',
    structure: 'Balanced',
    drive: 'Balanced',
    reaction: 'Balanced',
  })
  const [result, setResult] = useState(null)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)

  const setField = useCallback((field, value) => {
    setForm((prev) => ({ ...prev, [field]: value }))
    setResult(null)
    setError(null)
  }, [])

  const handleGenerate = useCallback(async () => {
    setError(null)
    setLoading(true)
    setResult(null)
    try {
      const res = await fetch('/api/manual/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      })
      if (!res.ok) throw new Error('Failed to generate learning path')
      const data = await res.json()
      setResult(data)
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }, [form])

  return (
    <div className="app">
      <header className="app-header">
        <h1 className="app-title">Personality Assessment</h1>
      </header>

      <section className="manual-screen">
        <h2 className="manual-title">Manual Trait Selector</h2>
        <p className="manual-text">
          Select your traits directly to generate a learning path without taking the assessment.
        </p>

        <form className="manual-form" onSubmit={(e) => { e.preventDefault(); handleGenerate() }}>
          {Object.entries(DIMENSION_OPTIONS).map(([key, options]) => (
            <div key={key} className="manual-field">
              <label htmlFor={key} className="manual-label">
                {DIMENSION_LABELS[key]}
              </label>
              <select
                id={key}
                value={form[key]}
                onChange={(e) => setField(key, e.target.value)}
                className="manual-select"
              >
                {options.map((opt) => (
                  <option key={opt} value={opt}>
                    {opt}
                  </option>
                ))}
              </select>
            </div>
          ))}

          {error && <p className="error-message">{error}</p>}

          <button
            type="submit"
            className="btn submit-btn"
            disabled={loading}
          >
            {loading ? 'Generating…' : 'Generate Learning Path'}
          </button>
        </form>

        {result && (
          <div className="manual-results">
            <h3 className="results-title">Personality Profile</h3>
            <div className="personality-section">
              <div className="personality-grid">
                {Object.entries(result.personality).map(([dim, value]) => (
                  <div key={dim} className="personality-item">
                    <span className="personality-label">{DIMENSION_LABELS[dim] ?? dim}</span>
                    <span className="personality-value">{value}</span>
                  </div>
                ))}
              </div>
            </div>

            {result.skills?.length > 0 && (
              <>
                <h3 className="skills-title">Recommended Skills</h3>
                <div className="skills-section">
                  <ul className="skills-list">
                    {result.skills.map((skill, i) => (
                      <li key={i} className="skill-item">
                        {skill}
                      </li>
                    ))}
                  </ul>
                </div>
              </>
            )}
          </div>
        )}

        <Link to="/" className="manual-link">
          ← Back to Assessment
        </Link>
      </section>
    </div>
  )
}
