import react from '@vitejs/plugin-react'
import { defineConfig, loadEnv } from 'vite'

function chatUserProxyMethodGuard() {
  return {
    name: 'chat-user-proxy-method-guard',
    configureServer(server) {
      server.middlewares.use((request, response, next) => {
        const pathname = new URL(request.url || '/', 'http://localhost').pathname

        if (pathname === '/api/chat_user' && request.method !== 'POST') {
          response.statusCode = 405
          response.setHeader('Allow', 'POST')
          response.setHeader('Content-Type', 'application/json')
          response.end(JSON.stringify({
            status: 'error',
            message: 'Method not allowed. Use POST.',
          }))
          return
        }

        next()
      })
    },
  }
}

export default defineConfig(({ command, mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiKey = env.API_KEY

  if (command === 'serve' && !apiKey) {
    throw new Error('Set the API_KEY environment variable before starting the frontend dev server.')
  }

  return {
    plugins: [react(), chatUserProxyMethodGuard()],
    server: {
      host: '0.0.0.0', // Binds Vite to all network interfaces
      port: 5173,
      hmr: {
        host: '192.168.1.111', // Replace with your VirtualBox IP (e.g., 192.168.56.101)
      },
      proxy: command === 'serve'
        ? Object.fromEntries(
            [
              '^/api/chat_user/?$',
              '^/api/chat_users/?$',
              '^/api/user_messages/?(\\?.*)?$',
              '^/api/user_message/?$',
              '^/api/nba/teams/?$',
              '^/api/nba/teams_w_players/?$',
              '^/api/comments/?(\\?.*)?$',
              '^/api/users_w_comments/?(\\?.*)?$',
              '^/api/user/?(\\?.*)?$',
            ].map((route) => [
              route,
              {
                target: 'http://192.168.1.111:8004',
                changeOrigin: true,
                headers: {
                  'X-API-Key': apiKey,
                },
              },
            ]),
          )
        : undefined,
    },
  }
})
