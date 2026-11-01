import axios from 'axios'
import { act, fireEvent, render, screen, waitFor } from '@testing-library/react'
import React from 'react';
import App from '../App.jsx';

jest.mock('axios', () => ({
    __esModule: true,
    default: {
        get: jest.fn(),
        post: jest.fn(),
        isAxiosError: jest.fn(),
    },
}))

let usersResponse
let messagesQueue

function mockChatApi() {
    axios.get.mockImplementation((url) => {
        if (url === '/api/chat_users') {
            return Promise.resolve({ data: { data: usersResponse } })
        }
        if (url === '/api/user_messages') {
            return Promise.resolve({
                data: { data: { messages: messagesQueue.shift() ?? [] } },
            })
        }
        return Promise.reject(new Error(`Unexpected GET ${url}`))
    })
}

async function login(name = 'Rafal') {
    fireEvent.change(screen.getByLabelText(/username/i), {
        target: { value: name },
    })
    fireEvent.click(screen.getByRole('button', { name: /login/i }))
    await screen.findByRole('heading', { name: 'Prado Chat Demo' })
}

describe('App tests', () => {
    beforeEach(() => {
        jest.clearAllMocks()
        usersResponse = [{ username: 'Rafal' }, { username: 'Jola' }]
        messagesQueue = []
        mockChatApi()
        axios.post.mockResolvedValue({ data: {} })
    })

    afterEach(() => {
        jest.useRealTimers()
    })

    it('posts the entered username to the chat user endpoint', async () => {
        axios.post.mockResolvedValue({ data: { username: 'Rafal' } })
        render(<App />)

        fireEvent.change(screen.getByLabelText(/username/i), {
            target: { value: 'Rafal' },
        })
        fireEvent.click(screen.getByRole('button', { name: /login/i }))

        await waitFor(() => {
            expect(axios.post).toHaveBeenCalledWith(
                '/api/chat_user',
                { username: 'Rafal' },
            )
        })
        expect(await screen.findByRole('heading', { name: 'Prado Chat Demo' })).toBeInTheDocument()
    });

    it('shows the user list and messages after login', async () => {
        messagesQueue = [[{ from_user: 'Jola', message: 'Hi Rafal' }]]
        render(<App />)
        await login()

        expect(await screen.findByText('Hi Rafal')).toBeInTheDocument()
        expect(screen.getByText('Jola:')).toBeInTheDocument()
        expect(screen.getByRole('list')).toHaveTextContent('Jola')
        expect(axios.get).toHaveBeenCalledWith('/api/user_messages', {
            params: { username: 'Rafal' },
        })
        expect(axios.get).toHaveBeenCalledWith('/api/chat_users')
    });

    it('posts a message from the form', async () => {
        render(<App />)
        await login()

        fireEvent.change(screen.getByLabelText('Message'), {
            target: { value: 'Hello there' },
        })
        fireEvent.click(screen.getByRole('button', { name: /send/i }))

        await waitFor(() => {
            expect(axios.post).toHaveBeenCalledWith('/api/user_message', {
                from_user: 'Rafal',
                message: 'Hello there',
            })
        })
        await waitFor(() => {
            expect(screen.getByLabelText('Message')).toHaveValue('')
        })
    });

    it('polls every 2 seconds and appends new messages', async () => {
        jest.useFakeTimers()
        messagesQueue = [
            [{ from_user: 'Jola', message: 'first' }],
            [{ from_user: 'Majk', message: 'second' }],
        ]
        render(<App />)
        fireEvent.change(screen.getByLabelText(/username/i), {
            target: { value: 'Rafal' },
        })
        fireEvent.click(screen.getByRole('button', { name: /login/i }))
        expect(await screen.findByText('first')).toBeInTheDocument()

        await act(async () => {
            await jest.advanceTimersByTimeAsync(2000)
        })

        expect(screen.getByText('first')).toBeInTheDocument()
        expect(screen.getByText('second')).toBeInTheDocument()
    });

    it('returns to the login form when the user list is empty', async () => {
        usersResponse = []
        render(<App />)
        fireEvent.change(screen.getByLabelText(/username/i), {
            target: { value: 'Rafal' },
        })
        fireEvent.click(screen.getByRole('button', { name: /login/i }))

        await waitFor(() => {
            expect(axios.get).toHaveBeenCalledWith('/api/chat_users')
        })
        await waitFor(() => {
            expect(screen.getByLabelText(/username/i)).toBeInTheDocument()
        })
        expect(screen.queryByRole('heading', { name: 'Prado Chat Demo' })).toBeNull()
    });
});