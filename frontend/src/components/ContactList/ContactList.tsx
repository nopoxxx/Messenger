import { useEffect, useState } from 'react'
import wsApi from '../../utils/websocketApi'
import Contact from '../Contact/Contact'
//@ts-ignore
import classes from './ContactList.module.css'

export function ContactList() {
	// const TEMP_Contacts = [
	// 	{
	// 		avatar: 'http://localhost/uploads/avatars/1.jpg',
	// 		nickname: 'nopox',
	// 		email: 'dmitriy.nopox@gmail.com',
	// 	},
	// 	{
	// 		avatar: 'http://localhost/uploads/avatars/2.jpg',
	// 		nickname: 'Test',
	// 		email: '',
	// 	},
	// 	{
	// 		avatar: 'http://localhost/uploads/avatars/3.png',
	// 		nickname: '',
	// 		email: 'tester@sf.ru',
	// 	},
	// 	{
	// 		avatar: 'http://localhost/uploads/avatars/1.jpg',
	// 		nickname: 'Яна',
	// 		email: 'yana@mail.lv',
	// 	},
	// ]

	const [contacts, setContacts] = useState([])

	useEffect(() => {
		wsApi.on('getUsers', data => setContacts(data)) // Подписываемся на событие
		wsApi.getUsers() // Отправляем запрос
	}, [])

	return (
		<ul className={classes.ContactList}>
			{contacts.map((contact: any) => (
				<Contact
					key={contact.nickname ? contact.nickname : contact.email}
					{...contact}
				/>
			))}
		</ul>
	)
}

export default ContactList
