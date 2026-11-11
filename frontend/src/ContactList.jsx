import { useState } from 'react'
import 'antd/dist/reset.css'
import Form from './components/Form'
import SaveableTextArea from './components/SavableTextField'
import UserCommentsTable from './components/UserCommentsTable'

const SEND_COMMENT_API = '/api/comments'

const contacts = [
  {
    name: 'John Doe',
    email: 'john@example.com'
  },
  {
    name: 'Jane Smith',
    email: 'jane@example.com'
  }
];



export default function ContactList() {
  const [contactsState, setContactsState] = useState(contacts)

// Your async posting function
const handleAsyncPost = async (textValue) => {
    const response = await fetch(SEND_COMMENT_API, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment: textValue, user_id: 1 }),
    });

    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    const result = await response.json();
    console.log('Posted successfully:', result);
  };

  const addContact = (data) => {
    setContactsState(prev => [...prev, data])
  }

  return (
    <div>
      {/*      <SaveableTextArea
        onSave={handleAsyncPost}
        buttonText="Post Comment"
        placeholder="Write your feedback here..."
        maxLength={255}
        showCount
      /> */}
      <Form onAddContact={addContact} />
      <h2>Contacts</h2>
      <ul>
        {contactsState.map((contact, index) => (
          <li key={index}>{contact.name} - {contact.email}</li>
        ))}
      </ul>
      <UserCommentsTable />
    </div>
  )
}