import { useCallback, useEffect, useState } from 'react'

export default function Form(props) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
  })

  const handleChange = useCallback((e) => {
    const { name, value } = e.target
    setFormData((prev) => ({ ...prev, [name]: value }))
  }, [])

  const handleSubmit = useCallback((e) => {
    e.preventDefault()
   //console.log(formData)
    props.onAddContact(formData)
    setFormData({
      name: '',
      email: '',
    })
  }, [formData])

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        name="name"
        value={formData.name}
        onChange={handleChange}
        placeholder="Name"
      />
      <input
        type="email"
        name="email"
        value={formData.email}
        onChange={handleChange}
        placeholder="Email"
      />
      <button type="submit">Submit</button>
    </form>
  )
}