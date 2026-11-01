import { useEffect, useState } from 'react'
import { Table, Modal, Button, Checkbox, Spin, message } from 'antd'
import { CommentOutlined, EditOutlined } from '@ant-design/icons'
import SaveableTextArea from './SavableTextField'

const COMMENT_LENGTH = 50
const STATUS_ROLES = [
  { bit: 1, label: 'IS ACTIVE' },
  { bit: 2, label: 'IS MODERATOR' },
  { bit: 4, label: 'IS ADMIN' },
  { bit: 8, label: 'IS PREMIUM' },
]

export default function UserCommentsTable() {
  const [users, setUsers] = useState([])
  const [loading, setLoading] = useState(false)
  const [modalOpen, setModalOpen] = useState(false)
  const [modalLoading, setModalLoading] = useState(false)
  const [fullComment, setFullComment] = useState('')
  const [commentId, setCommentId] = useState(null)
  const [rolesUser, setRolesUser] = useState(null)
  const [rolesStatus, setRolesStatus] = useState(0)
  const [rolesSaving, setRolesSaving] = useState(false)

  useEffect(() => {
    const load = async () => {
      setLoading(true)
      try {
        const params = new URLSearchParams({ comment_length: COMMENT_LENGTH })
        const res = await fetch(`/api/users_w_comments?${params}`)
        if (!res.ok) throw new Error('Failed to load users')
        const data = await res.json()
        const list = Array.isArray(data) ? data : data?.users ?? data?.data
        if (!Array.isArray(list)) throw new Error('Unexpected response format')
        setUsers(list)
      } catch (e) {
        message.error(e.message)
      } finally {
        setLoading(false)
      }
    }
    load()
  }, [])

  const openComment = async (commentId) => {
    setCommentId(commentId)
    setFullComment('')
    setModalOpen(true)
    setModalLoading(true)
    try {
      const res = await fetch(`/api/comments?id=${encodeURIComponent(commentId)}`)
      if (!res.ok) throw new Error('Failed to load comment')
      const data = await res.json()
      const item = data?.data ?? data
      setFullComment((Array.isArray(item) ? item[0] : item)?.comment ?? '')
    } catch (e) {
      message.error(e.message)
    } finally {
      setModalLoading(false)
    }
  }

  const saveComment = async (comment, id) => {
    const res = await fetch(`/api/comments?id=${encodeURIComponent(id)}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment }),
    })
    if (!res.ok) throw new Error('Failed to save comment')

    setFullComment(comment)
    setUsers((currentUsers) =>
      currentUsers.map((user) => ({
        ...user,
        comments: (user.comments ?? []).map((item) =>
          item.id === id
            ? { ...item, comment: comment.slice(0, COMMENT_LENGTH) }
            : item,
        ),
      })),
    )
  }

  const openRoles = (user) => {
    setRolesUser(user)
    setRolesStatus(user.status ?? 0)
  }

  const toggleRole = (bit, checked) => {
    setRolesStatus((current) => (checked ? current | bit : current & ~bit))
  }

  const saveRoles = async () => {
    setRolesSaving(true)
    try {
      const res = await fetch(`/api/user?id=${encodeURIComponent(rolesUser.id)}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: rolesStatus }),
      })
      if (!res.ok) throw new Error('Failed to save roles')

      setUsers((currentUsers) =>
        currentUsers.map((user) =>
          user.id === rolesUser.id ? { ...user, status: rolesStatus } : user,
        ),
      )
      message.success('Roles saved')
      setRolesUser(null)
    } catch (e) {
      message.error(e.message)
    } finally {
      setRolesSaving(false)
    }
  }

  const columns = [
    { title: 'Name', dataIndex: 'name', key: 'name' },
    { title: 'Email', dataIndex: 'email', key: 'email' },
    {
      title: 'Roles',
      dataIndex: 'status',
      key: 'status',
      render: (status, user) => (
        <div style={{ display: 'flex', alignItems: 'flex-start', gap: 4 }}>
          <Button
            type="text"
            icon={<EditOutlined />}
            aria-label="Edit roles"
            onClick={() => openRoles(user)}
          />
          <div>
            {STATUS_ROLES.filter(({ bit }) => (status & bit) === bit).map(({ label }) => (
              <div key={label}>{label}</div>
            ))}
          </div>
        </div>
      ),
    },
    {
      title: 'Comment',
      dataIndex: 'comments',
      key: 'comments',
      // comment text (ch units) plus room for the icon button and cell padding
      width: `calc(${COMMENT_LENGTH}ch + 64px)`,
      render: (comments) =>
        (comments ?? []).map((c) => (
          <div key={c.id} style={{ whiteSpace: 'nowrap' }}>
            <Button
              type="text"
              icon={<CommentOutlined />}
              aria-label="Show full comment"
              onClick={() => openComment(c.id)}
            />{' '}
            {c.comment}
            {c.comment?.length >= COMMENT_LENGTH && '...'}
          </div>
        )),
    },
  ]

  return (
    <>
      <Table
        columns={columns}
        dataSource={users}
        loading={loading}
        rowKey={(r) => r.id ?? r.email}
      />
      <Modal
        title="Comment"
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        footer={null}
      >
        <Spin spinning={modalLoading}>
          <SaveableTextArea
            initialValue={fullComment}
            onSave={(comment) => saveComment(comment, commentId)}
            buttonText="Save comment"
            placeholder="Edit comment..."
            rows={8}
          />
        </Spin>
      </Modal>
      <Modal
        title="Roles"
        open={rolesUser !== null}
        onCancel={() => setRolesUser(null)}
        footer={null}
      >
        <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
          {STATUS_ROLES.map(({ bit, label }) => (
            <Checkbox
              key={bit}
              checked={(rolesStatus & bit) === bit}
              onChange={(e) => toggleRole(bit, e.target.checked)}
            >
              {label}
            </Checkbox>
          ))}
          <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
            <Button type="primary" onClick={saveRoles} loading={rolesSaving}>
              Save roles
            </Button>
          </div>
        </div>
      </Modal>
    </>
  )
}
