import Contact from '../Contact/Contact'
//@ts-ignore
import classes from './ContactList.module.css'

export function ContactList() {
	const TEMP_Contacts = [
		{
			avatar: 'http://localhost/uploads/avatars/1.jpg',
			nickname: 'nopox',
			email: 'dmitriy.nopox@gmail.com',
		},
		{
			avatar: 'http://localhost/uploads/avatars/2.jpg',
			nickname: 'Test',
			email: '',
		},
		{
			avatar: 'http://localhost/uploads/avatars/3.png',
			nickname: '',
			email: 'tester@sf.ru',
		},
		{
			avatar: 'http://localhost/uploads/avatars/1.jpg',
			nickname: 'Яна',
			email: 'yana@mail.lv',
		},
	]

	return (
		<ul className={classes.ContactList}>
			{TEMP_Contacts.map(contact => (
				<Contact {...contact} />
			))}
		</ul>
	)
}

export default ContactList
