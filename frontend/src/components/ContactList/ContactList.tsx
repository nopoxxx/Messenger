import { useEffect, useState } from 'react'
import wsApi from '../../utils/websocketApi'
import Contact from '../Contact/Contact'
//@ts-ignore
import classes from './ContactList.module.css'

export function ContactList() {
	const [contacts, setContacts] = useState<any[]>([])

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

	return (
		<ul className={classes.ContactList}>
			{contacts.map((contact: any) => (
				<Contact key={contact.id} {...contact} />
			))}
		</ul>
	)
}

export default ContactList
