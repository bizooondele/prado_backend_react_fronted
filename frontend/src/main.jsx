import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import './index.css'
import App from './App.jsx'
import Nba from './Nba.jsx';
import ContactList from './ContactList.jsx';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<App />} />
        <Route path="/nba" element={<Nba />} />
        <Route path="/contactlist" element={<ContactList />} />
      </Routes>
    </BrowserRouter>
  </StrictMode>,
)
