import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import React from 'react'
import UserCommentsTable from '../components/UserCommentsTable'

describe('UserCommentsTable', () => {
  const originalFetch = global.fetch

  beforeEach(() => {
    global.fetch = jest.fn((url, options) => {
      if (options?.method === 'PUT') {
        return Promise.resolve({ ok: true })
      }

      if (url.startsWith('/api/users_w_comments?')) {
        return Promise.resolve({
          ok: true,
          json: () => Promise.resolve([{
            id: 1,
            name: 'Jane Smith',
            email: 'jane@example.com',
            status: 6,
            comments: [{ id: 12, comment: 'Original comment' }],
          }]),
        })
      }

      if (url === '/api/comments?id=12') {
        return Promise.resolve({
          ok: true,
          json: () => Promise.resolve({ data: { id: 12, comment: 'Original comment' } }),
        })
      }

      return Promise.reject(new Error(`Unexpected request: ${url}`))
    })
  })

  afterEach(() => {
    global.fetch = originalFetch
  })

  it('loads, edits, and saves a comment', async () => {
    const updatedComment =
      'Updated comment with enough additional words to exceed fifty characters.'
    render(<UserCommentsTable />)

    fireEvent.click(await screen.findByRole('button', { name: 'Show full comment' }))

    const textArea = await screen.findByRole('textbox')
    await waitFor(() => expect(textArea).toHaveValue('Original comment'))
    fireEvent.change(textArea, { target: { value: updatedComment } })
    fireEvent.click(screen.getByRole('button', { name: 'Save comment' }))

    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith('/api/comments?id=12', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comment: updatedComment }),
      })
    })
    expect(await screen.findByText(`${updatedComment.slice(0, 50)}...`)).toBeInTheDocument()
  })

  it('displays each role enabled in the status bitmask', async () => {
    render(<UserCommentsTable />)

    expect(await screen.findByText('IS MODERATOR')).toBeInTheDocument()
    expect(screen.getByText('IS ADMIN')).toBeInTheDocument()
    expect(screen.queryByText('IS ACTIVE')).not.toBeInTheDocument()
    expect(screen.queryByText('IS PREMIUM')).not.toBeInTheDocument()
  })

  it('edits roles and saves the new status', async () => {
    render(<UserCommentsTable />)

    fireEvent.click(await screen.findByRole('button', { name: 'Edit roles' }))
    fireEvent.click(await screen.findByRole('checkbox', { name: 'IS ACTIVE' }))
    fireEvent.click(screen.getByRole('checkbox', { name: 'IS MODERATOR' }))
    fireEvent.click(screen.getByRole('button', { name: 'Save roles' }))

    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith('/api/users?id=1', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: 5 }),
      })
    })
    await waitFor(() => expect(screen.queryByText('IS MODERATOR')).not.toBeInTheDocument())
  })
})
