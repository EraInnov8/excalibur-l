import { useState, useCallback } from 'react'
import { Routes, Route, Link } from 'react-router-dom'
import './App.css'
import ManualSelector from './pages/ManualSelector'

const SCALE_LABELS = ['Strongly disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly agree']
const DIMENSION_LABELS = {
  energy: 'Energy',
  orientation: 'Orientation',
  structure: 'Structure',
  drive: 'Drive',
  reaction: 'Reaction',
}

export default function App() {
  const [phase, setPhase] = useState('intro')
  const [questions, setQuestions] = useState([])
  const [answers, setAnswers] = useState({})
  const [result, setResult] = useState(null)
  const [error, setError] = useState(null)

  const startAssessment = useCallback(async () => {
    setError(null)
    setPhase('loading')
    try {
      const res = await fetch('/api/questions')
      if (!res.ok) throw new Error('Failed to load questions')
      const data = await res.json()
      setQuestions(data.questions ?? [])
      setAnswers({})
      setPhase('questions')
    } catch (err) {
      setError(err.message)
      setPhase('error')
    }
  }, [])

  const setAnswer = useCallback((questionId, score) => {
    setAnswers((prev) => ({ ...prev, [questionId]: score }))
  }, [])

  const submitAssessment = useCallback(async () => {
    if (questions.length === 0) return
    const payload = questions.map((q) => ({
      question_id: q.id,
      score: answers[q.id] ?? 3,
    }))
    setError(null)
    setPhase('submitting')
    try {
      const res = await fetch('/api/assessment/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ answers: payload }),
      })
      if (!res.ok) throw new Error('Failed to submit assessment')
      const data = await res.json()
      setResult(data)
      setPhase('results')
    } catch (err) {
      setError(err.message)
      setPhase('questions')
    }
  }, [questions, answers])

  const answeredCount = Object.keys(answers).length
  const totalQuestions = questions.length
  const allAnswered = totalQuestions > 0 && answeredCount === totalQuestions
  const progressPercent = totalQuestions ? (answeredCount / totalQuestions) * 100 : 0

  return (
    <Routes>
      <Route path="/manual" element={<ManualSelector />} />
      <Route
        path="/"
        element={
          <div className="app">
            <header className="app-header">
              <h1 className="app-title">Personality Assessment</h1>
            </header>

            {phase === 'intro' && (
        <section className="intro-screen">
          <h2 className="intro-title">Discover your work style</h2>
          <p className="intro-text">
            This assessment helps you understand your personality across five dimensions and
            identifies skills that match your profile. Answer each statement honestly based on
            how you typically feel or behave. There are no right or wrong answers.
          </p>
          <button type="button" className="btn" onClick={startAssessment}>
            Start Assessment
          </button>
          <p className="intro-link-wrapper">
            <Link to="/manual" className="intro-link">
              Manual Trait Selector
            </Link>
          </p>
        </section>
      )}

      {phase === 'loading' && (
        <section className="loading-screen">
          <p className="loading-text">Loading questions…</p>
        </section>
      )}

      {phase === 'error' && (
        <section className="error-screen">
          <p className="error-message">{error}</p>
          <button type="button" className="btn restart-btn" onClick={startAssessment}>
            Try Again
          </button>
        </section>
      )}

      {phase === 'questions' && (
        <section className="questions-screen">
          <div className="progress-bar">
            <div className="progress-fill" style={{ width: `${progressPercent}%` }} />
          </div>
          <p className="progress-text">
            {answeredCount} of {totalQuestions} questions answered
          </p>

          {questions.map((q, i) => (
            <div key={q.id} className="question-card">
              <span className="question-number">Question {i + 1}</span>
              <p className="question-text">{q.text}</p>
              <div className="scale-labels">
                <span>{SCALE_LABELS[0]}</span>
                <span>{SCALE_LABELS[4]}</span>
              </div>
              <div className="scale-buttons">
                {[1, 2, 3, 4, 5].map((score) => (
                  <button
                    key={score}
                    type="button"
                    className={`scale-btn ${answers[q.id] === score ? 'selected' : ''}`}
                    onClick={() => setAnswer(q.id, score)}
                    aria-pressed={answers[q.id] === score}
                    aria-label={`${score}: ${SCALE_LABELS[score - 1]}`}
                  >
                    {score}
                  </button>
                ))}
              </div>
            </div>
          ))}

          <div className="submit-section">
            <button
              type="button"
              className="btn submit-btn"
              onClick={submitAssessment}
              disabled={!allAnswered}
            >
              {allAnswered ? 'View Results' : `Answer ${totalQuestions - answeredCount} more to continue`}
            </button>
            {error && <p className="error-message">{error}</p>}
          </div>
        </section>
      )}

      {phase === 'submitting' && (
        <section className="loading-screen">
          <p className="loading-text">Calculating your profile…</p>
        </section>
      )}

      {phase === 'results' && result && (
        <section className="results-screen">
          <h2 className="results-title">Your Personality Profile</h2>

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
            <div className="skills-section">
              <h3 className="skills-title">Recommended Skills</h3>
              <ul className="skills-list">
                {result.skills.map((skill, i) => (
                  <li key={i} className="skill-item">
                    {skill}
                  </li>
                ))}
              </ul>
            </div>
          )}

          <button type="button" className="btn btn-secondary restart-btn" onClick={startAssessment}>
            Take Assessment Again
          </button>
        </section>
      )}
          </div>
        }
      />
    </Routes>
  )
}
