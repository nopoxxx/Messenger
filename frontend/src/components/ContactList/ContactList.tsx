import { useEffect, useState } from 'react'
import wsApi from '../../utils/websocketApi'
import Contact from '../Contact/Contact'
//@ts-ignore
import classes from './ContactList.module.css'

export function ContactList(props: any) {
	const [contacts, setContacts] = useState<any[]>([])
	const [active, setActive] = useState<number>(0)

	useEffect(() => {
		wsApi.on('getUsers', data => {
			if (Array.isArray(data)) {
				setContacts(data)
			} else {
				console.error('getUsers event returned non-array data:', data)
				setContacts([])
			}
		})

		wsApi.getUsers()
	}, [])

	useEffect(() => {
		wsApi.getChatMessages(active, false)
	}, [active])

	return (
		<ul className={classes.ContactList}>
			{contacts.map((contact: any) => (
				<Contact
					onClick={() => {
						props.setContact(
							contact.username ? contact.username : contact.email
						)
						props.setContactId(contact.id)
						console.log(contact.id)
						setActive(contact.id)
					}}
					key={contact.id}
					{...contact}
				/>
			))}
		</ul>
	)
}

export default ContactList
