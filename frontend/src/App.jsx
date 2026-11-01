import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import './App.css'

const CHAT_USER_API = '/api/chat_user'
const CHAT_USERS_API = '/api/chat_users'
const MESSAGES_API = '/api/user_messages'
const SEND_MESSAGE_API = '/api/user_message'

function userName(user) {
  return typeof user === 'string' ? user : (user?.username ?? user?.name ?? '')
}

function Chat({ username, onLogout }) {
  const [messages, setMessages] = useState([])
  const [users, setUsers] = useState([])
  const [text, setText] = useState('')
  const [isSending, setIsSending] = useState(false)
  const [error, setError] = useState('')

  const load = useCallback(async () => {
    try {
      const [messagesResponse, usersResponse] = await Promise.all([
        axios.get(MESSAGES_API, { params: { username } }),
        axios.get(CHAT_USERS_API),
      ])
      const messageData = messagesResponse.data.data.messages
      const userData = usersResponse.data.data
      if (Array.isArray(messageData) && messageData.length > 0) {
        setMessages((current) => [...current, ...messageData])
      }
      if (Array.isArray(userData) && userData.length === 0) {
        onLogout()
        return
      }
      setUsers(Array.isArray(userData) ? userData : [])
      setError('')
    } catch (requestError) {
      setError(
        requestError instanceof Error
          ? requestError.message
          : 'Unable to load chat.',
      )
    }
  }, [username, onLogout])

  useEffect(() => {
    load()
    const intervalId = setInterval(load, 2000)
    return () => clearInterval(intervalId)
  }, [load])

  async function handleSend(event) {
    event.preventDefault()
    if (!text.trim()) return
    setIsSending(true)
    try {
      await axios.post(SEND_MESSAGE_API, { from_user: username, message: text })
      setText('')
      await load()
    } catch (requestError) {
      setError(
        requestError instanceof Error
          ? requestError.message
          : 'Unable to send message.',
      )
    } finally {
      setIsSending(false)
    }
  }

  return (
    <main className="chat-page">
      <h1>Prado Chat Demo</h1>
      <div className="chat-layout">
        <section className="chat-history" aria-label="Messages">
          {messages.map((item, index) => (
            <div key={item.id ?? index}>
              <strong>{item.from_user ?? item.username ?? item.sender}:</strong>{' '}
              {item.message ?? item.text}
            </div>
          ))}
        </section>
        <aside className="chat-users" aria-label="Users">
          <ul>
            {users.map((user, index) => (
              <li key={userName(user) || index}>{userName(user)}</li>
            ))}
          </ul>
        </aside>
        <form className="chat-form" onSubmit={handleSend}>
          <textarea
            aria-label="Message"
            value={text}
            onChange={(event) => setText(event.target.value)}
            disabled={isSending}
          />
          <button type="submit" disabled={isSending}>
            Send
          </button>
        </form>
      </div>
      {error && <p className="form-error" role="alert">{error}</p>}
    </main>
  )
}

function App() {
  const [username, setUsername] = useState('')
  const [loggedIn, setLoggedIn] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')

  async function handleSubmit(event) {
    event.preventDefault()
    setIsSubmitting(true)
    setMessage('')
    setError('')

    try {
      await axios.post(CHAT_USER_API, { username })
      setMessage(`Welcome, ${username}.`)
      setLoggedIn(true)
    } catch (requestError) {
      const responseMessage = axios.isAxiosError(requestError)
        ? requestError.response?.data?.message
        : undefined
      setError(
        responseMessage ||
          (requestError instanceof Error
            ? requestError.message
            : 'Unable to log in.'),
      )
    } finally {
      setIsSubmitting(false)
    }
  }

  const handleLogout = useCallback(() => {
    setLoggedIn(false)
    setMessage('')
  }, [])

  if (loggedIn) {
    return <Chat username={username} onLogout={handleLogout} />
  }

  return (
    <main className="login-page">
      <h1>Prado Chat Demo Login</h1>
      <form onSubmit={handleSubmit}>
        <fieldset disabled={isSubmitting}>
          <legend>Please enter your name:</legend>
          <div className="username-field">
            <label htmlFor="username">Username:</label>
            <input
              id="username"
              name="username"
              type="text"
              autoComplete="username"
              maxLength={20}
              required
              value={username}
              onChange={(event) => setUsername(event.target.value)}
            />
          </div>
          <button type="submit">
            {isSubmitting ? 'Logging in...' : 'Login'}
          </button>
        </fieldset>
        {message && <p className="form-message" role="status">{message}</p>}
        {error && <p className="form-error" role="alert">{error}</p>}
      </form>
    </main>
  )
}

export default App
