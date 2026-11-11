import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import './App.css'

const NBA_TEAMS_API = '/api/nba/teams_w_players'

function Nba() {
  const [teams, setTeams] = useState([])
  const [expandedTeams, setExpandedTeams] = useState({}) 

  const fetchTeams = useCallback(async () => {
    try {
      const response = await axios.get(NBA_TEAMS_API)
      console.log(response.data.data)
      setTeams(response.data.data)
    } catch (error) {
      console.error(error)
    }
  }, [])

  useEffect(() => {
    fetchTeams()
  }, [fetchTeams])

  const toggleTeam = (id) =>
  setExpandedTeams(prev => ({ ...prev, [id]: !prev[id] }))

  return (
    <div>
      <h1>NBA Teams</h1>
      <ul>
        {teams.map(team => {
          const isOpen = !!expandedTeams[team.id]
          return (
            <li key={team.id}>
              <button
                type="button"
                onClick={() => toggleTeam(team.id)}
                aria-expanded={isOpen}
                style={{ cursor: 'pointer' }}
              >
                {isOpen ? '▼' : '▶'} {team.city} {team.name}
              </button>
              {isOpen && (
                <ul>
                  {team.players.map(player => (
                    <li key={player.id}>{player.first_name} {player.last_name}</li>
                  ))}
                </ul>
              )}
            </li>
          )
        })}
      </ul>
    </div>
  )
}

export default Nba